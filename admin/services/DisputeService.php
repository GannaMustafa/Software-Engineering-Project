<?php

require_once __DIR__ . '/../models/DisputeModel.php';

class DisputeService
{
    private $model;

    public function __construct()
    {
        $this->model = new DisputeModel();
    }

    public function getFilteredDisputes($status, $search)
    {
        return $this->model->searchAndFilter($search, $status);
    }

    public function getById($id)
    {
        return $this->model->getById($id);
    }

    public function resolveDispute($post)
    {
        $id = (int)($post['dispute_id'] ?? 0);
        $decision = trim($post['decision'] ?? '');
        $note = trim($post['note'] ?? '');

        if ($id && $decision) {
            $this->model->resolveDispute($id, $decision, $note);
            return "Dispute #$id has been resolved.";
        }
        return "Failed to resolve dispute.";
    }
}