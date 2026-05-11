<?php

require_once __DIR__ . '/../core/Database.php';

class Booking
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $ownerId, int $serviceId, int $petId, string $notes = '', $providerId = null)
    {
        if (empty($providerId)) {
            $providerId = 1; 
        }

        $sql = "INSERT INTO service_bookings 
                (owner_id, provider_id, service_id, pet_id, status, notes, booked_at)
                VALUES (?, ?, ?, ?, 'scheduled', ?, CURRENT_TIMESTAMP())";

        $stmt = $this->db->prepare($sql);

        $success = $stmt->execute([
            $ownerId,
            $providerId,
            $serviceId,
            $petId,
            $notes
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    public function getByUser(int $userId)
    {
        $sql = "SELECT sb.*, 
                       s.name AS service_name,
                       s.category,
                       s.price,
                       sp.business_name AS provider_name,
                       p.name AS pet_name,
                       p.species
                FROM service_bookings sb
                LEFT JOIN services s ON s.id = sb.service_id
                LEFT JOIN service_providers sp ON sp.id = sb.provider_id
                LEFT JOIN pets p ON p.id = sb.pet_id
                WHERE sb.owner_id = ?
                ORDER BY sb.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}