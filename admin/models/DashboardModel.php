<?php

require_once __DIR__ . '/../../Paw Hubs/app/core/Database.php';

class DashboardModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getDashboardStats()
    {
        return [
            'total_users'      => $this->getCount('users'),
            'total_providers'  => $this->getCount('service_providers') + $this->getCount('veterinarians'),
            'total_pets'       => $this->getCount('pets'),
            'pending_disputes' => $this->getPendingDisputesCount(),
        ];
    }

    public function getRecentDisputes($limit = 5)
    {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("
            SELECT * FROM disputes 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserGrowthData()
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%b') as month, COUNT(*) as count 
            FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY created_at ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrdersGrowthData()
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%b') as month, COUNT(*) as count 
            FROM orders 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY created_at ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCount($table)
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM `$table`");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getPendingDisputesCount()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM disputes WHERE status = 'pending'");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}