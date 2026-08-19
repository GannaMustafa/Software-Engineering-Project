<?php

require_once __DIR__ . '/../services/KYCService.php';
require_once __DIR__ . '/../models/KYCModel.php';  

class KYCController
{
    private $service;

    public function __construct()
    {
        $this->service = new KYCService();
    }

    public function index()
    {
        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $list = $this->service->getAll($status, $search);

        foreach ($list as &$item) {
            $item['docs'] = ($item['role'] === 'vet' || $item['user_role'] === 'vet') 
                ? ['National ID', 'Medical License', 'University Certificate']
                : ['Business License', 'Owner ID', 'Tax Certificate'];
        }

        return [
            'list'   => $list,
            'status' => $status,
            'search' => htmlspecialchars($search),
            'stats'  => $this->service->getStats(),
            'view'   => $this->service->getById((int)($_GET['view'] ?? 0))
        ];
    }

    public function postAction()
    {
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $id     = (int)($_POST['kyc_id'] ?? 0);

            $admin_id = $_SESSION['admin_id'] ?? 1; 
            $message = $this->service->handleAction($action, $id, $admin_id);
        }

        return $message;
    }
}