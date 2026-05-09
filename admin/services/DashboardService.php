<?php

require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardService
{
    private $model;

    public function __construct()
    {
        $this->model = new DashboardModel();
    }

    public function getDashboardData()
    {
        return [
            'stats'           => $this->model->getDashboardStats(),
            'recent_disputes' => $this->model->getRecentDisputes(5)
        ];
    }
}