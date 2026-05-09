<?php

class Service
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllWithProviders(): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.name, s.category, s.description, s.discount_percentage,
                    sp.business_name, sp.service_type, sp.rating
             FROM services s
             LEFT JOIN service_providers sp ON sp.id = s.provider_id
             ORDER BY s.discount_percentage DESC, sp.rating DESC, s.name ASC"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSittersWithProviders(): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.name, s.category, s.description, s.discount_percentage,
                    sp.business_name, sp.service_type, sp.rating
             FROM services s
             LEFT JOIN service_providers sp ON sp.id = s.provider_id
             WHERE s.category = 'Pet Care'
             ORDER BY s.discount_percentage DESC, sp.rating DESC, s.name ASC"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.name, s.category, s.description, s.discount_percentage,
                    sp.business_name, sp.service_type, sp.rating
             FROM services s
             LEFT JOIN service_providers sp ON sp.id = s.provider_id
             WHERE s.id = ?"
        );

        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
