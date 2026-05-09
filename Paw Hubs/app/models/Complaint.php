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
        $sql = "SELECT d.*, u.username 
                FROM disputes d
                LEFT JOIN users u ON u.username = d.user_name
                WHERE d.user_name = (SELECT username FROM users WHERE id = ? LIMIT 1)
                ORDER BY d.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $db = self::getDb();
        $sql = "INSERT INTO disputes 
                (user_name, provider_name, issue, user_msg, status, amount, date)
                VALUES (?, ?, ?, ?, 'pending', ?, CURRENT_DATE())";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['user_name'],
            $data['provider_name'],
            $data['issue'],
            $data['user_msg'],
            $data['amount'] ?? 0.00
        ]);
    }
}