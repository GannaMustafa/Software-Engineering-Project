<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../app/core/App.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../app/core/Validator.php';
require_once '../app/core/validation.php';

$app = new App();