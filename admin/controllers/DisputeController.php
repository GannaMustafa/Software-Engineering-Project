<?php

require_once __DIR__ . '/../services/DisputeService.php';

class DisputeController
{
    private $service;

    public function __construct()
    {
        $this->service = new DisputeService();
    }

    public function index()
    {
        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';

        return [
            'disputes' => $this->service->getFilteredDisputes($status, $search),
            'status'   => $status,
            'search'   => htmlspecialchars($search)
        ];
    }

    public function handlePost()
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resolve') {
            $message = $this->service->resolveDispute($_POST);
        }
        return $message;
    }

    public function getDetails($id)
    {
        return $this->service->getById($id);
    }
}