<?php

require_once __DIR__ . '/../models/SurgeryModel.php';

class SurgeryService
{
    private $model;

    public function __construct()
    {
        $this->model = new SurgeryModel();
    }

    public function getAll() { return $this->model->getAllSurgeries(); }
    public function filter($search, $status) { return $this->model->searchAndFilter($search, $status); }
    public function getById($id) { return $this->model->getById($id); }

    public function handleAction($action, $id, $data = [])
    {
        if ($action === 'approve') {
            return $this->model->approveSurgery(
                $id,
                $data['scheduled_date'] ?? null,
                $data['room'] ?? '',
                $data['scheduled_time'] ?? ''
            ) ? "Surgery #$id has been approved and scheduled." : "Failed to approve surgery.";
        }

        if ($action === 'reschedule') {
            return $this->model->rescheduleSurgery(
                $id,
                $data['scheduled_date'] ?? null,
                $data['room'] ?? '',
                $data['scheduled_time'] ?? '',
                $data['reschedule_reason'] ?? 'Rescheduled by admin'
            ) ? "Surgery #$id has been rescheduled." : "Failed to reschedule surgery.";
        }

        if ($action === 'reject') {
            return $this->model->updateStatus($id, 'rejected') ? "Surgery request rejected." : "Failed.";
        }

        if ($action === 'complete') {
            return $this->model->updateStatus($id, 'completed') ? "Surgery marked as completed." : "Failed.";
        }

        return 'Unknown action';
    }
}