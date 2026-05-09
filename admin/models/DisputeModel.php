<?php

require_once __DIR__ . '/../../Paw Hubs/app/core/Database.php';

class DisputeModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllDisputes()
    {
        $stmt = $this->db->query("
            SELECT * FROM disputes 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAndFilter($search = '', $status = 'all')
    {
        $sql = "SELECT * FROM disputes WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (user_name LIKE ? OR provider_name LIKE ? OR issue LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM disputes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function resolveDispute($id, $decision, $note = '')
    {
        $stmt = $this->db->prepare("
            UPDATE disputes 
            SET status = 'resolved',
                resolution = ?,
                resolved_at = NOW(),
                admin_note = ?
            WHERE id = ?
        ");
        return $stmt->execute([$decision, $note, $id]);
    }
}