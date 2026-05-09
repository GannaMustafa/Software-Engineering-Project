<?php

require_once '../app/core/Database.php';

class Service
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllActive(): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   sp.business_name,
                   sp.rating
            FROM services s
            LEFT JOIN service_providers sp ON sp.id = s.provider_id
            WHERE s.status = 'active'
            ORDER BY s.category, s.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   sp.business_name,
                   sp.rating
            FROM services s
            LEFT JOIN service_providers sp ON sp.id = s.provider_id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}