<?php

require_once __DIR__ . '/../services/SystemService.php';

class SystemController
{
    private $service;

    public function __construct()
    {
        $this->service = new SystemService();
    }

    public function index()
    {
        return $this->service->getSystemData();
    }

    public function handleRequest()
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $this->service->handleAction($_POST);
        }
        return $message;
    }
}