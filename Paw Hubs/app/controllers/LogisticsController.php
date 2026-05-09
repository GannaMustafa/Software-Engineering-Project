<?php

require_once '../app/services/LogisticsAnalyticsService.php';

class LogisticsController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new LogisticsAnalyticsService();
    }

    public function index()
    {
        [$role, $userId] = $this->requireProviderOrAdmin();
        $message = null;
        $errors = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            [$message, $errors] = $this->handleAction($userId);
        }

        $this->view('logistics/index', [
            'dashboard' => $this->service->dashboardData($role, $userId),
            'role' => $role,
            'message' => $message,
            'errors' => $errors
        ]);
    }

    public function included()
    {
        [$role, $userId] = $this->requireProviderOrAdmin();
        $this->view('logistics/included', [
            'dashboard' => $this->service->includedLogicData($role, $userId),
            'role' => $role
        ]);
    }

    public function paymentReport()
    {
        [$role, $userId] = $this->requireProviderOrAdmin();
        $this->view('logistics/payment_report', [
            'report' => $this->service->paymentReportData($role, $userId),
            'role' => $role
        ]);
    }

    public function downloadPaymentReport()
    {
        [$role, $userId] = $this->requireProviderOrAdmin();
        $report = $this->service->paymentReportData($role, $userId);
        $pdf = $this->service->paymentReportPdf($report);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $report['report_id'] . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function handleAction($userId)
    {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'create_completion_report') {
                $this->service->createCompletionReport(
                    (int) ($_POST['booking_id'] ?? 0),
                    trim($_POST['report_details'] ?? ''),
                    $userId
                );
                return ['Completion report saved and admin confirmation notification sent.', []];
            }

            if ($action === 'confirm_cash_payment') {
                $this->service->confirmCashPayment((int) ($_POST['payment_id'] ?? 0), $userId);
                return ['Cash payment marked as collected from pet owner.', []];
            }

            if ($action === 'mark_transferred') {
                $this->service->markTransferred((int) ($_POST['payment_id'] ?? 0), $userId);
                return ['Platform due amount marked as transferred.', []];
            }
        } catch (RuntimeException $e) {
            return [null, [$e->getMessage()]];
        }

        return [null, ['Invalid action.']];
    }

    private function requireProviderOrAdmin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/login');
            exit;
        }

        $role = $_SESSION['role'] ?? 'pet_owner';
        if (!in_array($role, ['service_provider', 'admin'], true)) {
            http_response_code(403);
            die('Access denied. This page is available for service providers and admins only.');
        }

        return [$role, (int) $_SESSION['user_id']];
    }
}
