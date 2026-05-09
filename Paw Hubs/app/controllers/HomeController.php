<?php

class HomeController extends Controller {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'];

        $user = $this->fetchOne($db, "SELECT * FROM users WHERE id = ?", [$userId]);
        $role = $user['role'] ?? ($_SESSION['role'] ?? 'pet_owner');
        $_SESSION['role'] = $role;

        if ($role === 'admin') {
            header("Location: index.php?url=admin/index");
            exit;
        }

        if ($role === 'vet') {
            header("Location: index.php?url=clinical/index");
            exit;
        }

        $owner = $this->fetchOne($db, "SELECT id FROM pet_owners WHERE user_id = ?", [$userId]);
        $ownerId = $owner['id'] ?? null;

        $pets = [];
        $petIds = [];
        if ($ownerId) {
            $pets = $this->fetchAll($db, "SELECT * FROM pets WHERE owner_id IN (?, ?) ORDER BY id DESC", [$ownerId, $userId]);
            $petIds = array_column($pets, 'id');
        } else {
            $pets = $this->fetchAll($db, "SELECT * FROM pets WHERE owner_id = ? ORDER BY id DESC", [$userId]);
            $petIds = array_column($pets, 'id');
        }

        $upcomingAppointment = null;
        if ($this->tableExists($db, 'appointments')) {
            $upcomingAppointment = $this->fetchOne(
                $db,
                "SELECT a.*, p.name AS pet_name
                 FROM appointments a
                 LEFT JOIN pets p ON p.id = a.pet_id
                 WHERE a.user_id = ? AND a.appointment_date >= CURDATE()
                 ORDER BY a.appointment_date ASC
                 LIMIT 1",
                [$userId]
            );
        }

        $stats = [
            'appointment_date' => $upcomingAppointment
                ? date('M j, Y', strtotime($upcomingAppointment['appointment_date']))
                : 'No upcoming',
            'appointment_type' => $upcomingAppointment['appointment_type'] ?? 'Book your first visit',
            'vaccines_due' => $this->countForPets($db, 'vaccines', 'status', 'due', $petIds),
            'health_records' => $this->countHealthRecords($db, $petIds),
            'wellness_score' => $this->wellnessScore($db, $petIds),
            'loyalty_points' => $this->loyaltyPoints($db, $userId)
        ];

        // Fetch recommended marketplace products dynamically from MySQL.
        $recommendedProducts = $this->recommendedMarketplaceItems($db);

        $this->view('home', [
            'user' => $user,
            'pets' => $pets,
            'username' => $user['username'] ?? ($_SESSION['username'] ?? 'Guest'),
            'stats' => $stats,
            'recommendedProducts' => $recommendedProducts
        ]);
    }

    public function addPet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized request.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'];

        $owner = $this->fetchOne($db, "SELECT id FROM pet_owners WHERE user_id = ?", [$userId]);
        if (!$owner) {
            $stmt = $db->prepare("INSERT INTO pet_owners (user_id, address) VALUES (?, '')");
            $stmt->execute([$userId]);
            $ownerId = $db->lastInsertId();
        } else {
            $ownerId = $owner['id'];
        }

        $name = trim($_POST['name'] ?? '');
        $species = trim($_POST['species'] ?? '');
        $breed = trim($_POST['breed'] ?? '');
        $age = max(0, (int) ($_POST['age'] ?? 0));
        $gender = trim($_POST['gender'] ?? 'Unknown');
        $weight = trim($_POST['weight'] ?? '0');
        $weight = is_numeric($weight) ? number_format((float) $weight, 2, '.', '') : '0.00';
        $color = trim($_POST['color'] ?? '');
        $vaccinationStatus = trim($_POST['vaccination_status'] ?? 'Unknown');
        $allergies = trim($_POST['allergies'] ?? '');
        $medicalNotes = trim($_POST['medical_notes'] ?? '');
        $imageName = 'default-pet.png';

        $allowedSpecies = ['Dog', 'Cat', 'Bird', 'Other'];
        $allowedGenders = ['Male', 'Female', 'Unknown', 'Other'];

        $species = ucfirst(strtolower($species));
        $gender = ucfirst(strtolower($gender));

        if ($name === '' || !in_array($species, $allowedSpecies, true)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pet name and species are required.']);
            exit;
        }

        if ($age < 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Age must be a positive number.']);
            exit;
        }

        if (!in_array($gender, $allowedGenders, true)) {
            $gender = 'Unknown';
        }

        if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['pet_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Upload failed.']);
                exit;
            }

            if (!in_array($ext, $allowedExtensions, true)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Supported image types: jpg, jpeg, png, webp.']);
                exit;
            }

            $imageName = 'pet_' . $ownerId . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $uploadError = $this->savePetImageUpload($file['tmp_name'], $imageName);
            if ($uploadError !== null) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $uploadError]);
                exit;
            }
        }

        try {
            $stmt = $db->prepare(
                "INSERT INTO pets (owner_id, name, species, breed, age, gender, weight, color, vaccination_status, allergies, medical_notes, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $ownerId,
                $name,
                $species,
                $breed,
                $age,
                $gender,
                $weight,
                $color,
                $vaccinationStatus,
                $allergies,
                $medicalNotes,
                $imageName
            ]);

            $petId = $db->lastInsertId();
            $this->createUserNotification(
                $db,
                $userId,
                'Pet Added',
                sprintf('%s was added to your pets successfully.', $name),
                'pet_added'
            );
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Pet added successfully 🐾',
                'pet' => [
                    'id' => $petId,
                    'owner_id' => $ownerId,
                    'name' => $name,
                    'species' => $species,
                    'breed' => $breed,
                    'age' => $age,
                    'gender' => $gender,
                    'weight' => $weight,
                    'color' => $color,
                    'vaccination_status' => $vaccinationStatus,
                    'allergies' => $allergies,
                    'medical_notes' => $medicalNotes,
                    'image' => $imageName,
                    'created_at' => date('Y-m-d H:i:s'),
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Could not save pet.']);
        }
        exit;
    }

    public function editPet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized request.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'];
        $petId = (int) ($_POST['id'] ?? 0);

        if ($petId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid pet identifier.']);
            exit;
        }

        $owner = $this->fetchOne($db, "SELECT o.id FROM pet_owners o JOIN pets p ON p.owner_id = o.id WHERE o.user_id = ? AND p.id = ?", [$userId, $petId]);
        if (!$owner) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pet not found or not owned by current user.']);
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $species = ucfirst(strtolower(trim($_POST['species'] ?? '')));
        $breed = trim($_POST['breed'] ?? '');
        $age = max(0, (int) ($_POST['age'] ?? 0));
        $gender = ucfirst(strtolower(trim($_POST['gender'] ?? 'Unknown')));
        $weight = trim($_POST['weight'] ?? '0');
        $weight = is_numeric($weight) ? number_format((float) $weight, 2, '.', '') : '0.00';
        $color = trim($_POST['color'] ?? '');
        $vaccinationStatus = trim($_POST['vaccination_status'] ?? 'Unknown');
        $allergies = trim($_POST['allergies'] ?? '');
        $medicalNotes = trim($_POST['medical_notes'] ?? '');

        $allowedSpecies = ['Dog', 'Cat', 'Bird', 'Other'];
        $allowedGenders = ['Male', 'Female', 'Unknown', 'Other'];

        if ($name === '' || !in_array($species, $allowedSpecies, true)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pet name and species are required.']);
            exit;
        }

        if ($age < 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Age must be a positive number.']);
            exit;
        }

        if (!in_array($gender, $allowedGenders, true)) {
            $gender = 'Unknown';
        }

        $pet = $this->fetchOne($db, "SELECT image FROM pets WHERE id = ? AND owner_id = ?", [$petId, $owner['id']]);
        if (!$pet) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pet record not found.']);
            exit;
        }

        $imageName = $pet['image'] ?: 'default-pet.png';
        if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['pet_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Upload failed.']);
                exit;
            }

            if (!in_array($ext, $allowedExtensions, true)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Supported image types: jpg, jpeg, png, webp.']);
                exit;
            }

            $newImageName = 'pet_' . $petId . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $uploadError = $this->savePetImageUpload($file['tmp_name'], $newImageName);
            if ($uploadError !== null) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $uploadError]);
                exit;
            }
            $this->deletePetImageFiles($imageName);
            if ($newImageName !== '') {
                $imageName = $newImageName;
            }
        }

        try {
            $stmt = $db->prepare(
                "UPDATE pets SET name = ?, species = ?, breed = ?, age = ?, gender = ?, weight = ?, color = ?, vaccination_status = ?, allergies = ?, medical_notes = ?, image = ? WHERE id = ? AND owner_id = ?"
            );
            $stmt->execute([
                $name,
                $species,
                $breed,
                $age,
                $gender,
                $weight,
                $color,
                $vaccinationStatus,
                $allergies,
                $medicalNotes,
                $imageName,
                $petId,
                $owner['id']
            ]);
            $this->createUserNotification(
                $db,
                $userId,
                'Pet Updated',
                sprintf('%s profile details were updated.', $name),
                'pet_updated'
            );

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Pet updated successfully.',
                'pet' => [
                    'id' => $petId,
                    'owner_id' => $owner['id'],
                    'name' => $name,
                    'species' => $species,
                    'breed' => $breed,
                    'age' => $age,
                    'gender' => $gender,
                    'weight' => $weight,
                    'color' => $color,
                    'vaccination_status' => $vaccinationStatus,
                    'allergies' => $allergies,
                    'medical_notes' => $medicalNotes,
                    'image' => $imageName,
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Could not update pet.']);
        }
        exit;
    }

    public function deletePet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized request.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'];
        $petId = (int) ($_POST['id'] ?? 0);

        if ($petId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid pet identifier.']);
            exit;
        }

        $owner = $this->fetchOne($db, "SELECT o.id FROM pet_owners o JOIN pets p ON p.owner_id = o.id WHERE o.user_id = ? AND p.id = ?", [$userId, $petId]);
        if (!$owner) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pet not found or not owned by current user.']);
            exit;
        }

        $pet = $this->fetchOne($db, "SELECT image FROM pets WHERE id = ? AND owner_id = ?", [$petId, $owner['id']]);
        if (!$pet) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pet record not found.']);
            exit;
        }

        try {
            $stmt = $db->prepare("DELETE FROM pets WHERE id = ? AND owner_id = ?");
            $stmt->execute([$petId, $owner['id']]);

            $imageName = $pet['image'] ?: 'default-pet.png';
            $this->deletePetImageFiles($imageName);
            $this->createUserNotification(
                $db,
                $userId,
                'Pet Deleted',
                'A pet profile was removed from your account.',
                'pet_deleted'
            );

            header('Content-Type: application/json');
            echo json_encode([ 'success' => true, 'message' => 'Pet deleted successfully.' ]);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Could not remove pet.']);
        }
        exit;
    }



    private function createUserNotification($db, $userId, $title, $message, $type) {
        if (!$userId) {
            return;
        }

        try {
            $stmt = $db->prepare(
                "INSERT INTO notifications (user_id, title, message, type, is_read)
                 VALUES (?, ?, ?, ?, 0)"
            );
            $stmt->execute([$userId, $title, $message, $type]);
        } catch (PDOException $e) {
            return;
        }
    }

    private function petUploadDirectories() {
        $directories = [];
        $primary = dirname(__DIR__, 2) . '/public/uploads/pets/';
        $directories[] = $primary;

        $legacy = dirname(dirname(__DIR__, 2)) . '/public/uploads/pets/';
        if ($legacy !== $primary) {
            $directories[] = $legacy;
        }

        return $directories;
    }

    private function savePetImageUpload($tmpPath, $imageName) {
        $directories = $this->petUploadDirectories();
        $copied = false;

        foreach ($directories as $index => $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $destination = $directory . $imageName;
            if ($index === 0) {
                if (!move_uploaded_file($tmpPath, $destination)) {
                    return 'Could not save the uploaded pet image.';
                }
                $copied = true;
                continue;
            }

            if ($copied && is_file($directories[0] . $imageName)) {
                @copy($directories[0] . $imageName, $destination);
            }
        }

        return null;
    }

    private function deletePetImageFiles($imageName) {
        $imageName = trim((string) $imageName);
        if ($imageName === '' || $imageName === 'default-pet.png') {
            return;
        }

        foreach ($this->petUploadDirectories() as $directory) {
            $path = $directory . basename($imageName);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }



    private function recommendedMarketplaceItems($db, $limit = 5) {
        if (!$this->tableExists($db, 'marketplace_items')) {
            return [];
        }

        $sql = "
            SELECT id, name, short_description, price, rating, image, category, stock
            FROM marketplace_items
            WHERE is_recommended = 1
            ORDER BY rating DESC
            LIMIT " . (int) $limit;

        $rows = $this->fetchAll($db, $sql);
        $items = [];

        foreach ($rows as $row) {
            $items[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => $row['name'] ?? 'Pet Product',
                'meta' => $row['short_description'] ?? '',
                'price' => 'EGP ' . number_format((float) ($row['price'] ?? 0), 0),
                'image' => trim((string) ($row['image'] ?? '')),
                'rating' => number_format((float) ($row['rating'] ?? 0), 1),
                'category' => $row['category'] ?? '',
                'stock' => (int) ($row['stock'] ?? 0),
            ];
        }

        return $items;
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

    private function countForPets($db, $table, $statusColumn, $status, $petIds) {
        if (empty($petIds) || !$this->tableExists($db, $table)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($petIds), '?'));
        $params = array_merge($petIds, [$status]);
        $stmt = $db->prepare("SELECT COUNT(*) FROM `$table` WHERE pet_id IN ($placeholders) AND LOWER(`$statusColumn`) = ?");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function countHealthRecords($db, $petIds) {
        if (empty($petIds)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($petIds), '?'));
        $healthCount = 0;
        $medicalCount = 0;

        if ($this->tableExists($db, 'health_records')) {
            $healthStmt = $db->prepare("SELECT COUNT(*) FROM health_records WHERE pet_id IN ($placeholders)");
            $healthStmt->execute($petIds);
            $healthCount = (int) $healthStmt->fetchColumn();
        }

        if ($this->tableExists($db, 'medical_records')) {
            $medicalStmt = $db->prepare("SELECT COUNT(*) FROM medical_records WHERE pet_id IN ($placeholders)");
            $medicalStmt->execute($petIds);
            $medicalCount = (int) $medicalStmt->fetchColumn();
        }

        return $healthCount + $medicalCount;
    }

    private function wellnessScore($db, $petIds) {
        if (empty($petIds) || !$this->tableExists($db, 'wellness')) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($petIds), '?'));
        $stmt = $db->prepare("SELECT ROUND(AVG(score)) FROM wellness WHERE pet_id IN ($placeholders)");
        $stmt->execute($petIds);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function loyaltyPoints($db, $userId) {
        if (!$this->tableExists($db, 'loyalty_points')) {
            return 0;
        }

        $stmt = $db->prepare("SELECT COALESCE(SUM(points), 0) FROM loyalty_points WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
