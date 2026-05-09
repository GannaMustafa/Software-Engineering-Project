<?php

require_once '../app/core/Database.php';

class Review
{
    public static function allWithDetails(): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT r.*, s.name AS service_name, sp.business_name AS service_provider_name, p2.business_name AS direct_provider_name
                FROM reviews r
                LEFT JOIN services s ON s.id = r.service_id
                LEFT JOIN service_providers sp ON sp.id = s.provider_id
                LEFT JOIN service_providers p2 ON p2.id = r.service_id
                ORDER BY r.created_at DESC";

        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allServices(): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query('SELECT id, name FROM services ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allProviders(): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query('SELECT id, business_name FROM service_providers ORDER BY business_name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getOwnerIdByUserId(int $userId): ?int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id FROM pet_owners WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);
        return $owner['id'] ?? null;
    }

    public static function add(int $ownerId, int $serviceId, int $rating, string $comment): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO reviews (owner_id, service_id, rating, comment) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$ownerId, $serviceId, $rating, $comment]);
    }
}
