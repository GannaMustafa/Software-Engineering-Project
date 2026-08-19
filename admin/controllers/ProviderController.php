<?php

require_once __DIR__ . '/../services/ProviderService.php';

class ProviderController
{
    private $service;

    public function __construct()
    {
        $this->service = new ProviderService();
    }

    public function index()
    {
        $search = htmlspecialchars($_GET['search'] ?? '');
        $status = $_GET['status'] ?? 'all';
        $role   = $_GET['role'] ?? 'all';

        return [
            'list'     => $this->service->filter($search, $status, $role),
            'search'   => $search,
            'status'   => $status,
            'role'     => $role,
            'profile'  => $this->service->getById((int)($_GET['profile'] ?? 0)),
            'showAdd'  => isset($_GET['add']),
            'all'      => $this->service->getAll()
        ];
    }

    public function postAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return '';
        }

        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['provider_id'] ?? 0);

        $message = $this->service->handleAction($action, $id, $_POST);

        header("Location: provider-management.php?message=" . urlencode($message) . 
               (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') .
               (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '') .
               (isset($_GET['role']) ? '&role=' . urlencode($_GET['role']) : ''));
        exit;
    }
}   