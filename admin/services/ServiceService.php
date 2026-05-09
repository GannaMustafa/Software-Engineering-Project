<?php

require_once __DIR__ . '/../models/ServiceModel.php';

class ServiceService
{
    private $model;

    public function __construct()
    {
        $this->model = new ServiceModel();
    }

    public function getAll()
    {
        return $this->model->getAllServices();
    }

    public function filter($search, $performed_by)
    {
        return $this->model->searchAndFilter($search, $performed_by);
    }

    public function getById($id)
    {
        return $this->model->getById($id);
    }

    public function handleAction($action, $id, $data = [])
    {
        if ($action === 'add' || $action === 'edit') {
            $success = $this->model->saveService(array_merge(['id' => $id], $data));
            return $success 
                ? ($action === 'add' ? "New service added successfully." : "Service updated successfully.")
                : "Operation failed.";
        }
        
        if ($action === 'delete') {
            $this->model->deleteService($id);
            return "Service #$id has been deleted.";
        }
        return '';
    }
}