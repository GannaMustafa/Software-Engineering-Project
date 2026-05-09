<?php

require_once __DIR__ . '/../models/SurgeryModel.php';

class SurgeryService
{
    private $model;

    public function __construct()
    {
        $this->model = new SurgeryModel();
    }

    public function getAll()
    {
        return $this->model->getAllSurgeries();
    }

    public function filter($search, $status)
    {
        return $this->model->searchAndFilter($search, $status);
    }

    public function getById($id)
    {
        return $this->model->getById($id);
    }

    public function handleAction($action, $id, $data = [])
    {
        if ($action === 'approve') {
            $data['status'] = 'approved';
            $this->model->saveSurgery($data);
            return "Surgery #$id approved and scheduled.";
        }
        if ($action === 'reject') {
            $data['status'] = 'rejected';
            $this->model->saveSurgery($data);
            return "Surgery request #$id rejected.";
        }
        if ($action === 'complete') {
            $data['status'] = 'completed';
            $this->model->saveSurgery($data);
            return "Surgery #$id marked as completed.";
        }
        if ($action === 'delete') {
            $this->model->deleteSurgery($id);
            return "Surgery #$id deleted permanently.";
        }
        return '';
    }
}