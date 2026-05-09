<?php

require_once __DIR__ . '/../models/UserModel.php';

class UserService
{
    private $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function getAll()
    {
        return $this->model->getAllUsers();
    }

    public function filter($search, $status)
    {
        return $this->model->searchAndFilter($search, $status);
    }

    public function sort($users, $sort)
    {
        usort($users, function ($a, $b) use ($sort) {
            if ($sort === 'name') {
                return strcmp($a['username'] ?? '', $b['username'] ?? '');
            }
            return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
        });
        return $users;
    }

    public function getById($id)
    {
        $user = $this->model->getById($id);
        if ($user) {
            // Load bookings for this user
            require_once __DIR__ . '/../../Paw Hubs/app/models/Booking.php';
            $bookingModel = new Booking();
            $user['bookings'] = $bookingModel->getByUser($id);
        }
        return $user;
    }

    public function handleAction($action, $id)
    {
        if ($action === 'suspend') {
            $this->model->updateStatus($id, 'suspended');
            return "User #$id has been suspended.";
        }
        if ($action === 'unsuspend') {
            $this->model->updateStatus($id, 'active');
            return "User #$id has been unsuspended.";
        }
        if ($action === 'delete') {
            $this->model->deleteUser($id);
            return "User #$id has been deleted.";
        }
        return '';
    }

    // Helper for bookings
    public function getUserBookings($userId)
    {
        require_once __DIR__ . '/../../Paw Hubs/app/models/Booking.php';
        $bookingModel = new Booking();
        return $bookingModel->getByUser($userId);
    }
}