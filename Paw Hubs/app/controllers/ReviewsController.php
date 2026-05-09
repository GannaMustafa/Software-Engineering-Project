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

class ReviewsController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . app_url('auth/login'));
            exit;
        }

        require_once '../app/models/Review.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $serviceId = (int) ($_POST['service_id'] ?? 0);
            $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
            $comment = trim($_POST['comment'] ?? '');
            $ownerId = Review::getOwnerIdByUserId($_SESSION['user_id']);

            if (!$ownerId) {
                $_SESSION['review_error'] = 'Only pet owner accounts can submit reviews.';
            } elseif ($serviceId > 0 && $comment !== '') {
                Review::add($ownerId, $serviceId, $rating, $comment);
                $_SESSION['review_submitted'] = true;
            }

            header('Location: ' . app_url('reviews/index'));
            exit;
        }

        $reviews = Review::allWithDetails();
        $services = Review::allServices();
        $providers = Review::allProviders();
        $pageTitle = 'Reviews';

        $this->view('reviews/index', compact('reviews', 'services', 'providers', 'pageTitle'));
    }
}
