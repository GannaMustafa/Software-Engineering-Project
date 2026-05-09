<?php

require_once '../app/models/LogisticsAnalytics.php';

class LogisticsAnalyticsService
{
    private $model;
    private $db;

    public function __construct($model = null)
    {
        $this->model = $model ?: new LogisticsAnalytics();
        $this->db = class_exists('Database') ? Database::getInstance()->getConnection() : null;
    }

    public function dashboardData($role = 'pet_owner', $userId = null)
    {
        $provider = $this->currentProvider($role, $userId);
        $providerId = $provider['id'] ?? null;
        $payments = $this->providerPaymentRows($providerId);
        $bookings = $this->bookingRows($providerId);
        $reports = $this->completionReports($providerId);
        $income = $this->incomeData($payments, $provider);

        return [
            'role' => $role,
            'provider' => $provider,
            'sections' => $this->model->sections(),
            'capabilities' => $this->model->capabilities(),
            'stats' => $this->stats($payments, $bookings, $reports, $income),
            'vendorSummary' => $this->providerSummary($payments),
            'vendors' => $payments,
            'batches' => $bookings,
            'incidents' => $reports,
            'behaviorProfiles' => $this->behaviorRows($providerId),
            'income' => $income
        ];
    }

    public function pageSectionsData($role = 'pet_owner', $userId = null)
    {
        return $this->dashboardData($role, $userId);
    }

    public function includedLogicData($role = 'pet_owner', $userId = null)
    {
        $dashboard = $this->dashboardData($role, $userId);

        return [
            'role' => $role,
            'sections' => $dashboard['sections'],
            'capabilities' => $dashboard['capabilities'],
            'stats' => $dashboard['stats']
        ];
    }

    public function paymentReportData($role = 'pet_owner', $userId = null)
    {
        date_default_timezone_set('Africa/Cairo');

        $provider = $this->currentProvider($role, $userId);
        $payments = $this->providerPaymentRows($provider['id'] ?? null);
        $income = $this->incomeData($payments, $provider);

        return [
            'role' => $role,
            'report_id' => 'PAY-' . date('Ymd') . '-' . (($provider['id'] ?? null) ?: 'ALL'),
            'report_date' => date('F j, Y'),
            'generated_at' => date('F j, Y, g:i A'),
            'service_provider' => $provider['business_name'] ?? 'All service providers',
            'summary' => $this->paymentSummary($payments, $income),
            'vendor_payments' => $payments,
            'provider_payments' => $this->providerCards($payments, $provider),
            'income_sources' => $income['sources'],
            'monthly_income' => $income['monthly']
        ];
    }

    public function createCompletionReport($bookingId, $details, $userId)
    {
        if ($bookingId <= 0) {
            throw new RuntimeException('Please choose a valid completed service.');
        }

        $provider = $this->currentProvider('service_provider', $userId);
        if (!$provider) {
            throw new RuntimeException('Service provider profile was not found for this account.');
        }

        $booking = $this->fetchOne("
            SELECT b.*, s.name AS service_name, s.price, s.discount_percentage
            FROM service_bookings b
            JOIN services s ON s.id = b.service_id
            WHERE b.id = ? AND b.provider_id = ?
        ", [$bookingId, $provider['id']]);

        if (!$booking) {
            throw new RuntimeException('This booking does not belong to the current service provider.');
        }

        $existing = $this->fetchOne('SELECT id FROM service_completion_reports WHERE booking_id = ? LIMIT 1', [$bookingId]);
        if ($existing) {
            throw new RuntimeException('A completion report already exists for this service.');
        }

        $this->db->beginTransaction();
        try {
            $this->execute(
                "UPDATE service_bookings SET status = 'completed', completed_at = COALESCE(completed_at, NOW()) WHERE id = ?",
                [$bookingId]
            );

            $title = 'Completion report for ' . ($booking['service_name'] ?: 'service #' . $booking['service_id']);
            $this->execute("
                INSERT INTO service_completion_reports
                    (booking_id, provider_id, owner_id, service_id, report_title, report_details, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [
                $bookingId,
                $provider['id'],
                $booking['owner_id'],
                $booking['service_id'],
                $title,
                $details,
                $userId
            ]);

            $reportId = (int) $this->db->lastInsertId();
            $calc = $this->paymentCalculation((float) $booking['price'], (float) $booking['discount_percentage']);
            $this->execute("
                INSERT INTO provider_payments
                    (booking_id, report_id, provider_id, owner_id, service_id, base_price, commission_rate,
                     commission_amount, tax_rate, tax_amount, gross_amount, provider_earning, platform_total_due)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $bookingId,
                $reportId,
                $provider['id'],
                $booking['owner_id'],
                $booking['service_id'],
                $calc['base_price'],
                $calc['commission_rate'],
                $calc['commission_amount'],
                $calc['tax_rate'],
                $calc['tax_amount'],
                $calc['gross_amount'],
                $calc['provider_earning'],
                $calc['platform_total_due']
            ]);

            $this->notifyAdmins(
                'Service report needs confirmation',
                $provider['business_name'] . ' submitted a completion report for ' . ($booking['service_name'] ?: 'a service') . '.',
                'service_report_confirm'
            );
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw new RuntimeException('Could not save the report: ' . $e->getMessage());
        }
    }

    public function confirmCashPayment($paymentId, $userId)
    {
        $payment = $this->ownedPayment($paymentId, $userId);
        $this->execute("
            UPDATE provider_payments
            SET payment_status = 'cash_collected',
                cash_received_amount = gross_amount,
                paid_at = COALESCE(paid_at, NOW())
            WHERE id = ?
        ", [$payment['id']]);
    }

    public function markTransferred($paymentId, $userId)
    {
        $payment = $this->ownedPayment($paymentId, $userId);
        if ($payment['payment_status'] !== 'cash_collected') {
            throw new RuntimeException('Cash must be collected before transferring the platform due amount.');
        }

        $this->execute("
            UPDATE provider_payments
            SET transfer_status = 'transferred',
                transferred_at = COALESCE(transferred_at, NOW())
            WHERE id = ?
        ", [$payment['id']]);
    }

    public function paymentReportPdf($report)
    {
        return $this->buildSimplePdf($this->paymentReportPdfLines($report));
    }

    private function currentProvider($role, $userId)
    {
        if (!$this->db) {
            return null;
        }

        if ($role === 'service_provider') {
            $provider = $this->fetchOne("
                SELECT sp.*, u.username, u.email
                FROM service_providers sp
                JOIN users u ON u.id = sp.user_id
                WHERE sp.user_id = ?
                LIMIT 1
            ", [$userId]);

            if ($provider) {
                return $provider;
            }

            $user = $this->fetchOne('SELECT id, username, email FROM users WHERE id = ? LIMIT 1', [$userId]);
            if (!$user) {
                return null;
            }

            $this->execute(
                "INSERT INTO service_providers (user_id, business_name, service_type, rating) VALUES (?, ?, 'Pet service', 0)",
                [$userId, $user['username'] ?: 'Service Provider']
            );

            return $this->fetchOne("
                SELECT sp.*, u.username, u.email
                FROM service_providers sp
                JOIN users u ON u.id = sp.user_id
                WHERE sp.user_id = ?
                LIMIT 1
            ", [$userId]);
        }

        return $this->fetchOne("
            SELECT sp.*, u.username, u.email
            FROM service_providers sp
            JOIN users u ON u.id = sp.user_id
            ORDER BY sp.id ASC
            LIMIT 1
        ");
    }

    private function providerPaymentRows($providerId)
    {
        if (!$this->db || !$providerId) {
            return [];
        }

        $rows = $this->fetchAll("
            SELECT pp.*, sp.business_name, sp.service_type, sp.rating, s.name AS service_name,
                   po.user_id AS owner_user_id, u.username AS owner_name, p.name AS pet_name,
                   scr.created_at AS report_created_at, b.booked_at, b.completed_at
            FROM provider_payments pp
            JOIN service_providers sp ON sp.id = pp.provider_id
            JOIN services s ON s.id = pp.service_id
            JOIN pet_owners po ON po.id = pp.owner_id
            JOIN users u ON u.id = po.user_id
            LEFT JOIN service_bookings b ON b.id = pp.booking_id
            LEFT JOIN pets p ON p.id = b.pet_id
            LEFT JOIN service_completion_reports scr ON scr.id = pp.report_id
            WHERE pp.provider_id = ?
            ORDER BY COALESCE(pp.paid_at, pp.created_at) DESC, pp.id DESC
        ", [$providerId]);

        return array_map(function ($row) {
            $row['vendor'] = $row['business_name'];
            $row['type'] = 'Service provider';
            $row['orders'] = 1;
            $row['gross_revenue'] = (float) $row['gross_amount'];
            $row['refunds'] = 0;
            $row['logistics_cost'] = 0;
            $row['commission_rate'] = (float) $row['commission_rate'];
            $row['platform_commission'] = (float) $row['commission_amount'];
            $row['tax_amount'] = (float) $row['tax_amount'];
            $row['net_revenue'] = (float) $row['gross_amount'];
            $row['provider_payout'] = (float) $row['provider_earning'];
            $row['platform_total_due'] = (float) $row['platform_total_due'];
            $row['sla_score'] = 100;
            $row['trend'] = '';
            $row['status'] = $row['transfer_status'] === 'transferred' ? 'Transferred' : $row['payment_status'];
            $row['status_class'] = $this->statusClass($row['status']);
            return $row;
        }, $rows);
    }

    private function bookingRows($providerId)
    {
        if (!$this->db || !$providerId) {
            return [];
        }

        return $this->fetchAll("
            SELECT b.*, s.name AS service_name, s.price, u.username AS owner_name, p.name AS pet_name,
                   scr.id AS report_id
            FROM service_bookings b
            JOIN services s ON s.id = b.service_id
            JOIN pet_owners po ON po.id = b.owner_id
            JOIN users u ON u.id = po.user_id
            LEFT JOIN pets p ON p.id = b.pet_id
            LEFT JOIN service_completion_reports scr ON scr.booking_id = b.id
            WHERE b.provider_id = ?
            ORDER BY b.booked_at DESC, b.id DESC
        ", [$providerId]);
    }

    private function completionReports($providerId)
    {
        if (!$this->db || !$providerId) {
            return [];
        }

        $rows = $this->fetchAll("
            SELECT r.*, s.name AS service_name, s.price, sp.business_name, u.username AS owner_name
            FROM service_completion_reports r
            JOIN services s ON s.id = r.service_id
            JOIN service_providers sp ON sp.id = r.provider_id
            JOIN pet_owners po ON po.id = r.owner_id
            JOIN users u ON u.id = po.user_id
            WHERE r.provider_id = ?
            ORDER BY r.created_at DESC, r.id DESC
        ", [$providerId]);

        return array_map(function ($row) {
            $row['incident_id'] = 'RPT-' . $row['id'];
            $row['pet'] = $row['service_name'];
            $row['owner'] = $row['owner_name'];
            $row['sitter'] = $row['business_name'];
            $row['type'] = 'Completion report';
            $row['severity'] = 'Normal';
            $row['reported_at'] = $row['created_at'];
            $row['owner_notified'] = true;
            $row['response_time'] = 'Admin confirmation pending';
            $row['status'] = $row['report_status'];
            $row['next_action'] = $row['report_details'] ?: 'No details added.';
            $row['status_class'] = $this->statusClass($row['report_status']);
            $row['severity_class'] = 'low';
            return $row;
        }, $rows);
    }

    private function behaviorRows($providerId)
    {
        if (!$this->db || !$providerId) {
            return [];
        }

        $rows = $this->fetchAll("
            SELECT DISTINCT p.name AS pet, p.species, u.username AS owner, sp.business_name AS shared_with,
                   p.medical_notes, p.vaccination_status, p.created_at AS last_update
            FROM service_bookings b
            JOIN pets p ON p.id = b.pet_id
            JOIN pet_owners po ON po.id = b.owner_id
            JOIN users u ON u.id = po.user_id
            JOIN service_providers sp ON sp.id = b.provider_id
            WHERE b.provider_id = ? AND b.pet_id IS NOT NULL
            ORDER BY b.booked_at DESC
        ", [$providerId]);

        return array_map(function ($row) {
            $signals = array_filter([
                $row['vaccination_status'] ? 'Vaccination: ' . $row['vaccination_status'] : null,
                $row['medical_notes'] ? 'Medical notes available' : null
            ]);

            $row['share_status'] = 'Shared';
            $row['provider_note'] = $row['medical_notes'] ?: 'No behavior or medical notes recorded yet.';
            $row['signals'] = $signals ?: ['No special signals'];
            $row['signal_count'] = count($row['signals']);
            $row['status_class'] = 'success';
            return $row;
        }, $rows);
    }

    private function incomeData($payments, $provider)
    {
        $monthly = [];
        foreach ($payments as $payment) {
            $month = date('M', strtotime($payment['paid_at'] ?: $payment['created_at']));
            if (!isset($monthly[$month])) {
                $monthly[$month] = ['month' => $month, 'income' => 0, 'commission' => 0];
            }
            $monthly[$month]['income'] += (float) $payment['provider_earning'];
            $monthly[$month]['commission'] += (float) $payment['platform_total_due'];
        }

        $monthly = array_values($monthly);
        $maxIncome = max(array_merge([0], array_column($monthly, 'income')));
        foreach ($monthly as &$month) {
            $month['income_percent'] = $maxIncome > 0 ? round(($month['income'] / $maxIncome) * 100) : 0;
        }
        unset($month);

        $providerIncome = array_sum(array_column($payments, 'provider_earning'));
        $platformDue = array_sum(array_column($payments, 'platform_total_due'));
        $gross = array_sum(array_column($payments, 'gross_amount'));
        $sources = [
            ['label' => 'Provider net income', 'amount' => $providerIncome, 'color' => 'green'],
            ['label' => 'Platform commission and tax due', 'amount' => $platformDue, 'color' => 'gold'],
            ['label' => 'Owner cash collected', 'amount' => $gross, 'color' => 'teal']
        ];
        $total = max(1, array_sum(array_column($sources, 'amount')));
        foreach ($sources as &$source) {
            $source['percent'] = round(($source['amount'] / $total) * 100);
        }
        unset($source);

        return [
            'monthly' => $monthly,
            'sources' => $sources,
            'providers' => $this->providerCards($payments, $provider),
            'total_monthly_income' => $providerIncome,
            'total_commission' => $platformDue,
            'source_total' => $gross
        ];
    }

    private function providerCards($payments, $provider)
    {
        if (!$provider) {
            return [];
        }

        return [[
            'provider' => $provider['business_name'] ?: ($provider['username'] ?? 'Service provider'),
            'service' => $provider['service_type'] ?: 'Pet service',
            'bookings' => count($payments),
            'income' => array_sum(array_column($payments, 'provider_earning')),
            'rating' => $provider['rating'] ?? 0,
            'payout_status' => $this->hasPendingTransfer($payments) ? 'Review' : 'Ready',
            'status_class' => $this->hasPendingTransfer($payments) ? 'warning' : 'success'
        ]];
    }

    private function stats($payments, $bookings, $reports, $income)
    {
        return [
            ['label' => 'Owner cash collected', 'value' => $this->money(array_sum(array_column($payments, 'gross_amount'))), 'hint' => count($payments) . ' provider cash payments', 'icon' => 'fa-money-bill-wave', 'tone' => 'teal'],
            ['label' => 'Platform due', 'value' => $this->money(array_sum(array_column($payments, 'platform_total_due'))), 'hint' => 'Commission plus tax', 'icon' => 'fa-percent', 'tone' => 'green'],
            ['label' => 'Completed services', 'value' => (string) count(array_filter($bookings, fn($b) => $b['status'] === 'completed')), 'hint' => count($reports) . ' completion reports', 'icon' => 'fa-clipboard-check', 'tone' => 'sky'],
            ['label' => 'Pending transfers', 'value' => (string) count(array_filter($payments, fn($p) => $p['transfer_status'] !== 'transferred')), 'hint' => 'Provider owes system after cash collection', 'icon' => 'fa-building-columns', 'tone' => 'rose'],
            ['label' => 'Provider income', 'value' => $this->money($income['total_monthly_income']), 'hint' => 'Lifetime history from database', 'icon' => 'fa-sack-dollar', 'tone' => 'gold']
        ];
    }

    private function providerSummary($payments)
    {
        return [
            'gross_revenue' => array_sum(array_column($payments, 'gross_amount')),
            'total_payouts' => array_sum(array_column($payments, 'provider_earning')),
            'average_sla' => 100
        ];
    }

    private function paymentSummary($payments, $income)
    {
        return [
            ['label' => 'Owner cash total', 'value' => $this->money(array_sum(array_column($payments, 'gross_amount')))],
            ['label' => 'Provider earning', 'value' => $this->money(array_sum(array_column($payments, 'provider_earning')))],
            ['label' => 'Platform commission', 'value' => $this->money(array_sum(array_column($payments, 'commission_amount')))],
            ['label' => 'Tax amount', 'value' => $this->money(array_sum(array_column($payments, 'tax_amount')))],
            ['label' => 'Platform total due', 'value' => $this->money(array_sum(array_column($payments, 'platform_total_due')))],
            ['label' => 'Tracked income history', 'value' => $this->money($income['source_total'])]
        ];
    }

    private function paymentCalculation($price, $discountPercentage)
    {
        $base = max(0, $price - ($price * ($discountPercentage / 100)));
        $commissionRate = 0.15;
        $taxRate = 0.14;
        $commission = round($base * $commissionRate, 2);
        $tax = round($base * $taxRate, 2);

        return [
            'base_price' => round($base, 2),
            'commission_rate' => $commissionRate,
            'commission_amount' => $commission,
            'tax_rate' => $taxRate,
            'tax_amount' => $tax,
            'gross_amount' => round($base + $commission + $tax, 2),
            'provider_earning' => round($base, 2),
            'platform_total_due' => round($commission + $tax, 2)
        ];
    }

    private function ownedPayment($paymentId, $userId)
    {
        if ($paymentId <= 0) {
            throw new RuntimeException('Please choose a valid payment row.');
        }

        $provider = $this->currentProvider('service_provider', $userId);
        $payment = $provider ? $this->fetchOne('SELECT * FROM provider_payments WHERE id = ? AND provider_id = ?', [$paymentId, $provider['id']]) : null;
        if (!$payment) {
            throw new RuntimeException('This payment does not belong to the current service provider.');
        }

        return $payment;
    }

    private function hasPendingTransfer($payments)
    {
        foreach ($payments as $payment) {
            if (($payment['transfer_status'] ?? '') !== 'transferred') {
                return true;
            }
        }
        return false;
    }

    private function notifyAdmins($title, $message, $type)
    {
        $admins = $this->fetchAll("SELECT id FROM users WHERE role = 'admin'");
        $stmt = $this->db->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
        foreach ($admins as $admin) {
            $stmt->execute([$admin['id'], $title, $message, $type]);
        }
    }

    private function paymentReportPdfLines($report)
    {
        $lines = [
            'Paw Hubs Service Provider Income Report',
            'Report ID: ' . $report['report_id'],
            'Date: ' . $report['report_date'],
            'Generated at: ' . $report['generated_at'],
            'Service provider: ' . $report['service_provider'],
            '',
            'Payment summary'
        ];

        foreach ($report['summary'] as $summary) {
            $lines[] = $summary['label'] . ': ' . $summary['value'];
        }

        $lines[] = '';
        $lines[] = 'Transaction history';
        foreach ($report['vendor_payments'] as $payment) {
            $lines[] = 'Payment #' . $payment['id'] . ' | ' . $payment['service_name'] . ' | Owner: ' . $payment['owner_name'];
            $lines[] = 'Cash: ' . $this->money($payment['gross_amount']) . ' | Provider: ' . $this->money($payment['provider_earning']) . ' | Commission: ' . $this->money($payment['commission_amount']) . ' | Tax: ' . $this->money($payment['tax_amount']);
            $lines[] = 'Platform due: ' . $this->money($payment['platform_total_due']) . ' | Payment: ' . $payment['payment_status'] . ' | Transfer: ' . $payment['transfer_status'];
        }

        return $lines;
    }

    private function buildSimplePdf($lines)
    {
        $wrappedLines = [];
        foreach ($lines as $line) {
            foreach (explode("\n", wordwrap((string) $line, 96, "\n", true)) as $part) {
                $wrappedLines[] = $part;
            }
        }

        $pages = array_chunk($wrappedLines, 44);
        $objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>', 3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
        $nextObject = 4;
        $pageRefs = [];

        foreach ($pages ?: [[]] as $pageLines) {
            $contentObject = $nextObject++;
            $pageObject = $nextObject++;
            $pageRefs[] = $pageObject . ' 0 R';
            $content = "BT\n/F1 10 Tf\n50 760 Td\n14 TL\n";
            foreach ($pageLines as $line) {
                $content .= '(' . $this->escapePdfText($line) . ") Tj\nT*\n";
            }
            $content .= 'ET';
            $objects[$contentObject] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
            $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R >> >> /Contents ' . $contentObject . ' 0 R >>';
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageRefs) . '] /Count ' . count($pageRefs) . ' >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $objectNumber => $objectBody) {
            $offsets[$objectNumber] = strlen($pdf);
            $pdf .= $objectNumber . " 0 obj\n" . $objectBody . "\nendobj\n";
        }
        $xrefPosition = strlen($pdf);
        $objectCount = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 " . $objectCount . "\n0000000000 65535 f \n";
        for ($i = 1; $i < $objectCount; $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i] ?? 0) . "\n";
        }
        return $pdf . "trailer\n<< /Size " . $objectCount . " /Root 1 0 R >>\nstartxref\n" . $xrefPosition . "\n%%EOF";
    }

    private function escapePdfText($text)
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string) $text);
    }

    private function money($amount)
    {
        return 'EGP ' . number_format((float) $amount, 0);
    }

    private function statusClass($status)
    {
        $status = strtolower((string) $status);
        if (strpos($status, 'transferred') !== false || strpos($status, 'collected') !== false || strpos($status, 'ready') !== false || strpos($status, 'shared') !== false) {
            return 'success';
        }
        if (strpos($status, 'pending') !== false || strpos($status, 'review') !== false || strpos($status, 'submitted') !== false || strpos($status, 'not_transferred') !== false) {
            return 'warning';
        }
        return 'neutral';
    }

    private function fetchOne($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchAll($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function execute($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }
}
