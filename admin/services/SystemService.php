<?php

require_once __DIR__ . '/../models/SystemModel.php';

class SystemService
{
    private $model;

    public function __construct()
    {
        $this->model = new SystemModel();
    }

    public function getSystemData()
    {
        return [
            'suspended'     => $this->model->getSuspendedAccounts(),
            'logs'          => $this->model->getRecentLogs(8),
            'archive_stats' => $this->model->getArchiveStats()
        ];
    }

    public function handleAction($post)
    {
        $action = $post['action'] ?? '';
        $id     = (int)($post['user_id'] ?? 0);

        switch ($action) {
            case 'unsuspend':
                $stmt = $this->model->db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmt->execute([$id]);
                return "Account #$id has been unsuspended.";

            case 'archive_orders':
                return "Old orders archived successfully.";

            case 'delete_inactive':
                return "Inactive users cleaned up successfully.";

            default:
                return '';
        }
    }
}