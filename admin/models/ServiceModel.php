<?php

require_once __DIR__ . '/../../Paw Hubs/app/core/Database.php';

class ServiceModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllServices()
    {
        $stmt = $this->db->query("SELECT * FROM services ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAndFilter($search = '', $performed_by = 'all')
    {
        $sql = "SELECT * FROM services WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($performed_by !== 'all') {
            $results = array_filter($results, fn($s) => ($s['performed_by'] ?? '') === $performed_by);
        }

        return array_values($results);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveService($data)
    {
        if (empty($data['name']) || empty($data['category'])) {
            return false;
        }

        if (!empty($data['id'])) {
            $stmt = $this->db->prepare("
                UPDATE services 
                SET name = ?, category = ?, price = ?, duration = ?, 
                    performed_by = ?, description = ?, 
                    discount_percentage = ?, status = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['name'],
                $data['category'],
                $data['price'],
                $data['duration'],
                $data['performed_by'],
                $data['description'],
                $data['discount_percentage'],
                $data['status'] ?? 'active',
                $data['id']
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO services 
                (name, category, price, duration, performed_by, description, discount_percentage, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['name'],
                $data['category'],
                $data['price'],
                $data['duration'],
                $data['performed_by'],
                $data['description'],
                $data['discount_percentage'],
                $data['status'] ?? 'active'
            ]);
        }
    }

    public function deleteService($id)
    {
        $stmt = $this->db->prepare("DELETE FROM services WHERE id = ?");
        return $stmt->execute([$id]);
    }
}