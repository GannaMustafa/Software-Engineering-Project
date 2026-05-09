<?php

require_once '../app/core/Database.php';

class Complaint
{
    private static $db;

    public static function getDb()
    {
        if (!self::$db) {
            self::$db = Database::getInstance()->getConnection();
        }
        return self::$db;
    }

    public static function findByUser($userId)
    {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $db = self::getDb();
        $sql = "INSERT INTO complaints (user_id, order_id, service_id, provider_id, subject, description, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['user_id'],
            $data['order_id'] ?? null,
            $data['service_id'] ?? null,
            $data['provider_id'] ?? null,
            $data['subject'],
            $data['description'],
            'Submitted'
        ]);

        return $db->lastInsertId();
    }

    public static function findById($id, $userId)
    {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM complaints WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
