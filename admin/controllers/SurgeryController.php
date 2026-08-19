<?php

require_once __DIR__ . '/../services/SurgeryService.php';

class SurgeryController
{
    private $service;

    public function __construct()
    {
        $this->service = new SurgeryService();
    }

    public function index()
    {
        $search = htmlspecialchars($_GET['search'] ?? '');
        $status = $_GET['status'] ?? 'all';

        return [
            'list'     => $this->service->filter($search, $status),
            'search'   => $search,
            'status'   => $status,
            'surgery'  => $this->service->getById((int)($_GET['view'] ?? 0)),
            'all'      => $this->service->getAll()
        ];
    }

    public function postAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return '';

        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['surgery_id'] ?? 0);

        $data = [
            'scheduled_date'    => $_POST['scheduled_date'] ?? null,
            'scheduled_time'    => $_POST['scheduled_time'] ?? '',
            'room'              => $_POST['room'] ?? '',
            'reschedule_reason' => $_POST['reschedule_reason'] ?? 'Rescheduled by administrator'
        ];

        $message = $this->service->handleAction($action, $id, $data);

        header("Location: surgery-management.php?message=" . urlencode($message) .
               (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') .
               (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''));
        exit;
    }
}