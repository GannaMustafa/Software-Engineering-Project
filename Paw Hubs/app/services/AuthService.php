<?php

require_once '../app/models/User.php';

class AuthService {
    private $userModel;
    private $lastError = "Invalid credentials.";

    public function __construct() {
        $this->userModel = new User();
    }

    public function authenticate($email, $password) {
        $this->lastError = "Invalid credentials.";
        $user = $this->userModel->getByEmail($email);

        if (!$user) {
            return false;
        }

        $storedPassword = $user['password'];
        $looksLikeBcrypt = preg_match('/^\$2[ayb]\$/', $storedPassword);

        if ($looksLikeBcrypt) {
            if (strlen($storedPassword) < 60) {
                $this->lastError = "This account password was saved before the database fix. Please reset the password or create the account again.";
                return false;
            }

            if (password_verify($password, $storedPassword)) {
                return $user;
            }

            return false;
        }

        if (password_get_info($storedPassword)['algoName'] !== 'unknown') {
            if (password_verify($password, $storedPassword)) {
                return $user;
            }

            return false;
        }

        if (hash_equals($storedPassword, $password)) {
            $this->userModel->updatePassword($user['id'], password_hash($password, PASSWORD_DEFAULT));
            return $user;
        }

        return false;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function recordLogin($userId) {
        $db = Database::getInstance()->getConnection();
        $this->writeAuditLog($db, $userId, 'login', 'User logged in successfully.', 'users', $userId);
    }

    public function registerUser($data) {
        // 1. Validation Logic using core Validator
        if (($data['username'] ?? '') === '') return ["error" => "Username is required"];
        if (!validate_email($data['email'] ?? '')) return ["error" => "Invalid email format"];
        if (!validate_phone($data['phone'] ?? '')) return ["error" => "Invalid Egyptian phone number"];
        if (!validate_password($data['password'] ?? '')) return ["error" => "Password must be 8-20 chars with letters and numbers"];

        // 2. Business Logic: check if email exists
        $db = Database::getInstance()->getConnection();
        if (check_unique($db, 'email', 'users', $data['email'])) {
            return ["error" => "Email already exists"];
        }

        // 3. Hash password and save
        $data['password'] = hash_password($data['password']);

        try {
            $db->beginTransaction();

            $userId = $this->userModel->create($data);
            $db->prepare("INSERT INTO pet_owners (user_id, address) VALUES (?, ?)")->execute([
                $userId,
                trim($data['address'] ?? '')
            ]);
            $this->writeAuditLog($db, $userId, 'register', 'New pet owner account created.', 'users', $userId);

            $db->commit();
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            if ((string) $e->getCode() === '23000') {
                return ["error" => "Email already exists"];
            }

            error_log("Paw Hubs registration failed: " . $e->getMessage());
            return ["error" => "Could not create account. Database rejected the registration: " . $e->getMessage()];
        }

        return ["id" => $userId];
    }

    private function writeAuditLog($db, $userId, $action, $details, $entityType = null, $entityId = null) {
        $stmt = $db->prepare("
            INSERT INTO audit_logs (user_id, admin_id, action, details, entity_type, entity_id, ip_address)
            VALUES (?, NULL, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $action,
            $details,
            $entityType,
            $entityId,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
