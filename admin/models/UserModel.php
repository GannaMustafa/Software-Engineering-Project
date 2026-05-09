<?php

require_once __DIR__ . '/../../Paw Hubs/app/core/Database.php';

class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllUsers()
    {
        $stmt = $this->db->query("
            SELECT u.id, u.username, u.email, u.phone, u.role, u.status, u.created_at,
                   po.address
            FROM users u
            JOIN pet_owners po ON po.user_id = u.id
            WHERE u.role = 'pet_owner'
            ORDER BY u.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAndFilter($search = '', $status = 'all')
    {
        $sql = "
            SELECT u.id, u.username, u.email, u.phone, u.role, u.status, u.created_at,
                   po.address
            FROM users u
            JOIN pet_owners po ON po.user_id = u.id
            WHERE u.role = 'pet_owner'
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.username LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== 'all') {
            $sql .= " AND u.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email, u.phone, u.role, u.status, u.created_at,
                   po.address
            FROM users u
            JOIN pet_owners po ON po.user_id = u.id
            WHERE u.id = ? AND u.role = 'pet_owner'
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'pet_owner'");
        return $stmt->execute([$status, $id]);
    }

    public function deleteUser($id)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM pet_owners WHERE user_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'pet_owner'");
            $result = $stmt->execute([$id]);

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}