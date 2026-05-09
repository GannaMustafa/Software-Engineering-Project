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
                   p.name AS pet_name,
                   u.username AS owner,
                   COALESCE(v.username, vet.specialization, 'General Vet') AS provider
            FROM medical_procedures mp
            LEFT JOIN pets p ON mp.pet_id = p.id
            LEFT JOIN pet_owners po ON p.owner_id = po.id
            LEFT JOIN users u ON po.user_id = u.id
            LEFT JOIN veterinarians vet ON mp.vet_id = vet.id
            LEFT JOIN users v ON vet.user_id = v.id
            ORDER BY mp.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAndFilter($search = '', $status = 'all')
    {
        $sql = "
            SELECT mp.*,
                   p.name AS pet_name,
                   u.username AS owner,
                   COALESCE(v.username, vet.specialization, 'General Vet') AS provider
            FROM medical_procedures mp
            LEFT JOIN pets p ON mp.pet_id = p.id
            LEFT JOIN pet_owners po ON p.owner_id = po.id
            LEFT JOIN users u ON po.user_id = u.id
            LEFT JOIN veterinarians vet ON mp.vet_id = vet.id
            LEFT JOIN users v ON vet.user_id = v.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR u.username LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== 'all') {
            $sql .= " AND mp.status = ?";
            $params[] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT mp.*,
                   p.name AS pet_name,
                   u.username AS owner,
                   COALESCE(v.username, vet.specialization, 'General Vet') AS provider
            FROM medical_procedures mp
            LEFT JOIN pets p ON mp.pet_id = p.id
            LEFT JOIN pet_owners po ON p.owner_id = po.id
            LEFT JOIN users u ON po.user_id = u.id
            LEFT JOIN veterinarians vet ON mp.vet_id = vet.id
            LEFT JOIN users v ON vet.user_id = v.id
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
            SET status = 'approved',
                procedure_date = ?,
                room = ?,
                scheduled_time = ?,
                notes = CONCAT(COALESCE(notes, ''), '\n\n', ?)
            WHERE id = ?
        ");
        return $stmt->execute([$scheduled_date, $room, $scheduled_time, $notes, $id]);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE medical_procedures SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}