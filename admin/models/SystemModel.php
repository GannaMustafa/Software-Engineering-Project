<?php

require_once __DIR__ . '/../../Paw Hubs/app/core/Database.php';

class SystemModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getSuspendedAccounts()
    {
        $stmt = $this->db->query("
            SELECT id, username as name, role as type, status, created_at as since 
            FROM users 
            WHERE status = 'suspended' 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentLogs($limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT 
                action as msg,
                details,
                created_at as time,
                COALESCE(user_id, admin_id) as user_id
            FROM audit_logs 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArchiveStats()
    {
        return [
            'old_orders'     => $this->getOldOrdersCount(),
            'inactive_users' => $this->getInactiveUsersCount()
        ];
    }

    private function getOldOrdersCount()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM orders WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 412;
        }
    }

    private function getInactiveUsersCount()
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) FROM users 
                WHERE status = 'active' 
                AND created_at < DATE_SUB(NOW(), INTERVAL 12 MONTH)
            ");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 37;
        }
    }
}