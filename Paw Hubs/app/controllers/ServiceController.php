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

class ServiceController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . app_url('auth/login'));
            exit;
        }

        require_once '../app/models/Service.php';
        $serviceModel = new Service();

        $services = $serviceModel->getAllActive();
        $pageTitle = 'Our Services';

        $this->view('service/index', compact('services', 'pageTitle'));
    }

    public function reserve($serviceId = null)
    {
        if (!isset($_SESSION['user_id']) || !$serviceId) {
            header('Location: ' . app_url('service/index'));
            exit;
        }

        require_once '../app/models/Service.php';
        require_once '../app/models/Pet.php';

        $serviceModel = new Service();
        $service = $serviceModel->getById($serviceId);

        if (!$service) {
            header('Location: ' . app_url('service/index'));
            exit;
        }

        $petModel = new Pet();
        $pets = $petModel->getByOwner($_SESSION['user_id']);

        $pageTitle = 'Book ' . $service['name'];

        $this->view('service/reserve', compact('service', 'pets', 'pageTitle'));
    }

        public function confirmReservation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: ' . app_url('service/index'));
            exit;
        }

        require_once '../app/models/Service.php';
        require_once '../app/models/Booking.php';

        $serviceId = (int)($_POST['service_id'] ?? 0);
        $petId     = (int)($_POST['pet_id'] ?? 0);
        $notes     = trim($_POST['notes'] ?? '');

        if (!$serviceId || !$petId) {
            $_SESSION['booking_error'] = 'Please select a pet.';
            header('Location: ' . app_url('service/reserve/' . $serviceId));
            exit;
        }

        $serviceModel = new Service();
        $service = $serviceModel->getById($serviceId);

        if (!$service) {
            $_SESSION['booking_error'] = 'Service not found.';
            header('Location: ' . app_url('service/index'));
            exit;
        }

        $bookingModel = new Booking();
        $bookingId = $bookingModel->create(
    $_SESSION['user_id'],
    $serviceId,
    $petId,
    $notes,
    $service['provider_id'] ?? 1 
);

        if ($bookingId) {
            $_SESSION['booking_confirmed'] = true;
            $_SESSION['booking_message'] = 'Your booking request has been submitted successfully!';
        } else {
            $_SESSION['booking_error'] = 'Failed to create booking. Please try again.';
        }

        header('Location: ' . app_url('service/index'));
        exit;
    }
}