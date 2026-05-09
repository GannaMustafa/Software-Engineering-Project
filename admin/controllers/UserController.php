<?php

require_once __DIR__ . '/../services/UserService.php';

class UserController
{
    private $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function index()
    {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'all';
        $sort   = $_GET['sort'] ?? 'newest';
        $profileId = (int)($_GET['profile'] ?? 0);

        $users = $this->service->filter($search, $status);
        $users = $this->service->sort($users, $sort);

        $profile = $profileId ? $this->service->getById($profileId) : null;

        return [
            'list'    => $users,
            'search'  => htmlspecialchars($search),
            'status'  => $status,
            'sort'    => $sort,
            'profile' => $profile
        ];
    }

    public function postAction()
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $id     = (int)($_POST['user_id'] ?? 0);
            $message = $this->service->handleAction($action, $id);
        }
        return $message;
    }
}