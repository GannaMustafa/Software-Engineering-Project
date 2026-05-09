<?php

require_once __DIR__ . '/../services/DashboardService.php';

class DashboardController
{
    private $service;

    public function __construct()
    {
        $this->service = new DashboardService();
    }

    public function index()
    {
        return $this->service->getDashboardData();
    }
    
}