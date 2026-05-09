<?php

class UserController extends Controller {
    public function index() {
        $this->profile();
    }

    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $userModel = $this->model('User');
        $db = Database::getInstance()->getConnection();
        $errors = [];
        $success = null;
        $userId = $_SESSION['user_id'];
        $userData = $userModel->getById($userId);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'password' => $_POST['new_password'] ?? '',
                'image' => null
            ];

            if (!Validator::required($data['username'])) {
                $errors[] = 'Username is required.';
            }

            if (!Validator::email($data['email'])) {
                $errors[] = 'Invalid email format.';
            } elseif ($userModel->emailExistsForAnotherUser($data['email'], $userId)) {
                $errors[] = 'Email already exists.';
            }

            if ($data['phone'] !== '' && !Validator::phone($data['phone'])) {
                $errors[] = 'Invalid Egyptian phone number.';
            }

            if ($data['password'] !== '') {
                if ($data['password'] !== ($_POST['confirm_password'] ?? '')) {
                    $errors[] = 'Password confirmation does not match.';
                } elseif (!Validator::password($data['password'])) {
                    $errors[] = 'Password must be 8-20 chars with letters and numbers.';
                }
            }

            $uploadedImage = null;
            if (empty($errors)) {
                $imageResult = $this->uploadProfileImage($data['username']);
                if (isset($imageResult['error'])) {
                    $errors[] = $imageResult['error'];
                } elseif (!empty($imageResult['filename'])) {
                    $data['image'] = $imageResult['filename'];
                    $uploadedImage = $imageResult['path'];
                }
            }

            if (empty($errors)) {
                $userModel->updateProfile($userId, $data);
                $userData = $userModel->getById($userId);
                $_SESSION['username'] = $userData['username'];
                $_SESSION['profile_pic'] = $userData['image'] ?? 'default.png';
                $success = 'Profile updated successfully.';
            } elseif ($uploadedImage && file_exists($uploadedImage)) {
                unlink($uploadedImage);
            }
        }

        $owner = $this->fetchOne($db, "SELECT id FROM pet_owners WHERE user_id = ?", [$userId]);
        $ownerId = $owner['id'] ?? null;

        $this->view('user/profile', [
            'user' => $userData,
            'errors' => $errors,
            'success' => $success,
            'history' => [
                'appointments' => $this->appointmentsHistory($db, $userId),
                'orders' => $this->ordersHistory($db, $ownerId),
                'services' => $this->servicesHistory($db, $ownerId)
            ]
        ]);
    }

    public function dbStatus() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->view('user/db_status', ['tables' => $tables]);
    }

    private function appointmentsHistory($db, $userId) {
        if (!$this->tableExists($db, 'appointments')) {
            return [];
        }

        return $this->fetchAll(
            $db,
            "SELECT a.*, p.name AS pet_name, p.species
             FROM appointments a
             LEFT JOIN pets p ON p.id = a.pet_id
             WHERE a.user_id = ?
             ORDER BY a.appointment_date DESC, a.id DESC
             LIMIT 10",
            [$userId]
        );
    }

    private function ordersHistory($db, $ownerId) {
        if (!$ownerId || !$this->tableExists($db, 'orders')) {
            return [];
        }

        return $this->fetchAll(
            $db,
            "SELECT o.*, v.name AS vendor_name
             FROM orders o
             LEFT JOIN vendors v ON v.id = o.vendor_id
             WHERE o.owner_id = ?
             ORDER BY o.id DESC
             LIMIT 10",
            [$ownerId]
        );
    }

    private function servicesHistory($db, $ownerId) {
        if (!$ownerId || !$this->tableExists($db, 'reviews')) {
            return [];
        }

        return $this->fetchAll(
            $db,
            "SELECT r.*, s.provider_id, sp.business_name, sp.service_type
             FROM reviews r
             LEFT JOIN services s ON s.id = r.service_id
             LEFT JOIN service_providers sp ON sp.id = s.provider_id
             WHERE r.owner_id = ?
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT 10",
            [$ownerId]
        );
    }

    private function fetchOne($db, $sql, $params = []) {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchAll($db, $sql, $params = []) {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function tableExists($db, $table) {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ");
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function uploadProfileImage($username) {
        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] === UPLOAD_ERR_NO_FILE) {
            return ['filename' => null];
        }

        if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Profile image upload failed. Please try again.'];
        }

        if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            return ['error' => 'Profile image must be 2MB or smaller.'];
        }

        $tmpPath = $_FILES['profile_image']['tmp_name'];
        $imageInfo = @getimagesize($tmpPath);
        if (!$imageInfo) {
            return ['error' => 'Please upload a valid image file.'];
        }

        $allowedTypes = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp'
        ];

        if (!isset($allowedTypes[$imageInfo[2]])) {
            return ['error' => 'Profile image must be JPG, PNG, or WEBP.'];
        }

        $safeName = preg_replace('/[^\p{L}\p{N}_-]+/u', '_', trim($username));
        $safeName = trim($safeName, '_-') ?: 'user_profile';
        $extension = $allowedTypes[$imageInfo[2]];
        $uploadDir = __DIR__ . '/../../public/uploads';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filename = $safeName . '.' . $extension;
        $targetPath = $uploadDir . '/' . $filename;
        $counter = 1;
        while (file_exists($targetPath)) {
            $filename = $safeName . '_' . $counter . '.' . $extension;
            $targetPath = $uploadDir . '/' . $filename;
            $counter++;
        }

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            return ['error' => 'Could not save the profile image.'];
        }

        return ['filename' => $filename, 'path' => $targetPath];
    }
}
