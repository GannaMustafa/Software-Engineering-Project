<?php

require_once '../app/services/AuthService.php';

class AuthController extends Controller
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            $this->refreshSessionUser($_SESSION['user_id']);
            $this->redirectBasedOnRole($_SESSION['role']);
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['pass'] ?? '';

            $user = $this->authService->authenticate($email, $pass);

            if ($user) {
                session_regenerate_id(true);
                $_SESSION['user_id']     = $user['id'];
                $_SESSION['username']    = $user['username'];
                $_SESSION['role']        = $user['role'];
                $_SESSION['profile_pic'] = $user['image'] ?? 'default.png';
                $_SESSION['flash_success'] = "Welcome back, " . explode(' ', trim($user['username']))[0] . "!";

                $this->insertNotification(
                    $user['id'],
                    "Welcome back, " . $user['username'],
                    'We are happy to see you again.',
                    'login'
                );

                $this->authService->recordLogin($user['id']);
                $this->redirectBasedOnRole($user['role']);
            } else {
                $errors[] = $this->authService->getLastError();
            }
        }

        $this->view('auth/login', ['errors' => $errors]);
    }

    public function register()
    {
        if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBasedOnRole($_SESSION['role']);
        }

        $errors = [];
        $old = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => validate_input($_POST['username'] ?? ''),
                'email'    => validate_input($_POST['email'] ?? ''),
                'phone'    => $this->normalizePhone($_POST['phone_number'] ?? ''),
                'password' => $_POST['pass'] ?? '',
                'address' => $_POST['address'] ?? '',
                'role'     => 'pet_owner',
                'status'   => 'active',
                'image'    => 'default.png'
            ];
            $old = [
                'username' => $data['username'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],

            ];

            if (($data['password'] ?? '') !== ($_POST['confirm_pass'] ?? '')) {
                $errors[] = "Password confirmation does not match.";
            }

            if ($data['username'] === '') {
                $errors[] = "Username is required.";
            }

            if (!validate_email($data['email'])) {
                $errors[] = "Invalid email format.";
            }

            if (!validate_phone($data['phone'])) {
                $errors[] = "Invalid Egyptian phone number. Use a number like 01012345678.";
            }

            if (!validate_password($data['password'])) {
                $errors[] = "Password must be 8-20 characters and contain letters and numbers.";
            }

            if (empty($errors)) {
                $db = Database::getInstance()->getConnection();
                if (check_unique($db, 'email', 'users', $data['email'])) {
                    $errors[] = "Email already exists.";
                }
            }

            if (!isset($_POST['accept_rules'])) {
                $errors[] = "You must accept Paw Hubs rules before creating an account.";
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

            $result = empty($errors) ? $this->authService->registerUser($data) : ['error' => null];

            if (array_key_exists('error', $result)) {
                if ($result['error']) {
                    $errors[] = $result['error'];
                }

                if ($uploadedImage && file_exists($uploadedImage)) {
                    unlink($uploadedImage);
                }
            } elseif (!empty($result['id'])) {
                $createdUser = (new User())->getById($result['id']);
                if (!$createdUser) {
                    $errors[] = "Account was not saved. Please try again.";
                    $this->view('auth/register', ['errors' => $errors, 'old' => $old]);
                    return;
                }

                session_regenerate_id(true);
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['username'] = $createdUser['username'];
                $_SESSION['role'] = $createdUser['role'] ?? $data['role'];
                $_SESSION['profile_pic'] = $createdUser['image'] ?? $data['image'];
                $_SESSION['flash_success'] = "Welcome to Paw Hubs, " . explode(' ', trim($createdUser['username']))[0] . "! Your account is ready.";

                $this->insertNotification(
                    $result['id'],
                    'Welcome to Paw Hubs 🎉',
                    'Your account has been created successfully.',
                    'welcome'
                );

                $this->redirectBasedOnRole($_SESSION['role']);
            } else {
                $errors[] = "Account could not be created. Please check your details and try again.";
            }
        }
        $this->view('auth/register', ['errors' => $errors, 'old' => $old]);
    }

    public function logout()
    {
        $_SESSION = [];
        session_unset();
        session_destroy();
        header("Location: index.php?url=auth/login");
        exit;
    }

    private function redirectBasedOnRole($role)
    {
        $routes = [
            'admin' => 'admin/index',
            'vet' => 'clinical/index',
            'service_provider' => 'logistics/index',
            'pet_owner' => 'home/index'
        ];

        $target = $routes[$role] ?? 'home/index';
        header("Location: index.php?url=" . $target);
        exit;
    }

    private function refreshSessionUser($userId)
    {
        $userModel = new User();
        $user = $userModel->getById($userId);

        if ($user) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_pic'] = $user['image'] ?? 'default.png';
        }
    }

    private function insertNotification($userId, $title, $message, $type)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$userId, $title, $message, $type]);
        } catch (PDOException $e) {
            // keep auth flow working even if notifications fail
        }
    }

    private function uploadProfileImage($username)
    {
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
        $safeName = trim($safeName, '_-');
        if ($safeName === '') {
            $safeName = 'user_profile';
        }

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

    private function normalizePhone($phone)
    {
        $phone = preg_replace('/[^\d+]/', '', trim($phone));

        if (str_starts_with($phone, '+20')) {
            return '0' . substr($phone, 3);
        }

        if (str_starts_with($phone, '20') && strlen($phone) === 12) {
            return '0' . substr($phone, 2);
        }

        return $phone;
    }
}
