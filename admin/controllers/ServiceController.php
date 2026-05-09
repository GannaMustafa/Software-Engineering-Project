<?php

require_once __DIR__ . '/../services/ServiceService.php';

class ServiceController
{
    private $service;

    public function __construct()
    {
        $this->service = new ServiceService();
    }

    public function index()
    {
        $search       = htmlspecialchars($_GET['search'] ?? '');
        $performed_by = $_GET['performed_by'] ?? 'all';

        return [
            'list'         => $this->service->filter($search, $performed_by),
            'search'       => $search,
            'performed_by' => $performed_by,
            'service'      => $this->service->getById((int)($_GET['view'] ?? 0)),
            'editService'  => $this->service->getById((int)($_GET['edit'] ?? 0)),
            'showAdd'      => isset($_GET['add']),
            'all'          => $this->service->getAll()
        ];
    }

    public function postAction()
    {
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $id     = (int)($_POST['service_id'] ?? 0);

            $data = [
                'name'              => trim($_POST['name'] ?? ''),
                'category'          => trim($_POST['category'] ?? ''),
                'price'             => (float)($_POST['price'] ?? 0),
                'duration'          => trim($_POST['duration'] ?? ''),
                'performed_by'      => trim($_POST['performed_by'] ?? 'Vet'),
                'description'       => trim($_POST['description'] ?? ''),
                'discount_percentage' => (float)($_POST['discount_percentage'] ?? 0),
                'status'            => 'active'
            ];

            $message = $this->service->handleAction($action, $id, $data);
        }

        return $message;
    }
}