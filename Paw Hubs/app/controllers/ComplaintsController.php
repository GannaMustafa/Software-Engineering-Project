<?php

if (!function_exists('app_url')) {
    function app_url($route = 'home/index', $fragment = '')
    {
        $scriptName = urldecode(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
        $base = '';

        $knownFolders = [
            '/Paw Hubs/public',
            '/Pet Health page',
            '/Smart Marketplace Page'
        ];

        foreach ($knownFolders as $folder) {
            $pos = strpos($scriptName, $folder);
            if ($pos !== false) {
                $base = substr($scriptName, 0, $pos);
                break;
            }
        }

        $url = rtrim($base, '/') . '/Paw Hubs/public/index.php?url=' . ltrim($route, '/');
        return $fragment ? $url . '#' . ltrim($fragment, '#') : $url;
    }
}

class ComplaintsController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . app_url('auth/login'));
            exit;
        }

        require_once '../app/models/Complaint.php';
        $complaints = Complaint::findByUser($_SESSION['user_id']);
        $pageTitle = 'My Complaints — Paw Hubs';

        $this->view('complaints/index', compact('complaints', 'pageTitle'));
    }

    public function create()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . app_url('auth/login'));
            exit;
        }

        $data = [
            'order_id' => (int)($_GET['order_id'] ?? 0),
            'service_id' => (int)($_GET['service_id'] ?? 0),
            'provider_id' => (int)($_GET['provider_id'] ?? 0),
        ];
        $pageTitle = 'Submit Complaint — Paw Hubs';
        $error = '';

        $this->view('complaints/create', compact('data', 'pageTitle', 'error'));
    }

    public function store()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . app_url('auth/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . app_url('complaints/index'));
            exit;
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'order_id' => (int)($_POST['order_id'] ?? 0),
            'service_id' => (int)($_POST['service_id'] ?? 0),
            'provider_id' => (int)($_POST['provider_id'] ?? 0),
            'subject' => trim($_POST['subject'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        if (empty($data['subject']) || empty($data['description'])) {
            $error = 'Subject and description are required.';
            $pageTitle = 'Submit Complaint — Paw Hubs';
            $this->view('complaints/create', compact('error', 'data', 'pageTitle'));
            return;
        }

        require_once '../app/models/Complaint.php';
        Complaint::create($data);

        $_SESSION['complaint_submitted'] = true;
        header('Location: ' . app_url('complaints/index'));
        exit;
    }

    public function download()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . app_url('auth/login'));
            exit;
        }

        require_once '../app/models/Complaint.php';
        $complaints = Complaint::findByUser($_SESSION['user_id']);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="my-complaints-' . $_SESSION['user_id'] . '.json"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo json_encode($complaints, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
