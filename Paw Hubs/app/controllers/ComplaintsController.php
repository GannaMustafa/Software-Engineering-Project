<?php

if (!function_exists('app_url')) {
    function app_url($route = 'home/index', $fragment = '')
    {
        $scriptName = urldecode(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
        $base = '';
        $knownFolders = ['/Paw Hubs/public', '/Pet Health page', '/Smart Marketplace Page'];
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
            'order_id'     => (int)($_GET['order_id'] ?? 0),
            'service_id'   => (int)($_GET['service_id'] ?? 0),
            'provider_id'  => (int)($_GET['provider_id'] ?? 0),
        ];

        $pageTitle = 'Submit Complaint — Paw Hubs';

        $this->view('complaints/create', compact('data', 'pageTitle'));
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

    require_once '../app/models/Complaint.php';

    // Get username
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $data = [
        'user_name'     => $user['username'] ?? 'Unknown User',
        'provider_name' => trim($_POST['provider_name'] ?? 'Service Provider'),
        'issue'         => trim($_POST['issue'] ?? ''),
        'user_msg'      => trim($_POST['description'] ?? ''),
        'amount'        => (float)($_POST['amount'] ?? 0),
    ];

    if (empty($data['issue']) || empty($data['user_msg'])) {
        $_SESSION['complaint_error'] = 'Issue and description are required.';
        header('Location: ' . app_url('complaints/create'));
        exit;
    }

    if (Complaint::create($data)) {
        $_SESSION['complaint_submitted'] = true;
    } else {
        $_SESSION['complaint_error'] = 'Failed to submit complaint.';
    }

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

        echo json_encode($complaints, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}   