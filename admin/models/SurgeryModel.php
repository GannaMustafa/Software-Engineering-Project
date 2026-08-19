<?php

require_once __DIR__ . '/../../Paw Hubs/app/core/Database.php';

class SurgeryModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllSurgeries()
    {
        $stmt = $this->db->query("
            SELECT mp.*,
                   p.name        AS pet_name,
                   p.species     AS pet_species,
                   p.breed       AS pet_breed,
                   u.username    AS owner,
                   u.email       AS owner_email,
                   COALESCE(v.username, vet.specialization, 'General Vet') AS provider,
                   vet.user_id   AS vet_user_id
            FROM medical_procedures mp
            LEFT JOIN pets p         ON mp.pet_id  = p.id
            LEFT JOIN pet_owners po  ON p.owner_id = po.id
            LEFT JOIN users u        ON po.user_id = u.id
            LEFT JOIN veterinarians vet ON mp.vet_id = vet.id
            LEFT JOIN users v        ON vet.user_id = v.id
            ORDER BY mp.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAndFilter($search = '', $status = 'all')
    {
        $sql = "
            SELECT mp.*,
                   p.name        AS pet_name,
                   p.species     AS pet_species,
                   p.breed       AS pet_breed,
                   u.username    AS owner,
                   u.email       AS owner_email,
                   COALESCE(v.username, vet.specialization, 'General Vet') AS provider,
                   vet.user_id   AS vet_user_id
            FROM medical_procedures mp
            LEFT JOIN pets p         ON mp.pet_id  = p.id
            LEFT JOIN pet_owners po  ON p.owner_id = po.id
            LEFT JOIN users u        ON po.user_id = u.id
            LEFT JOIN veterinarians vet ON mp.vet_id = vet.id
            LEFT JOIN users v        ON vet.user_id = v.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR u.username LIKE ? OR mp.procedure_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== 'all') {
            $sql .= " AND mp.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY mp.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT mp.*,
                   p.name        AS pet_name,
                   p.species     AS pet_species,
                   p.breed       AS pet_breed,
                   p.age         AS pet_age,
                   p.weight      AS pet_weight,
                   p.gender      AS pet_gender,
                   p.allergies   AS pet_allergies,
                   p.medical_notes AS pet_medical_notes,
                   p.vaccination_status AS pet_vaccination_status,
                   u.username    AS owner,
                   u.email       AS owner_email,
                   u.phone       AS owner_phone,
                   COALESCE(v.username, vet.specialization, 'General Vet') AS provider,
                   vet.user_id   AS vet_user_id,
                   vet.specialization AS vet_specialization,
                   vet.license_number AS vet_license
            FROM medical_procedures mp
            LEFT JOIN pets p         ON mp.pet_id  = p.id
            LEFT JOIN pet_owners po  ON p.owner_id = po.id
            LEFT JOIN users u        ON po.user_id = u.id
            LEFT JOIN veterinarians vet ON mp.vet_id = vet.id
            LEFT JOIN users v        ON vet.user_id = v.id
            WHERE mp.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function approveSurgery($id, $scheduled_date, $room, $scheduled_time)
    {
        $notes = "Room: $room | Time: $scheduled_time | Approved by Admin";

        $stmt = $this->db->prepare("
            UPDATE medical_procedures
            SET status         = 'approved',
                procedure_date = ?,
                room           = ?,
                scheduled_time = ?,
                notes          = CONCAT(COALESCE(notes, ''), '\n\n', ?)
            WHERE id = ?
        ");
        return $stmt->execute([$scheduled_date, $room, $scheduled_time, $notes, $id]);
    }

    public function rescheduleSurgery($id, $new_date, $room, $new_time, $reason)
    {
        $notes = "Rescheduled — Room: $room | New Date: $new_date | Time: $new_time | Reason: $reason";

        $stmt = $this->db->prepare("
            UPDATE medical_procedures
            SET status         = 'rescheduled',
                procedure_date = ?,
                room           = ?,
                scheduled_time = ?,
                notes          = CONCAT(COALESCE(notes, ''), '\n\n', ?)
            WHERE id = ?
        ");
        return $stmt->execute([$new_date, $room, $new_time, $notes, $id]);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE medical_procedures SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Send a notification to a user (vet) via the notifications table.
     */
    public function notifyUser($user_id, $title, $message, $type = 'surgery')
    {
        if (!$user_id) return false;
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$user_id, $title, $message, $type]);
    }
}
