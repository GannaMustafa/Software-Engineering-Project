<?php

require_once __DIR__ . '/../models/ProviderModel.php';

class ProviderService
{
    private $model;

    public function __construct()
    {
        $this->model = new ProviderModel();
    }

    public function getAll()
    {
        return $this->model->getAllProviders();
    }

    public function filter($search, $status, $role)
    {
        return $this->model->searchAndFilter($search, $status, $role);
    }

    public function getById($id)
    {
        return $this->model->getById($id);
    }

    public function handleAction($action, $id = null, $postData = [])
    {
        if ($action === 'add' && !empty($postData)) {
            $success = $this->model->createProvider(
                $postData['name'] ?? '',
                $postData['email'] ?? '',
                $postData['password'] ?? '',
                $postData['phone'] ?? '',
                $postData['role'] ?? 'provider'
            );
            
            return $success 
                ? "New " . ucfirst($postData['role'] ?? 'provider') . " added successfully." 
                : "Failed to add. Email may already exist or fields are missing.";
        }

        if (!$id) return 'Invalid ID';

        switch ($action) {
            case 'suspend':
                return $this->model->suspendProvider($id) ? "Account suspended successfully." : "Failed to suspend account.";
            case 'unsuspend':
                return $this->model->unsuspendProvider($id) ? "Account unsuspended successfully." : "Failed to unsuspend account.";
            case 'delete':
                return $this->model->deleteProvider($id) ? "Account deleted permanently." : "Failed to delete account.";
            default:
                return 'Unknown action';
        }
    }
}