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
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $id     = (int)($_POST['surgery_id'] ?? 0);

            $data = [
                'id'              => $id,
                'procedure_date'  => $_POST['scheduled_date'] ?? null,
                'notes'           => "Room: " . ($_POST['room'] ?? '') . " | Time: " . ($_POST['scheduled_time'] ?? '')
            ];

            $message = $this->service->handleAction($action, $id, $data);
        }

        return $message;
    }
}