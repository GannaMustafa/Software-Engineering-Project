<?php

require_once __DIR__ . '/../models/KYCModel.php';

class KYCService
{
    public function getAll($status = 'all', $search = '')
    {
        return KYCModel::getAllKYC($status, $search);
    }

    public function getById($id)
    {
        return KYCModel::getById($id);
    }

    public function getStats()
    {
        return KYCModel::getStats();
    }

    public function handleAction($action, $id, $admin_id = null, $note = '')
    {
        if ($action === 'approve') {
            KYCModel::approve($id, $admin_id);
            return "KYC #$id has been approved successfully.";
        }

        if ($action === 'reject') {
            KYCModel::reject($id, $admin_id, $note);
            return "KYC #$id has been rejected.";
        }

        return '';
    }
}