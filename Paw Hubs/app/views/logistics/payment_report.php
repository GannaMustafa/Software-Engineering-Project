<?php
$report = $report ?? [];
$role = $role ?? ($report['role'] ?? 'pet_owner');
$summary = $report['summary'] ?? [];
$vendorPayments = $report['vendor_payments'] ?? [];
$providerPayments = $report['provider_payments'] ?? [];
$incomeSources = $report['income_sources'] ?? [];
$monthlyIncome = $report['monthly_income'] ?? [];
$backUrl = $role === 'admin'
    ? 'index.php?url=admin/index'
    : ($role === 'service_provider' ? 'index.php?url=logistics/index' : 'index.php?url=home/index');

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('money')) {
    function money($amount)
    {
        return 'EGP ' . number_format((float) $amount, 0);
    }
}

if (!function_exists('rate_percent')) {
    function rate_percent($rate)
    {
        return number_format((float) $rate * 100, 1) . '%';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report | Paw Hubs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/logistics.css">
</head>
<body>
<div class="app-frame">
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-file-invoice-dollar"></i><span>Payment Data</span></div>

        <div>
            <p class="menu-label">Report Sections</p>
            <nav class="menu" aria-label="Payment report sections">
                <a href="#summary"><i class="fas fa-chart-pie"></i> Summary</a>
                <a href="#vendor-payments"><i class="fas fa-money-bill-transfer"></i> Provider transactions</a>
                <a href="#provider-payments"><i class="fas fa-user-tie"></i> Provider payments</a>
                <a href="#income-sources"><i class="fas fa-coins"></i> Income sources</a>
                <a href="#monthly-income"><i class="fas fa-chart-line"></i> Monthly income</a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <nav class="menu">
                <a href="index.php?url=logistics/index"><i class="fas fa-route"></i> Page sections</a>
                <a href="index.php?url=logistics/included"><i class="fas fa-list-check"></i> Included logic</a>
                <a href="<?= e($backUrl) ?>"><i class="fas fa-arrow-left"></i> Back to workspace</a>
            </nav>
        </div>
    </aside>

    <main class="content">
        <header class="topbar">
            <div>
                <h1>Provider Income Report</h1>
                <p>Review the database payment history before downloading the printable PDF.</p>
            </div>
            <div class="action-row">
                <span class="role-pill"><i class="fas fa-calendar-day"></i> <?= e($report['report_date'] ?? '') ?></span>
                <a class="action-btn" href="index.php?url=logistics/index"><i class="fas fa-route"></i> Page Sections</a>
            </div>
        </header>

        <section class="panel" id="summary">
            <header class="page-head">
                <div>
                    <h2>Report information</h2>
                    <p>The date and service provider are shown before the report can be confirmed and downloaded.</p>
                </div>
                <span class="badge info"><i class="fas fa-receipt"></i> <?= e($report['report_id'] ?? '') ?></span>
            </header>

            <div class="report-meta">
                <div class="meta-item">
                    <span>Date</span>
                    <strong><?= e($report['report_date'] ?? '') ?></strong>
                </div>
                <div class="meta-item">
                    <span>Generated at</span>
                    <strong><?= e($report['generated_at'] ?? '') ?></strong>
                </div>
                <div class="meta-item">
                    <span>Service provider</span>
                    <strong><?= e($report['service_provider'] ?? '') ?></strong>
                </div>
            </div>

            <div class="summary-grid" style="margin-top: 16px;">
                <?php foreach ($summary as $item): ?>
                    <div class="summary-item">
                        <span><?= e($item['label']) ?></span>
                        <strong><?= e($item['value']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel" id="vendor-payments">
            <header class="page-head">
                <div>
                    <h2>Provider transaction data</h2>
                    <p>All service revenue, commission, tax, provider earning, and platform due values are calculated from database rows.</p>
                </div>
                <span class="badge success"><i class="fas fa-scale-balanced"></i> Commission ready</span>
            </header>

            <div class="table-scroll">
                <table>
                    <thead>
                    <tr>
                        <th>Service</th>
                        <th>Owner</th>
                        <th>Cash total</th>
                        <th>Provider keeps</th>
                        <th>Platform due</th>
                        <th>Commission</th>
                        <th>Tax</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($vendorPayments as $vendor): ?>
                        <tr>
                            <td>
                                <strong><?= e($vendor['service_name'] ?? $vendor['vendor']) ?></strong>
                                <span class="muted">Payment #<?= (int) $vendor['id'] ?></span>
                            </td>
                            <td><?= e($vendor['owner_name'] ?? '') ?></td>
                            <td><?= money($vendor['gross_amount'] ?? $vendor['gross_revenue']) ?></td>
                            <td><?= money($vendor['provider_earning'] ?? $vendor['provider_payout']) ?></td>
                            <td><?= money($vendor['platform_total_due'] ?? 0) ?></td>
                            <td><?= money($vendor['platform_commission']) ?><br><span class="muted"><?= rate_percent($vendor['commission_rate']) ?></span></td>
                            <td><?= money($vendor['tax_amount'] ?? 0) ?><br><span class="muted"><?= rate_percent($vendor['tax_rate'] ?? 0) ?></span></td>
                            <td><span class="badge <?= e($vendor['status_class']) ?>"><?= e($vendor['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($vendorPayments)): ?><tr><td colspan="8">No payment transactions found.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel" id="provider-payments">
            <header class="page-head">
                <div>
                    <h2>Provider summary</h2>
                    <p>Provider income rows show service type, completed payment count, rating, and payout review state.</p>
                </div>
                <span class="badge info"><i class="fas fa-user-tie"></i> Database provider rows</span>
            </header>

            <div class="report-grid">
                <?php foreach ($providerPayments as $provider): ?>
                    <article class="report-card">
                        <div class="card-head">
                            <div>
                                <h3><?= e($provider['provider']) ?></h3>
                                <p><?= e($provider['service']) ?></p>
                            </div>
                            <span class="badge <?= e($provider['status_class']) ?>"><?= e($provider['payout_status']) ?></span>
                        </div>
                        <div class="detail-list">
                            <div><span>Bookings</span><strong><?= (int) $provider['bookings'] ?></strong></div>
                            <div><span>Income</span><strong><?= money($provider['income']) ?></strong></div>
                            <div><span>Rating</span><strong><?= number_format((float) $provider['rating'], 1) ?>/5</strong></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel" id="income-sources">
            <header class="page-head">
                <div>
                    <h2>Income sources</h2>
                    <p>The report keeps source mix visible before the PDF is downloaded.</p>
                </div>
                <span class="badge success"><i class="fas fa-coins"></i> Source mix</span>
            </header>

            <div class="payment-grid">
                <div>
                    <?php foreach ($incomeSources as $source): ?>
                        <div class="source-row">
                            <div>
                                <strong><?= e($source['label']) ?></strong>
                                <span><?= (int) $source['percent'] ?>% of tracked income</span>
                            </div>
                            <strong><?= money($source['amount']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-grid">
                    <?php foreach ($summary as $item): ?>
                        <div class="summary-item">
                            <span><?= e($item['label']) ?></span>
                            <strong><?= e($item['value']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="panel" id="monthly-income">
            <header class="page-head">
                <div>
                    <h2>Monthly income</h2>
                    <p>Monthly income and commission values are included in the PDF output.</p>
                </div>
                <span class="badge info"><i class="fas fa-chart-line"></i> Month trend</span>
            </header>

            <div class="bars">
                <?php foreach ($monthlyIncome as $month): ?>
                    <div class="bar-row">
                        <strong><?= e($month['month']) ?></strong>
                        <div class="bar-track" aria-label="<?= e($month['month']) ?> income">
                            <div class="bar-fill" style="width: <?= (int) $month['income_percent'] ?>%;"></div>
                        </div>
                        <span class="muted"><?= money($month['income']) ?> / commission <?= money($month['commission']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <form class="report-actions" method="post" action="index.php?url=logistics/downloadPaymentReport">
                <input type="hidden" name="service_provider" value="<?= e($report['service_provider'] ?? '') ?>">
                <a class="action-btn" href="index.php?url=logistics/index"><i class="fas fa-arrow-left"></i> Back to Page Sections</a>
                <button class="confirm-btn" type="submit"><i class="fas fa-file-arrow-down"></i> Confirm & Download PDF</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
