<?php

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $host = "localhost";
        $dbName = "pawhub";
        $username = "root";
        $password = "";

        try {
            // 1. الاتصال بـ MySQL بدون تحديد قاعدة بيانات في الأول
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 2. إنشاء قاعدة البيانات لو مش موجودة
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

            // 3. الاتصال بقاعدة البيانات المطلوبة فعلياً
            $this->connection = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 4. إنشاء الجداول الأساسية لو مش موجودة (Self-Healing)
            $this->createTables();
            $this->migrateTables();
        } catch (PDOException $e) {
            die("Database Error: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function createTables()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(100) NOT NULL,
          `email` varchar(100) NOT NULL,
          `phone` varchar(20) DEFAULT NULL,
          `password` varchar(255) NOT NULL,
          `image` varchar(255) DEFAULT 'default.png',
          `role` enum('pet_owner','admin','service_provider','vet') DEFAULT 'pet_owner',
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `notifications` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `title` varchar(255) NOT NULL,
          `message` text DEFAULT NULL,
          `type` varchar(50) DEFAULT NULL,
          `is_read` tinyint(1) NOT NULL DEFAULT 0,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `pet_owners` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `address` varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `admins` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `veterinarians` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `license_number` varchar(50) NOT NULL DEFAULT '',
          `specialization` varchar(100) NOT NULL DEFAULT '',
          `clinic_address` varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `service_providers` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `business_name` varchar(150) NOT NULL DEFAULT '',
          `service_type` varchar(50) NOT NULL DEFAULT '',
          `rating` double NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS pets (
          id int(11) NOT NULL AUTO_INCREMENT,
          owner_id int(11) NOT NULL,
          name varchar(50) NOT NULL,
          species varchar(50) NOT NULL,
          breed varchar(100) DEFAULT '',
          age int(11) NOT NULL,
          gender varchar(20) DEFAULT 'Unknown',
          weight decimal(5,2) NOT NULL DEFAULT 0.00,
          color varchar(50) DEFAULT '',
          medical_notes text,
          allergies text DEFAULT NULL,
          vaccination_status varchar(50) DEFAULT 'Unknown',
          image varchar(255) DEFAULT 'default-pet.png',
          created_at timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `medical_procedures` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) NOT NULL,
          `vet_id` int(11) DEFAULT NULL,
          `procedure_name` varchar(120) NOT NULL,
          `procedure_type` varchar(80) DEFAULT NULL,
          `status` varchar(40) DEFAULT 'scheduled',
          `procedure_date` date DEFAULT NULL,
          `notes` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `lab_reports` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) NOT NULL,
          `vet_id` int(11) DEFAULT NULL,
          `test_name` varchar(120) NOT NULL,
          `result_summary` varchar(255) DEFAULT NULL,
          `interpretation` text DEFAULT NULL,
          `status` varchar(40) DEFAULT 'pending',
          `report_date` date DEFAULT NULL,
          `file_path` varchar(255) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `referrals` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) NOT NULL,
          `from_vet_id` int(11) DEFAULT NULL,
          `to_vet_id` int(11) DEFAULT NULL,
          `specialty` varchar(120) DEFAULT NULL,
          `reason` text DEFAULT NULL,
          `priority` varchar(40) DEFAULT 'normal',
          `status` varchar(40) DEFAULT 'pending',
          `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `notes` text DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `operating_rooms` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) NOT NULL,
          `location` varchar(120) DEFAULT NULL,
          `capacity` int(11) DEFAULT 1,
          `status` varchar(30) DEFAULT 'available',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `surgical_equipment` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(120) NOT NULL,
          `type` varchar(80) DEFAULT NULL,
          `status` varchar(30) DEFAULT 'available',
          `notes` text DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `procedure_bookings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) NOT NULL,
          `vet_id` int(11) DEFAULT NULL,
          `room_id` int(11) NOT NULL,
          `equipment_id` int(11) NOT NULL,
          `specialist_id` int(11) NOT NULL,
          `procedure_name` varchar(120) NOT NULL,
          `procedure_type` varchar(80) DEFAULT NULL,
          `start_time` datetime NOT NULL,
          `end_time` datetime NOT NULL,
          `status` varchar(40) DEFAULT 'scheduled',
          `notes` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `audit_logs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) DEFAULT NULL,
          `admin_id` int(11) DEFAULT NULL,
          `entity_type` varchar(80) DEFAULT NULL,
          `entity_id` int(11) DEFAULT NULL,
          `action` varchar(50) NOT NULL,
          `details` text DEFAULT NULL,
          `ip_address` varchar(45) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `access_controls` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `subject_role` varchar(50) NOT NULL,
          `resource_type` varchar(80) NOT NULL,
          `clinic_scope` varchar(120) DEFAULT NULL,
          `permission_level` varchar(50) NOT NULL DEFAULT 'view',
          `access_duration` varchar(80) DEFAULT NULL,
          `status` varchar(30) DEFAULT 'active',
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `vet_action_permissions` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `vet_id` int(11) NOT NULL,
          `action_key` varchar(80) NOT NULL,
          `access_mode` varchar(40) NOT NULL DEFAULT 'request_admin',
          `is_active` tinyint(1) NOT NULL DEFAULT 1,
          `notes` text DEFAULT NULL,
          `updated_by` int(11) DEFAULT NULL,
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `clinical_action_requests` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `action_key` varchar(80) NOT NULL,
          `action_title` varchar(150) NOT NULL,
          `pet_id` int(11) DEFAULT NULL,
          `owner_user_id` int(11) DEFAULT NULL,
          `requester_user_id` int(11) NOT NULL,
          `requester_role` varchar(30) NOT NULL DEFAULT 'vet',
          `target_vet_id` int(11) DEFAULT NULL,
          `owner_status` varchar(30) NOT NULL DEFAULT 'not_needed',
          `admin_status` varchar(30) NOT NULL DEFAULT 'not_needed',
          `request_status` varchar(30) NOT NULL DEFAULT 'pending',
          `payload` text DEFAULT NULL,
          `notes` text DEFAULT NULL,
          `decided_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `vendors` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(50) NOT NULL,
          `balance` double NOT NULL DEFAULT 0,
          `is_active` tinyint(1) NOT NULL DEFAULT 1,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS orders (
          id int(11) NOT NULL AUTO_INCREMENT,
          owner_id int(11) NOT NULL,
          vendor_id int(11) NOT NULL,
          total_price double NOT NULL DEFAULT 0,
          is_recurring tinyint(1) NOT NULL DEFAULT 0,
          delivery_date date DEFAULT NULL,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS order_items (
          id int(11) NOT NULL AUTO_INCREMENT,
          order_id int(11) NOT NULL,
          product_name varchar(150) NOT NULL,
          price double NOT NULL DEFAULT 0,
          quantity int(11) NOT NULL DEFAULT 1,
          availability_status varchar(20) NOT NULL DEFAULT 'pending',
          points int(11) NOT NULL DEFAULT 0,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `services` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `provider_id` int(11) DEFAULT NULL,
          `name` varchar(100) NOT NULL DEFAULT '',
          `category` varchar(50) DEFAULT NULL,
          `description` text DEFAULT NULL,
          `discount_percentage` double NOT NULL DEFAULT 0,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `reviews` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `owner_id` int(11) NOT NULL,
          `service_id` int(11) NOT NULL,
          `rating` int(11) NOT NULL DEFAULT 5,
          `comment` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `appointments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) DEFAULT NULL,
          `pet_id` int(11) DEFAULT NULL,
          `appointment_date` date DEFAULT NULL,
          `appointment_type` varchar(100) DEFAULT NULL,
          `status` varchar(50) DEFAULT 'upcoming',
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `health_records` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) DEFAULT NULL,
          `title` varchar(100) DEFAULT NULL,
          `description` text DEFAULT NULL,
          `record_date` date DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `vaccines` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) DEFAULT NULL,
          `vaccine_name` varchar(100) DEFAULT NULL,
          `due_date` date DEFAULT NULL,
          `status` varchar(50) DEFAULT 'pending',
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `vet_requests` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) NOT NULL,
          `owner_user_id` int(11) DEFAULT NULL,
          `request_type` varchar(50) NOT NULL,
          `title` varchar(150) NOT NULL,
          `description` text DEFAULT NULL,
          `status` varchar(30) NOT NULL DEFAULT 'pending',
          `priority` varchar(20) NOT NULL DEFAULT 'normal',
          `related_type` varchar(50) DEFAULT NULL,
          `related_id` int(11) DEFAULT NULL,
          `destination_country` varchar(100) DEFAULT NULL,
          `reviewed_by` int(11) DEFAULT NULL,
          `reviewed_at` datetime DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `pet_id` (`pet_id`),
          KEY `status` (`status`),
          KEY `request_type` (`request_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `country_pet_requirements` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `country_name` varchar(100) NOT NULL,
          `species` varchar(50) NOT NULL DEFAULT 'dog',
          `rabies_required` tinyint(1) NOT NULL DEFAULT 1,
          `microchip_required` tinyint(1) NOT NULL DEFAULT 1,
          `health_certificate_required` tinyint(1) NOT NULL DEFAULT 1,
          `tapeworm_treatment_required` tinyint(1) NOT NULL DEFAULT 0,
          `notes` text DEFAULT NULL,
          `source_url` varchar(255) DEFAULT NULL,
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `country_species` (`country_name`, `species`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `wellness` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) DEFAULT NULL,
          `score` int(11) DEFAULT NULL,
          `status` varchar(50) DEFAULT NULL,
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `loyalty_points` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) DEFAULT NULL,
          `points` int(11) DEFAULT 0,
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `marketplace_items` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `short_description` text DEFAULT NULL,
          `price` decimal(10,2) NOT NULL DEFAULT 0.00,
          `category` varchar(100) DEFAULT NULL,
          `image` varchar(255) DEFAULT NULL,
          `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
          `stock` int(11) NOT NULL DEFAULT 0,
          `is_recommended` tinyint(1) NOT NULL DEFAULT 1,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `daily_logs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) NOT NULL,
          `metric_type` enum('water_intake','insulin','mobility_score') NOT NULL,
          `metric_value` decimal(10,2) NOT NULL,
          `logged_date` date NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


        CREATE TABLE IF NOT EXISTS `pet_weight_logs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pet_id` int(11) NOT NULL,
          `weight` decimal(5,2) NOT NULL,
          `bcs_score` int(11) NOT NULL,
          `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

        ";

        $this->connection->exec($sql);
    }

    private function migrateTables()
    {
        $this->addColumnIfMissing('users', 'username', "`username` varchar(100) NOT NULL DEFAULT '' AFTER `id`");
        $this->addColumnIfMissing('users', 'phone', "`phone` varchar(20) DEFAULT NULL AFTER `email`");
        $this->addColumnIfMissing('users', 'password', "`password` varchar(255) NOT NULL DEFAULT '' AFTER `phone`");
        $this->addColumnIfMissing('users', 'image', "`image` varchar(255) DEFAULT 'default.png' AFTER `password`");
        $this->addColumnIfMissing('users', 'role', "`role` enum('pet_owner','admin','service_provider','vet') DEFAULT 'pet_owner' AFTER `image`");
        $this->addColumnIfMissing('users', 'status', "`status` varchar(20) NOT NULL DEFAULT 'active' AFTER `role`");
        $this->addColumnIfMissing('users', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `role`");
        $this->backfillUsernames();
        $this->connection->exec("ALTER TABLE `users` MODIFY `password` varchar(255) NOT NULL");
        $this->connection->exec("ALTER TABLE `users` MODIFY `username` varchar(100) NOT NULL");
        $this->connection->exec("ALTER TABLE `users` MODIFY `status` varchar(20) NOT NULL DEFAULT 'active'");
        $this->relaxLegacyNameColumn();

        $this->addColumnIfMissing('pet_owners', 'user_id', "`user_id` int(11) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('pet_owners', 'address', "`address` varchar(255) NOT NULL DEFAULT '' AFTER `user_id`");
        $this->connection->exec("ALTER TABLE `pet_owners` MODIFY `address` varchar(255) NOT NULL DEFAULT ''");

        $this->addColumnIfMissing('pets', 'image', "`image` varchar(255) DEFAULT 'default-pet.png' AFTER `age`");
        $this->addColumnIfMissing('pets', 'breed', "`breed` varchar(100) DEFAULT '' AFTER `species`");
        $this->addColumnIfMissing('pets', 'gender', "`gender` varchar(20) DEFAULT 'Unknown' AFTER `age`");
        $this->addColumnIfMissing('pets', 'weight', "`weight` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `gender`");
        $this->addColumnIfMissing('pets', 'color', "`color` varchar(50) DEFAULT '' AFTER `weight`");
        $this->addColumnIfMissing('pets', 'allergies', "allergies text DEFAULT NULL AFTER medical_notes");
        $this->addColumnIfMissing('pets', 'medical_notes', "`medical_notes` text AFTER `color`");
        $this->addColumnIfMissing('pets', 'vaccination_status', "`vaccination_status` varchar(50) DEFAULT 'Unknown' AFTER `medical_notes`");
        $this->addColumnIfMissing('pets', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `image`");

        $this->addColumnIfMissing('admins', 'user_id', "`user_id` int(11) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('veterinarians', 'user_id', "`user_id` int(11) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('veterinarians', 'license_number', "`license_number` varchar(50) NOT NULL DEFAULT '' AFTER `user_id`");
        $this->addColumnIfMissing('veterinarians', 'specialization', "`specialization` varchar(100) NOT NULL DEFAULT '' AFTER `license_number`");
        $this->addColumnIfMissing('veterinarians', 'clinic_address', "`clinic_address` varchar(255) NOT NULL DEFAULT '' AFTER `specialization`");
        $this->addColumnIfMissing('service_providers', 'user_id', "`user_id` int(11) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('service_providers', 'business_name', "`business_name` varchar(150) NOT NULL DEFAULT '' AFTER `user_id`");
        $this->addColumnIfMissing('service_providers', 'service_type', "`service_type` varchar(50) NOT NULL DEFAULT '' AFTER `business_name`");
        $this->addColumnIfMissing('service_providers', 'rating', "`rating` double NOT NULL DEFAULT 0 AFTER `service_type`");

        $this->addColumnIfMissing('services', 'provider_id', "`provider_id` int(11) DEFAULT NULL AFTER `id`");
        $this->addColumnIfMissing('services', 'name', "`name` varchar(100) NOT NULL DEFAULT '' AFTER `provider_id`");
        $this->addColumnIfMissing('services', 'category', "`category` varchar(50) DEFAULT NULL AFTER `name`");
        $this->addColumnIfMissing('services', 'description', "`description` text DEFAULT NULL AFTER `category`");
        $this->addColumnIfMissing('services', 'discount_percentage', "`discount_percentage` double NOT NULL DEFAULT 0 AFTER `description`");
        $this->addColumnIfMissing('services', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `discount_percentage`");
        $this->connection->exec("ALTER TABLE `services` MODIFY `provider_id` int(11) DEFAULT NULL");
        $this->connection->exec("ALTER TABLE `services` MODIFY `discount_percentage` double NOT NULL DEFAULT 0");
        $this->backfillServices();

        $this->addColumnIfMissing('reviews', 'owner_id', "`owner_id` int(11) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('reviews', 'service_id', "`service_id` int(11) NOT NULL AFTER `owner_id`");
        $this->addColumnIfMissing('reviews', 'rating', "`rating` int(11) NOT NULL DEFAULT 5 AFTER `service_id`");
        $this->addColumnIfMissing('reviews', 'comment', "`comment` text DEFAULT NULL AFTER `rating`");
        $this->addColumnIfMissing('reviews', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `comment`");

        $this->addColumnIfMissing('vendors', 'name', "name varchar(50) NOT NULL DEFAULT '' AFTER id");
        $this->addColumnIfMissing('vendors', 'balance', "balance double NOT NULL DEFAULT 0 AFTER name");
        $this->addColumnIfMissing('vendors', 'is_active', "is_active tinyint(1) NOT NULL DEFAULT 1 AFTER balance");
        $this->addColumnIfMissing('orders', 'owner_id', "owner_id int(11) NOT NULL AFTER id");
        $this->addColumnIfMissing('orders', 'vendor_id', "vendor_id int(11) NOT NULL AFTER owner_id");
        $this->addColumnIfMissing('orders', 'total_price', "total_price double NOT NULL DEFAULT 0 AFTER vendor_id");
        $this->addColumnIfMissing('orders', 'is_recurring', "is_recurring tinyint(1) NOT NULL DEFAULT 0 AFTER status");
        $this->addColumnIfMissing('orders', 'delivery_date', "delivery_date date DEFAULT NULL AFTER is_recurring");

        $this->addColumnIfMissing('order_items', 'order_id', "order_id int(11) NOT NULL AFTER id");
        $this->addColumnIfMissing('order_items', 'product_name', "product_name varchar(150) NOT NULL AFTER order_id");
        $this->addColumnIfMissing('order_items', 'price', "price double NOT NULL DEFAULT 0 AFTER product_name");
        $this->addColumnIfMissing('order_items', 'quantity', "quantity int(11) NOT NULL DEFAULT 1 AFTER price");
        $this->addColumnIfMissing('order_items', 'availability_status', "availability_status varchar(20) NOT NULL DEFAULT 'pending' AFTER quantity");
        $this->addColumnIfMissing('order_items', 'points', "points int(11) NOT NULL DEFAULT 0 AFTER availability_status");

        $this->addColumnIfMissing('marketplace_items', 'name', "`name` varchar(255) NOT NULL DEFAULT '' AFTER `id`");
        $this->addColumnIfMissing('marketplace_items', 'short_description', "`short_description` text DEFAULT NULL AFTER `name`");
        $this->addColumnIfMissing('marketplace_items', 'price', "`price` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `short_description`");
        $this->addColumnIfMissing('marketplace_items', 'category', "`category` varchar(100) DEFAULT NULL AFTER `price`");
        $this->addColumnIfMissing('marketplace_items', 'image', "`image` varchar(255) DEFAULT NULL AFTER `category`");
        $this->addColumnIfMissing('marketplace_items', 'rating', "`rating` decimal(2,1) NOT NULL DEFAULT 0.0 AFTER `image`");
        $this->addColumnIfMissing('marketplace_items', 'stock', "`stock` int(11) NOT NULL DEFAULT 0 AFTER `rating`");
        $this->addColumnIfMissing('marketplace_items', 'is_recommended', "`is_recommended` tinyint(1) NOT NULL DEFAULT 1 AFTER `stock`");
        $this->addColumnIfMissing('marketplace_items', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `is_recommended`");
        $this->seedMarketplaceItems();

        if ($this->tableExists('activity_lists')) {
            $this->addColumnIfMissing('activity_lists', 'occurred_at', "`occurred_at` timestamp NOT NULL DEFAULT current_timestamp()");
        }

        $this->addColumnIfMissing('medical_procedures', 'pet_id', "`pet_id` int(11) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('medical_procedures', 'vet_id', "`vet_id` int(11) DEFAULT NULL AFTER `pet_id`");
        $this->addColumnIfMissing('medical_procedures', 'procedure_name', "`procedure_name` varchar(120) NOT NULL AFTER `vet_id`");
        $this->addColumnIfMissing('medical_procedures', 'procedure_type', "`procedure_type` varchar(80) DEFAULT NULL AFTER `procedure_name`");
        $this->addColumnIfMissing('medical_procedures', 'status', "`status` varchar(40) DEFAULT 'scheduled' AFTER `procedure_type`");
        $this->addColumnIfMissing('medical_procedures', 'procedure_date', "`procedure_date` date DEFAULT NULL AFTER `status`");
        $this->addColumnIfMissing('medical_procedures', 'notes', "`notes` text DEFAULT NULL AFTER `procedure_date`");
        $this->addColumnIfMissing('medical_procedures', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `notes`");

        $this->addColumnIfMissing('lab_reports', 'pet_id', "`pet_id` int(11) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('lab_reports', 'vet_id', "`vet_id` int(11) DEFAULT NULL AFTER `pet_id`");
        $this->addColumnIfMissing('lab_reports', 'test_name', "`test_name` varchar(120) NOT NULL AFTER `vet_id`");
        $this->addColumnIfMissing('lab_reports', 'result_summary', "`result_summary` varchar(255) DEFAULT NULL AFTER `test_name`");
        $this->addColumnIfMissing('lab_reports', 'interpretation', "`interpretation` text DEFAULT NULL AFTER `result_summary`");
        $this->addColumnIfMissing('lab_reports', 'status', "`status` varchar(40) DEFAULT 'pending' AFTER `interpretation`");
        $this->addColumnIfMissing('lab_reports', 'report_date', "`report_date` date DEFAULT NULL AFTER `status`");
        $this->addColumnIfMissing('lab_reports', 'file_path', "`file_path` varchar(255) DEFAULT NULL AFTER `report_date`");
        $this->addColumnIfMissing('lab_reports', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `file_path`");

        $this->addColumnIfMissing('referrals', 'pet_id', "`pet_id` int(11) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('referrals', 'from_vet_id', "`from_vet_id` int(11) DEFAULT NULL AFTER `pet_id`");
        $this->addColumnIfMissing('referrals', 'to_vet_id', "`to_vet_id` int(11) DEFAULT NULL AFTER `from_vet_id`");
        $this->addColumnIfMissing('referrals', 'specialty', "`specialty` varchar(120) DEFAULT NULL AFTER `to_vet_id`");
        $this->addColumnIfMissing('referrals', 'reason', "`reason` text DEFAULT NULL AFTER `specialty`");
        $this->addColumnIfMissing('referrals', 'priority', "`priority` varchar(40) DEFAULT 'normal' AFTER `reason`");
        $this->addColumnIfMissing('referrals', 'status', "`status` varchar(40) DEFAULT 'pending' AFTER `priority`");
        $this->addColumnIfMissing('referrals', 'requested_at', "`requested_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `status`");
        $this->addColumnIfMissing('referrals', 'notes', "`notes` text DEFAULT NULL AFTER `requested_at`");

        $this->addColumnIfMissing('audit_logs', 'user_id', "`user_id` int(11) DEFAULT NULL AFTER `id`");
        $this->addColumnIfMissing('audit_logs', 'admin_id', "`admin_id` int(11) DEFAULT NULL AFTER `user_id`");
        $this->addColumnIfMissing('audit_logs', 'entity_type', "`entity_type` varchar(80) DEFAULT NULL AFTER `admin_id`");
        $this->addColumnIfMissing('audit_logs', 'entity_id', "`entity_id` int(11) DEFAULT NULL AFTER `entity_type`");
        $this->addColumnIfMissing('audit_logs', 'action', "`action` varchar(50) NOT NULL AFTER `entity_id`");
        $this->addColumnIfMissing('audit_logs', 'details', "`details` text DEFAULT NULL AFTER `action`");
        $this->addColumnIfMissing('audit_logs', 'ip_address', "`ip_address` varchar(45) DEFAULT NULL AFTER `details`");
        $this->addColumnIfMissing('audit_logs', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `ip_address`");
        $this->connection->exec("ALTER TABLE `audit_logs` MODIFY `admin_id` int(11) DEFAULT NULL");
        $this->connection->exec("ALTER TABLE `audit_logs` MODIFY `details` text DEFAULT NULL");

        $this->addColumnIfMissing('access_controls', 'subject_role', "`subject_role` varchar(50) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('access_controls', 'resource_type', "`resource_type` varchar(80) NOT NULL AFTER `subject_role`");
        $this->addColumnIfMissing('access_controls', 'clinic_scope', "`clinic_scope` varchar(120) DEFAULT NULL AFTER `resource_type`");
        $this->addColumnIfMissing('access_controls', 'permission_level', "`permission_level` varchar(50) NOT NULL DEFAULT 'view' AFTER `clinic_scope`");
        $this->addColumnIfMissing('access_controls', 'access_duration', "`access_duration` varchar(80) DEFAULT NULL AFTER `permission_level`");
        $this->addColumnIfMissing('access_controls', 'status', "`status` varchar(30) DEFAULT 'active' AFTER `access_duration`");
        $this->addColumnIfMissing('access_controls', 'created_by', "`created_by` int(11) DEFAULT NULL AFTER `status`");
        $this->addColumnIfMissing('access_controls', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `created_by`");

        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `vet_action_permissions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `vet_id` int(11) NOT NULL,
              `action_key` varchar(80) NOT NULL,
              `access_mode` varchar(40) NOT NULL DEFAULT 'request_admin',
              `is_active` tinyint(1) NOT NULL DEFAULT 1,
              `notes` text DEFAULT NULL,
              `updated_by` int(11) DEFAULT NULL,
              `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->addColumnIfMissing('vet_action_permissions', 'vet_id', "`vet_id` int(11) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('vet_action_permissions', 'action_key', "`action_key` varchar(80) NOT NULL AFTER `vet_id`");
        $this->addColumnIfMissing('vet_action_permissions', 'access_mode', "`access_mode` varchar(40) NOT NULL DEFAULT 'request_admin' AFTER `action_key`");
        $this->addColumnIfMissing('vet_action_permissions', 'is_active', "`is_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `access_mode`");
        $this->addColumnIfMissing('vet_action_permissions', 'notes', "`notes` text DEFAULT NULL AFTER `is_active`");
        $this->addColumnIfMissing('vet_action_permissions', 'updated_by', "`updated_by` int(11) DEFAULT NULL AFTER `notes`");
        $this->addColumnIfMissing('vet_action_permissions', 'updated_at', "`updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `updated_by`");

        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `clinical_action_requests` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `action_key` varchar(80) NOT NULL,
              `action_title` varchar(150) NOT NULL,
              `pet_id` int(11) DEFAULT NULL,
              `owner_user_id` int(11) DEFAULT NULL,
              `requester_user_id` int(11) NOT NULL,
              `requester_role` varchar(30) NOT NULL DEFAULT 'vet',
              `target_vet_id` int(11) DEFAULT NULL,
              `owner_status` varchar(30) NOT NULL DEFAULT 'not_needed',
              `admin_status` varchar(30) NOT NULL DEFAULT 'not_needed',
              `request_status` varchar(30) NOT NULL DEFAULT 'pending',
              `payload` text DEFAULT NULL,
              `notes` text DEFAULT NULL,
              `decided_by` int(11) DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->addColumnIfMissing('clinical_action_requests', 'action_key', "`action_key` varchar(80) NOT NULL AFTER `id`");
        $this->addColumnIfMissing('clinical_action_requests', 'action_title', "`action_title` varchar(150) NOT NULL AFTER `action_key`");
        $this->addColumnIfMissing('clinical_action_requests', 'pet_id', "`pet_id` int(11) DEFAULT NULL AFTER `action_title`");
        $this->addColumnIfMissing('clinical_action_requests', 'owner_user_id', "`owner_user_id` int(11) DEFAULT NULL AFTER `pet_id`");
        $this->addColumnIfMissing('clinical_action_requests', 'requester_user_id', "`requester_user_id` int(11) NOT NULL AFTER `owner_user_id`");
        $this->addColumnIfMissing('clinical_action_requests', 'requester_role', "`requester_role` varchar(30) NOT NULL DEFAULT 'vet' AFTER `requester_user_id`");
        $this->addColumnIfMissing('clinical_action_requests', 'target_vet_id', "`target_vet_id` int(11) DEFAULT NULL AFTER `requester_role`");
        $this->addColumnIfMissing('clinical_action_requests', 'owner_status', "`owner_status` varchar(30) NOT NULL DEFAULT 'not_needed' AFTER `target_vet_id`");
        $this->addColumnIfMissing('clinical_action_requests', 'admin_status', "`admin_status` varchar(30) NOT NULL DEFAULT 'not_needed' AFTER `owner_status`");
        $this->addColumnIfMissing('clinical_action_requests', 'request_status', "`request_status` varchar(30) NOT NULL DEFAULT 'pending' AFTER `admin_status`");
        $this->addColumnIfMissing('clinical_action_requests', 'payload', "`payload` text DEFAULT NULL AFTER `request_status`");
        $this->addColumnIfMissing('clinical_action_requests', 'notes', "`notes` text DEFAULT NULL AFTER `payload`");
        $this->addColumnIfMissing('clinical_action_requests', 'decided_by', "`decided_by` int(11) DEFAULT NULL AFTER `notes`");
        $this->addColumnIfMissing('clinical_action_requests', 'created_at', "`created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `decided_by`");
        $this->addColumnIfMissing('clinical_action_requests', 'updated_at', "`updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `created_at`");

        $this->seedVetActionPermissions();
    }

    private function addColumnIfMissing($table, $column, $definition)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);

        if ((int) $stmt->fetchColumn() === 0) {
            $this->connection->exec("ALTER TABLE `$table` ADD COLUMN $definition");
        }
    }

    private function columnExists($table, $column)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function tableExists($table)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ");
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function backfillUsernames()
    {
        if ($this->columnExists('users', 'name')) {
            $this->connection->exec("
                UPDATE `users`
                SET `username` = COALESCE(NULLIF(`username`, ''), NULLIF(`name`, ''), CONCAT('user_', `id`))
                WHERE `username` = '' OR `username` IS NULL
            ");
            return;
        }

        $this->connection->exec("
            UPDATE `users`
            SET `username` = CONCAT('user_', `id`)
            WHERE `username` = '' OR `username` IS NULL
        ");
    }

    private function relaxLegacyNameColumn()
    {
        if ($this->columnExists('users', 'name')) {
            $this->connection->exec("ALTER TABLE `users` MODIFY `name` varchar(100) DEFAULT NULL");
        }
    }

    private function backfillServices()
    {
        if (!$this->tableExists('services') || !$this->columnExists('services', 'name')) {
            return;
        }

        $defaults = [
            1 => ['Veterinary Clinic', 'Healthcare'],
            2 => ['Pet Grooming', 'Grooming'],
            3 => ['Dog Walking', 'Pet Care'],
            4 => ['Pet Marketplace', 'Shopping'],
            5 => ['Pet Training', 'Training']
        ];

        foreach ($defaults as $id => [$name, $category]) {
            $stmt = $this->connection->prepare("
                INSERT INTO `services` (`id`, `name`, `category`)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    `name` = IF(`name` = '' OR `name` IS NULL, VALUES(`name`), `name`),
                    `category` = IF(`category` IS NULL OR `category` = '', VALUES(`category`), `category`)
            ");
            $stmt->execute([$id, $name, $category]);
        }
    }

    private function seedVetActionPermissions()
    {
        if (!$this->tableExists('veterinarians') || !$this->tableExists('vet_action_permissions')) {
            return;
        }

        $defaults = [
            'lab_reports' => 'approve_user',
            'referrals' => 'request_admin',
            'surgery_booking' => 'request_admin',
            'medical_records' => 'request_user'
        ];

        $vets = $this->connection->query("SELECT id FROM veterinarians")->fetchAll(PDO::FETCH_COLUMN);
        $check = $this->connection->prepare("SELECT COUNT(*) FROM vet_action_permissions WHERE vet_id = ? AND action_key = ?");
        $insert = $this->connection->prepare("INSERT INTO vet_action_permissions (vet_id, action_key, access_mode, is_active) VALUES (?, ?, ?, 1)");

        foreach ($vets as $vetId) {
            foreach ($defaults as $actionKey => $mode) {
                $check->execute([$vetId, $actionKey]);
                if ((int) $check->fetchColumn() === 0) {
                    $insert->execute([$vetId, $actionKey, $mode]);
                }
            }
        }
    }

    private function seedMarketplaceItems()
    {
        if (!$this->tableExists('marketplace_items')) {
            return;
        }

        $items = [
            [
                'name' => 'Premium Dog Food',
                'short_description' => 'High-protein blend for active dogs',
                'price' => 520.00,
                'category' => 'Food',
                'image' => 'premium-dog-food.png',
                'rating' => 4.8,
                'stock' => 20,
                'is_recommended' => 1,
            ],
            [
                'name' => 'Squeaky Plush Toy',
                'short_description' => 'Soft chew-friendly playtime favorite',
                'price' => 120.00,
                'category' => 'Toys',
                'image' => 'squeaky-plush-toy.png',
                'rating' => 4.6,
                'stock' => 30,
                'is_recommended' => 1,
            ],
            [
                'name' => 'Adjustable Pet Collar',
                'short_description' => 'Comfort fit with premium buckle',
                'price' => 95.00,
                'category' => 'Accessories',
                'image' => 'adjustable-pet-collar.png',
                'rating' => 4.7,
                'stock' => 25,
                'is_recommended' => 1,
            ],
            [
                'name' => 'Soft Cozy Pet Bed',
                'short_description' => 'Cloud-soft rest spot for naps',
                'price' => 450.00,
                'category' => 'Beds',
                'image' => 'soft-cozy-pet-bed.png',
                'rating' => 4.9,
                'stock' => 12,
                'is_recommended' => 1,
            ],
            [
                'name' => 'Durable Rope Toy',
                'short_description' => 'Strong braided rope for tug play',
                'price' => 110.00,
                'category' => 'Toys',
                'image' => 'durable-rope-toy.png',
                'rating' => 4.5,
                'stock' => 18,
                'is_recommended' => 1,
            ],
        ];

        $insert = $this->connection->prepare("
            INSERT INTO `marketplace_items`
                (`name`, `short_description`, `price`, `category`, `image`, `rating`, `stock`, `is_recommended`)
            VALUES
                (:name, :short_description, :price, :category, :image, :rating, :stock, :is_recommended)
        ");
        $update = $this->connection->prepare("
            UPDATE `marketplace_items`
            SET `short_description` = :short_description,
                `price` = :price,
                `category` = :category,
                `image` = :image,
                `rating` = :rating,
                `stock` = :stock,
                `is_recommended` = :is_recommended
            WHERE `id` = :id
        ");
        $check = $this->connection->prepare("SELECT id FROM `marketplace_items` WHERE `name` = ? LIMIT 1");

        foreach ($items as $item) {
            $check->execute([$item['name']]);
            $existingId = $check->fetchColumn();

            if ($existingId) {
                $update->execute([
                    'short_description' => $item['short_description'],
                    'price' => $item['price'],
                    'category' => $item['category'],
                    'image' => $item['image'],
                    'rating' => $item['rating'],
                    'stock' => $item['stock'],
                    'is_recommended' => $item['is_recommended'],
                    'id' => $existingId,
                ]);
                continue;
            }

            $insert->execute([
                'name' => $item['name'],
                'short_description' => $item['short_description'],
                'price' => $item['price'],
                'category' => $item['category'],
                'image' => $item['image'],
                'rating' => $item['rating'],
                'stock' => $item['stock'],
                'is_recommended' => $item['is_recommended'],
            ]);
        }
    }
}
