<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../app/core/App.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../app/core/Validator.php';
require_once '../app/core/validation.php';

if (isset($_SESSION['user_id'])) {
    try {
        if (!function_exists('asset')) {
            function asset($path)
            {
                $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
                if ($base === '/' || $base === '.') {
                    $base = '';
                }
                return $base . '/' . ltrim($path, '/');
            }
        }
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['status'] == 'suspended') {
            session_unset();
            session_destroy();
            $_SESSION['suspend_msg'] = "Your account has been suspended by the administrator. Please contact support.";
            header("Location: index.php?url=auth/login&suspended=1");
            exit();
        }
    } catch (Exception $e) {
        error_log("Suspension check failed: " . $e->getMessage());
    }
}

$app = new App();
