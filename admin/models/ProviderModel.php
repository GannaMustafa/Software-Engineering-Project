<?php
require_once __DIR__ . '/../../Paw Hubs/app/core/Database.php';
require_once __DIR__ . '/KYCModel.php';

class ProviderModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    public function getAllProviders()   
    {
        $sql = "
            SELECT * FROM (
                SELECT v.id, u.username as name, u.email, u.phone, 'vet' as role, 
                       COALESCE(u.status, 'active') as status, COALESCE(k.status, 'approved') as kyc,
                       0 as rating, 0 as earnings
                FROM veterinarians v 
                JOIN users u ON v.user_id = u.id 
                LEFT JOIN kyc_verifications k ON k.user_id = u.id
                
                UNION ALL
                
                SELECT sp.id, u.username as name, u.email, u.phone, 'provider' as role, 
                       COALESCE(u.status, 'active') as status, COALESCE(k.status, 'approved') as kyc,
                       COALESCE(sp.rating, 0) as rating, 0 as earnings
                FROM service_providers sp 
                JOIN users u ON sp.user_id = u.id 
                LEFT JOIN kyc_verifications k ON k.user_id = u.id
                
                UNION ALL
                
                SELECT ven.id, u.username as name, u.email, u.phone, 'vendor' as role, 
                       IF(ven.is_active=1,'active','suspended') as status, 'approved' as kyc,
                       0 as rating, ven.balance as earnings
                FROM vendors ven
                LEFT JOIN users u ON ven.user_id = u.id
            ) as all_providers
            ORDER BY name ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAndFilter($search = '', $status = 'all', $role = 'all')
    {
        $sql = "
            SELECT * FROM (
                SELECT v.id, u.username as name, u.email, u.phone, 'vet' as role, 
                       COALESCE(u.status,'active') as status, COALESCE(k.status,'approved') as kyc,
                       0 as rating, 0 as earnings
                FROM veterinarians v 
                JOIN users u ON v.user_id = u.id 
                LEFT JOIN kyc_verifications k ON k.user_id = u.id
                
                UNION ALL
                
                SELECT sp.id, u.username as name, u.email, u.phone, 'provider' as role, 
                       COALESCE(u.status,'active') as status, COALESCE(k.status,'approved') as kyc,
                       COALESCE(sp.rating, 0) as rating, 0 as earnings
                FROM service_providers sp 
                JOIN users u ON sp.user_id = u.id 
                LEFT JOIN kyc_verifications k ON k.user_id = u.id
                
                UNION ALL
                
                SELECT ven.id, ven.name, '' as email, '' as phone, 'vendor' as role, 
                       IF(ven.is_active=1,'active','suspended') as status, 'approved' as kyc,
                       0 as rating, ven.balance as earnings
                FROM vendors ven
            ) as p
            WHERE 1=1
        ";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND name LIKE ?";
            $params[] = "%$search%";
        }
        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        if ($role !== 'all') {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT id, name, '' as email, '' as phone, 'vendor' as role, 
                                    IF(is_active=1,'active','suspended') as status, 'approved' as kyc, balance as earnings 
                                    FROM vendors WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) return $data;

        $stmt = $this->db->prepare("
            SELECT v.id, u.username as name, u.email, u.phone, 'vet' as role, 
                   COALESCE(u.status,'active') as status, COALESCE(k.status,'approved') as kyc
            FROM veterinarians v 
            JOIN users u ON v.user_id = u.id 
            LEFT JOIN kyc_verifications k ON k.user_id = u.id 
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $data['services'] = ['General Veterinary Services'];
            return $data;
        }

        $stmt = $this->db->prepare("
            SELECT ven.id, COALESCE(u.username, ven.name) as name, u.email, u.phone, 
                   'vendor' as role, IF(ven.is_active=1,'active','suspended') as status, 
                   'approved' as kyc, ven.balance as earnings
            FROM vendors ven
            LEFT JOIN users u ON ven.user_id = u.id
            WHERE ven.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) return $data;

       $stmt = $this->db->prepare("
            SELECT v.id, u.username as name, u.email, u.phone, 'vet' as role, 
                   COALESCE(u.status,'active') as status, COALESCE(k.status,'approved') as kyc
            FROM veterinarians v 
            JOIN users u ON v.user_id = u.id 
            LEFT JOIN kyc_verifications k ON k.user_id = u.id 
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $data['services'] = ['General Veterinary Services'];
            return $data;
        }

        $stmt = $this->db->prepare("
            SELECT sp.id, u.username as name, u.email, u.phone, 'provider' as role, 
                   COALESCE(u.status,'active') as status, COALESCE(k.status,'approved') as kyc,
                   COALESCE(sp.rating, 0) as rating
            FROM service_providers sp 
            JOIN users u ON sp.user_id = u.id 
            LEFT JOIN kyc_verifications k ON k.user_id = u.id 
            WHERE sp.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $data['services'] = ['General Services'];
            return $data;
        }

        return null;
    }

    public function createProvider($name, $email, $password, $phone, $role)
    {
        $this->db->beginTransaction();
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $user_id = null;

            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, phone, password, role, status) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$name, $email, $phone, $hashed, $role]);
            $user_id = $this->db->lastInsertId();

            if ($role === 'vet') {
                $this->db->prepare("INSERT INTO veterinarians (user_id) VALUES (?)")
                         ->execute([$user_id]);
            } 
            elseif ($role === 'provider') {
                $this->db->prepare("INSERT INTO service_providers (user_id, business_name) VALUES (?, ?)")
                         ->execute([$user_id, $name]);
            } 
            elseif ($role === 'vendor') {
                $this->db->prepare("
                    INSERT INTO vendors (user_id, name, balance, commission_rate, tax_rate, is_active) 
                    VALUES (?, ?, 0.00, 0.1000, 0.1400, 1)
                ")->execute([$user_id, $name]);
            }

            if ($user_id && in_array($role, ['vet', 'provider'])) {
                KYCModel::createKYC($user_id, $name, $email, $role);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Create Provider Error: " . $e->getMessage());
            return false;
        }
    }

    public function suspendProvider($id)
    {
        $provider = $this->getById($id);
        if (!$provider) return false;

        if ($provider['role'] === 'vendor') {
            $stmt = $this->db->prepare("UPDATE vendors SET is_active = 0 WHERE id = ?");
            return $stmt->execute([$id]);
        } else {
            $user_id = $this->getUserId($id, $provider['role']);
            if ($user_id) {
                $stmt = $this->db->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
                return $stmt->execute([$user_id]);
            }
        }
        return false;
    }

    public function unsuspendProvider($id)
    {
        $provider = $this->getById($id);
        if (!$provider) return false;

        if ($provider['role'] === 'vendor') {
            $stmt = $this->db->prepare("UPDATE vendors SET is_active = 1 WHERE id = ?");
            return $stmt->execute([$id]);
        } else {
            $user_id = $this->getUserId($id, $provider['role']);
            if ($user_id) {
                $stmt = $this->db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                return $stmt->execute([$user_id]);
            }
        }
        return false;
    }

    
    public function deleteProvider($id)
    {
        $provider = $this->getById($id);
        if (!$provider) return false;

        $this->db->beginTransaction();
        try {
            if ($provider['role'] === 'vendor') {
                // Get user_id first
                $stmt = $this->db->prepare("SELECT user_id FROM vendors WHERE id = ?");
                $stmt->execute([$id]);
                $user_id = $stmt->fetchColumn();

                $this->db->prepare("DELETE FROM vendors WHERE id = ?")->execute([$id]);
                if ($user_id) {
                    $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                }
            } else {
                $user_id = $this->getUserId($id, $provider['role']);
                if ($user_id) {
                    $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                }
                if ($provider['role'] === 'vet') {
                    $this->db->prepare("DELETE FROM veterinarians WHERE id = ?")->execute([$id]);
                } else {
                    $this->db->prepare("DELETE FROM service_providers WHERE id = ?")->execute([$id]);
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Delete Provider Error: " . $e->getMessage());
            return false;
        }
    }

    private function getUserId($id, $role)
    {
        if ($role === 'vet') {
            $stmt = $this->db->prepare("SELECT user_id FROM veterinarians WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetchColumn();
        }
        if ($role === 'provider') {
            $stmt = $this->db->prepare("SELECT user_id FROM service_providers WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetchColumn();
        }
        return null;
    }
}