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

class ServiceController extends Controller
{
    public function index()
    {
        require_once '../app/models/Service.php';

        $serviceModel = new Service();
        $services = $serviceModel->getSittersWithProviders();
        $pageTitle = 'Find a Pet Sitter';

        $this->view('service/index', compact('services', 'pageTitle'));
    }

    public function reserve($serviceId = null)
    {
        if (!$serviceId) {
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
        $pets = $petModel->getByOwner($_SESSION['user_id'] ?? 0);

        $pageTitle = 'Reserve ' . htmlspecialchars($service['name']);

        $this->view('service/reserve', compact('service', 'pets', 'pageTitle'));
    }

    public function confirmReservation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: ' . app_url('service/index'));
            exit;
        }

        require_once '../app/models/Service.php';
        require_once '../app/models/Pet.php';
        require_once '../app/models/Booking.php';

        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $petId = (int) ($_POST['pet_id'] ?? 0);
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $specialInstructions = trim($_POST['special_instructions'] ?? '');

        if (!$serviceId || !$petId || !$startDate || !$endDate) {
            $_SESSION['error_message'] = 'Missing required fields';
            header('Location: ' . app_url('service/index'));
            exit;
        }

        $serviceModel = new Service();
        $service = $serviceModel->getById($serviceId);

        if (!$service) {
            $_SESSION['error_message'] = 'Service not found';
            header('Location: ' . app_url('service/index'));
            exit;
        }

        // Get a service provider for this service
        $providers = $this->db->prepare("SELECT id FROM service_providers WHERE service_id = ? LIMIT 1");
        $providers->execute([$serviceId]);
        $provider = $providers->fetch(PDO::FETCH_ASSOC);
        $serviceProviderId = $provider['id'] ?? null;

        // Create booking
        $bookingModel = new Booking();
        $bookingModel->create($_SESSION['user_id'], $serviceId, $petId, $startDate, $endDate, $specialInstructions, $serviceProviderId);

        $_SESSION['booking_confirmed'] = true;
        $_SESSION['booking_message'] = 'Your booking has been submitted successfully!';
        
        header('Location: ' . app_url('service/index'));
        exit;
    }

    public function processReservation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: ' . app_url('service/index'));
            exit;
        }

        require_once '../app/models/Service.php';
        require_once '../app/models/Pet.php';
        require_once '../app/models/Booking.php';

        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $petId = (int) ($_POST['pet_id'] ?? 0);
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $specialInstructions = trim($_POST['special_instructions'] ?? '');

        if (!$serviceId || !$petId || !$startDate || !$endDate) {
            $_SESSION['error_message'] = 'Missing required fields';
            header('Location: ' . app_url('service/index'));
            exit;
        }

        $serviceModel = new Service();
        $service = $serviceModel->getById($serviceId);

        if (!$service) {
            $_SESSION['error_message'] = 'Service not found';
            header('Location: ' . app_url('service/index'));
            exit;
        }

        // Get a service provider for this service
        $db = Database::getInstance()->getConnection();
        $providerStmt = $db->prepare("SELECT provider_id FROM services WHERE id = ? LIMIT 1");
        $providerStmt->execute([$serviceId]);
        $provider = $providerStmt->fetch(PDO::FETCH_ASSOC);
        $serviceProviderId = $provider['provider_id'] ?? null;

        // Create booking
        $bookingModel = new Booking();
        $bookingId = $bookingModel->create($_SESSION['user_id'], $serviceId, $petId, $startDate, $endDate, $specialInstructions, $serviceProviderId);

        if ($bookingId) {
            $booking = $bookingModel->getById($bookingId);
            $petModel = new Pet();
            $pets = $petModel->getByOwner($_SESSION['user_id'] ?? 0);
            $availableSlots = $bookingModel->getAvailableSlotsForService($serviceId);
            $capacity = $bookingModel->getServiceCapacity($serviceId);
            $pageTitle = 'Reserve ' . htmlspecialchars($service['name']);
            $this->view('service/reserve', compact('service', 'pets', 'availableSlots', 'capacity', 'pageTitle', 'booking'));
        } else {
            $_SESSION['error_message'] = 'Failed to create booking. Please try again.';
            header('Location: ' . app_url('service/reserve/' . $serviceId));
            exit;
        }
    }
}
