<?php

require_once '../app/core/Database.php';

class Booking
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $serviceId, $petId, $startDate, $endDate, $specialInstructions = '', $serviceProviderId = null, ?string $forceStatus = null)
    {
        if ($forceStatus === 'waiting') {
            $status = 'waiting';
        } else {
            $status = $this->canConfirmBooking($serviceId, $serviceProviderId, $startDate, $endDate) ? 'confirmed' : 'waiting';
        }

        $sql = "INSERT INTO bookings (user_id, service_id, service_provider_id, pet_id, start_date, end_date, special_instructions, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$userId, $serviceId, $serviceProviderId, $petId, $startDate, $endDate, $specialInstructions, $status])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function canConfirmBooking($serviceId, $serviceProviderId, $startDate, $endDate)
    {
        if (!$serviceProviderId) {
            return false;
        }

        $capacity = $this->getServiceCapacity($serviceId);
        if ($capacity <= 0) {
            return false;
        }

        $confirmedBookings = $this->getConfirmedBookingCount($serviceId, $startDate, $endDate);
        return $confirmedBookings < $capacity;
    }

    public function getAvailableSlotsForService($serviceId, $startDate = null, $endDate = null)
    {
        $capacity = $this->getServiceCapacity($serviceId);
        if ($capacity <= 0) {
            return 0;
        }

        $confirmedBookings = $this->getConfirmedBookingCount($serviceId, $startDate, $endDate);
        return max(0, $capacity - $confirmedBookings);
    }

    public function getConfirmedBookingCount($serviceId, $startDate = null, $endDate = null)
    {
        $sql = "SELECT COUNT(*) as count FROM bookings
                WHERE service_id = ? 
                AND status = 'confirmed'";

        $params = [$serviceId];

        if ($startDate && $endDate) {
            $sql .= " AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?) OR (start_date >= ? AND end_date <= ?))";
            $params = array_merge($params, [$startDate, $startDate, $endDate, $endDate, $startDate, $endDate]);
        } else {
            $sql .= " AND end_date >= CURDATE()";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getServiceCapacity($serviceId)
    {
        $sql = "SELECT s.name, s.category, sp.service_type
                FROM services s
                LEFT JOIN service_providers sp ON sp.id = s.provider_id
                WHERE s.id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$serviceId]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        $name = strtolower($service['name'] ?? '');
        $category = strtolower((string) ($service['category'] ?? ''));
        $type = strtolower((string) ($service['service_type'] ?? ''));

        if (str_contains($name, 'overnight') || str_contains($type, 'overnight')) {
            return 2;
        }

        if (str_contains($name, 'pet sitting') || str_contains($type, 'pet sitter')) {
            return 3;
        }

        if (str_contains($name, 'dog walking') || str_contains($type, 'walker')) {
            return 4;
        }

        if (str_contains($category, 'pet care')) {
            return 2;
        }

        return 2;
    }

    public function checkAvailability($serviceProviderId, $startDate, $endDate)
    {
        if (!$serviceProviderId) {
            return false; // If no provider assigned, mark as pending
        }

        // Get confirmed bookings for this provider in the date range
        $sql = "SELECT COUNT(*) as count FROM bookings 
                WHERE service_provider_id = ? 
                AND status = 'confirmed'
                AND (
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date >= ? AND end_date <= ?)
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$serviceProviderId, $startDate, $startDate, $endDate, $endDate, $startDate, $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no conflicts, provider is available
        return $result['count'] == 0;
    }

    public function getById($bookingId)
    {
        $sql = "SELECT b.*, s.name as service_name, sp.business_name as provider_name, p.name as pet_name 
                FROM bookings b
                LEFT JOIN services s ON b.service_id = s.id
                LEFT JOIN service_providers sp ON b.service_provider_id = sp.id
                LEFT JOIN pets p ON b.pet_id = p.id
                WHERE b.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUser($userId)
    {
        $sql = "SELECT b.*, s.name as service_name, sp.business_name as provider_name, p.name as pet_name 
                FROM bookings b
                LEFT JOIN services s ON b.service_id = s.id
                LEFT JOIN service_providers sp ON b.service_provider_id = sp.id
                LEFT JOIN pets p ON b.pet_id = p.id
                WHERE b.user_id = ?
                ORDER BY b.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
