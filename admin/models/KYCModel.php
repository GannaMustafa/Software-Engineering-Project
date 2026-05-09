<?php

require_once __DIR__ . '/../../Paw Hubs/app/core/Database.php';

class KYCModel
{
    private static $db;

    public static function init()
    {
        if (!self::$db) {
            $database = Database::getInstance();
            self::$db = $database->getConnection();
        }
    }

    public static function getAllKYC($status = 'all', $search = '')
    {
        self::init();
        
        $sql = "SELECT k.*, u.username, u.email, u.role as user_role 
                FROM kyc_verifications k 
                LEFT JOIN users u ON k.user_id = u.id 
                WHERE 1=1";

        $params = [];

        if ($status !== 'all' && $status !== '') {
            $sql .= " AND k.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (k.name LIKE ? OR k.email LIKE ? OR u.username LIKE ?)";
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY k.submitted_at DESC";

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$row) {
            $row['submitted'] = $row['submitted_at'] ?? $row['created_at'] ?? null;
        }

        return $results;
    }

    public static function getById($id)
    {
        self::init();
        $stmt = self::$db->prepare("
            SELECT k.*, u.username, u.email 
            FROM kyc_verifications k 
            LEFT JOIN users u ON k.user_id = u.id 
            WHERE k.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $data['submitted'] = $data['submitted_at'] ?? $data['created_at'] ?? null;
        }

        return $data;
    }

    public static function approve($id, $admin_id = null)
    {
        self::init();
        $stmt = self::$db->prepare("
            UPDATE kyc_verifications 
            SET status = 'approved', 
                reviewed_at = NOW(), 
                reviewed_by = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$admin_id, $id]);
    }

    public static function reject($id, $admin_id = null, $note = '')
    {
        self::init();
        $stmt = self::$db->prepare("
            UPDATE kyc_verifications 
            SET status = 'rejected', 
                reviewed_at = NOW(), 
                reviewed_by = ?,
                note = ?
            WHERE id = ?
        ");
        return $stmt->execute([$admin_id, $note, $id]);
    }

    public static function getStats()
    {
        self::init();
        $stmt = self::$db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM kyc_verifications
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'pending'=>0,'approved'=>0,'rejected'=>0];
    }

    public static function createKYC($user_id, $name, $email, $role)
    {
        self::init();
        $stmt = self::$db->prepare("
            INSERT INTO kyc_verifications (user_id, name, email, role) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$user_id, $name, $email, $role]);
    }
}