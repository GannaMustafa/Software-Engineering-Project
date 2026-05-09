-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2026 at 12:16 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pawhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_lists`
--

CREATE TABLE `activity_lists` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `occuerrd_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_type` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `pet_id`, `appointment_date`, `appointment_type`, `status`, `created_at`) VALUES
(1, 1, 1, '2026-05-10', 'Vet Checkup', 'upcoming', '2026-05-05 17:17:22');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certifications`
--

CREATE TABLE `certifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL,
  `issue_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disputes`
--

CREATE TABLE `disputes` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_points`
--

CREATE TABLE `loyalty_points` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `vet_id` int(11) NOT NULL,
  `diagnosis` varchar(255) NOT NULL,
  `treatment` text NOT NULL,
  `lab_result` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `total_price` double NOT NULL,
  `availability_status` varchar(20) NOT NULL,
  `is_recurring` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `species` varchar(50) NOT NULL,
  `breed` varchar(100) DEFAULT '',
  `age` int(11) NOT NULL,
  `gender` varchar(20) DEFAULT 'Unknown',
  `weight` decimal(5,2) NOT NULL DEFAULT 0.00,
  `color` varchar(50) DEFAULT '',
  `medical_notes` text,
  `vaccination_status` varchar(50) DEFAULT 'Unknown',
  `image` varchar(255) DEFAULT 'default-pet.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_for_adoption` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `owner_id`, `name`, `species`, `age`, `is_for_adoption`) VALUES
(1, 1, 'Buddy', 'Dog', 3, 1),
(4, 2, 'Max', 'Dog', 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pet_owners`
--

CREATE TABLE `pet_owners` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet_owners`
--

INSERT INTO `pet_owners` (`id`, `user_id`, `address`) VALUES
(1, 1, ''),
(2, 6, ''),
(4, 9, ''),
(5, 10, ''),
(6, 11, ''),
(7, 12, ''),
(8, 13, ''),
(9, 14, ''),
(10, 15, ''),
(11, 16, '');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `discount_percentage` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_providers`
--

CREATE TABLE `service_providers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `rating` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `image` varchar(255) DEFAULT 'default.png',
  `role` varchar(50) DEFAULT 'pet_owner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(100) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `status`, `image`, `role`, `created_at`) VALUES
(1, 'Sherif', 'test@test.com', '12345678', '01000000000', '', 'default.png', 'pet_owner', '2026-05-05 21:07:48'),
(6, 'Sherif Mohamed', 'sherifmo2719@gmail.com', '$2y$10$sULOiYXrWVVPtE.xZOakBO7WRfZlvtsoDGuYJUJyJRI', '01033062719', '', 'user_69fa27f54d8610.25277177.jpg', 'pet_owner', '2026-05-05 21:07:48'),
(9, 'Sherif Mohamed', 'sherifmo5555@gmail.com', '$2y$10$sThcZoAl6Lefc2ISwb0DLell684HcDHKyQfE4TPfhYe', '01033062719', '', 'user_69fa33bbe13bd3.90437339.jpg', 'pet_owner', '2026-05-05 21:07:48'),
(10, 'Sherif Mohamed', 'sherif@gmail.com', '$2y$10$6Wyrl3PISOAbbBCBdAlwnePWPbf1.hPx7eMV.qP3FTd', '01033062719', '', 'user_69fa37092cfdf8.99660501.jpg', 'pet_owner', '2026-05-05 21:07:48'),
(11, 'ahmed', 'ahmed@gmail.com', '$2y$10$xchwvkr4hLYkHYGaIQJYPux3BXt/ivuVs4S4ODq2/V4', '01033062719', '', 'user_69fa3d37830f14.19639691.jpg', 'pet_owner', '2026-05-05 21:07:48'),
(12, '', 'sara@gmail.com', '$2y$10$Qx1Wh671rnWlwN5rqPZm3.fxDc5MSo5uKVxIeN619qs', '01033062719', '', 'default.png', 'pet_owner', '2026-05-05 21:08:12'),
(13, 'Hassan', 'hassan@gmail.com', '$2y$10$gI.VF3m.I5oQBpEwoasutuiqis/.49aqtm4kxtuy.1u', '01033062719', '', 'default.png', 'pet_owner', '2026-05-05 21:15:10'),
(14, 'jana', 'jana@gmail.com', '$2y$10$VkAbLZzpY/ufRduYmV7OUuVIA1GaBsxourI3HzfgjKzyWsViDgazO', '01033062719', '', 'default.png', 'pet_owner', '2026-05-05 21:25:32'),
(15, 'roro', 'roro@gmail.com', '$2y$10$S/X.JebuuvC2g16xmlt.3OIjNOW4GykY0VdLrSB/deaSfg8jlwm.K', '01033062719', '', 'default.png', 'pet_owner', '2026-05-05 21:55:04'),
(16, 'roro', 'roro2@gmail.com', '$2y$10$ctrxo2BVpli.ToxNcoryqecJgfA2LPTMQbmOYAKuyDBxAAtpD3xV.', '01033062719', '', 'roro.png', 'pet_owner', '2026-05-05 22:11:37');

-- --------------------------------------------------------

--
-- Table structure for table `vaccines`
--

CREATE TABLE `vaccines` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `vaccine_name` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `balance` double NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `veterinarians`
--

CREATE TABLE `veterinarians` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `clinic_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wellness`
--

CREATE TABLE `wellness` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_lists`
--
ALTER TABLE `activity_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `pet_id_2` (`pet_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `certifications`
--
ALTER TABLE `certifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `disputes`
--
ALTER TABLE `disputes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `vet_id` (`vet_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `pet_owners`
--
ALTER TABLE `pet_owners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `service_providers`
--
ALTER TABLE `service_providers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vaccines`
--
ALTER TABLE `vaccines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `veterinarians`
--
ALTER TABLE `veterinarians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wellness`
--
ALTER TABLE `wellness`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_lists`
--
ALTER TABLE `activity_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certifications`
--
ALTER TABLE `certifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pet_owners`
--
ALTER TABLE `pet_owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_providers`
--
ALTER TABLE `service_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `vaccines`
--
ALTER TABLE `vaccines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `veterinarians`
--
ALTER TABLE `veterinarians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wellness`
--
ALTER TABLE `wellness`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_lists`
--
ALTER TABLE `activity_lists`
  ADD CONSTRAINT `fk_activity_lists_pets` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `fk_admins_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_admins` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `certifications`
--
ALTER TABLE `certifications`
  ADD CONSTRAINT `fk_certifications_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `disputes`
--
ALTER TABLE `disputes`
  ADD CONSTRAINT `fk_disputes_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_disputes_pet_owners` FOREIGN KEY (`owner_id`) REFERENCES `pet_owners` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_disputes_service_providers` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `fk_medical_records_pets` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medical_records_veterinarians` FOREIGN KEY (`vet_id`) REFERENCES `veterinarians` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_pet_owners` FOREIGN KEY (`owner_id`) REFERENCES `pet_owners` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_vendors` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_vendors` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `fk_pets_pet_owners` FOREIGN KEY (`owner_id`) REFERENCES `pet_owners` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `pet_owners`
--
ALTER TABLE `pet_owners`
  ADD CONSTRAINT `fk_pet_owners_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_pet_owners` FOREIGN KEY (`owner_id`) REFERENCES `pet_owners` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_services` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_services_service_providers` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `service_providers`
--
ALTER TABLE `service_providers`
  ADD CONSTRAINT `fk_service_providers_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vaccines`
--
ALTER TABLE `vaccines`
  ADD CONSTRAINT `vaccines_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `veterinarians`
--
ALTER TABLE `veterinarians`
  ADD CONSTRAINT `fk_veterinarians_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wellness`
--
ALTER TABLE `wellness`
  ADD CONSTRAINT `wellness_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;
-- --------------------------------------------------------
--
-- Paw Hubs app compatibility patch
-- Keeps this imported schema aligned with the current PHP pages.
--

ALTER TABLE `users`
  MODIFY `username` varchar(100) NOT NULL,
  MODIFY `email` varchar(100) NOT NULL,
  MODIFY `password` varchar(255) NOT NULL,
  MODIFY `phone` varchar(20) DEFAULT NULL,
  MODIFY `status` varchar(20) NOT NULL DEFAULT 'active',
  MODIFY `image` varchar(255) DEFAULT 'default.png',
  MODIFY `role` varchar(50) DEFAULT 'pet_owner';

UPDATE `users`
SET `status` = 'active'
WHERE `status` = '' OR `status` IS NULL;

ALTER TABLE `pet_owners`
  MODIFY `address` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `services`
  ADD COLUMN IF NOT EXISTS `name` varchar(100) NOT NULL DEFAULT '' AFTER `provider_id`,
  ADD COLUMN IF NOT EXISTS `category` varchar(50) DEFAULT NULL AFTER `name`,
  ADD COLUMN IF NOT EXISTS `price` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `category`,
  ADD COLUMN IF NOT EXISTS `duration` varchar(50) DEFAULT NULL AFTER `price`,
  ADD COLUMN IF NOT EXISTS `performed_by` enum('Vet','Provider') DEFAULT 'Vet' AFTER `duration`,
  ADD COLUMN IF NOT EXISTS `description` text DEFAULT NULL AFTER `performed_by`,
  ADD COLUMN IF NOT EXISTS `status` varchar(20) NOT NULL DEFAULT 'active' AFTER `discount_percentage`,
  ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `discount_percentage`,
  MODIFY `provider_id` int(11) DEFAULT NULL,
  MODIFY `discount_percentage` double NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS `service_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'scheduled',
  `booked_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `provider_id` (`provider_id`),
  KEY `owner_id` (`owner_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `service_completion_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `report_title` varchar(160) NOT NULL,
  `report_details` text DEFAULT NULL,
  `report_status` varchar(30) NOT NULL DEFAULT 'submitted',
  `created_by` int(11) DEFAULT NULL,
  `admin_confirmed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `provider_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `provider_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `commission_rate` decimal(5,4) NOT NULL DEFAULT 0.1500,
  `commission_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,4) NOT NULL DEFAULT 0.1400,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `provider_earning` decimal(10,2) NOT NULL DEFAULT 0.00,
  `platform_total_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cash_received_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(30) NOT NULL DEFAULT 'cash',
  `payment_status` varchar(30) NOT NULL DEFAULT 'pending_owner_cash',
  `transfer_status` varchar(30) NOT NULL DEFAULT 'not_transferred',
  `paid_at` datetime DEFAULT NULL,
  `transferred_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `provider_id` (`provider_id`),
  KEY `booking_id` (`booking_id`),
  KEY `report_id` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `status` varchar(20) NOT NULL DEFAULT 'pending' AFTER `availability_status`;

ALTER TABLE `audit_logs`
  ADD COLUMN IF NOT EXISTS `user_id` int(11) DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `entity_type` varchar(80) DEFAULT NULL AFTER `admin_id`,
  ADD COLUMN IF NOT EXISTS `entity_id` int(11) DEFAULT NULL AFTER `entity_type`,
  ADD COLUMN IF NOT EXISTS `ip_address` varchar(45) DEFAULT NULL AFTER `details`;

ALTER TABLE `activity_lists`
  ADD COLUMN IF NOT EXISTS `occurred_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `description`;

CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_type` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `pet_id` (`pet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `health_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pet_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pet_id` (`pet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `vaccines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pet_id` int(11) DEFAULT NULL,
  `vaccine_name` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pet_id` (`pet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `wellness` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pet_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pet_id` (`pet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `loyalty_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  PRIMARY KEY (`id`),
  KEY `pet_id` (`pet_id`),
  KEY `vet_id` (`vet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `surgery_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `medical_procedure_id` int(11) DEFAULT NULL,
  `procedure_type` varchar(120) NOT NULL,
  `reason` text NOT NULL,
  `urgency` varchar(30) NOT NULL DEFAULT 'normal',
  `status` varchar(40) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `pet_id` (`pet_id`),
  KEY `medical_procedure_id` (`medical_procedure_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `surgery_requests`
  ADD COLUMN IF NOT EXISTS `medical_procedure_id` int(11) DEFAULT NULL AFTER `pet_id`,
  ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `status`,
  ADD COLUMN IF NOT EXISTS `updated_at` timestamp NULL DEFAULT NULL AFTER `created_at`;

CREATE TABLE IF NOT EXISTS `lab_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pet_id` int(11) NOT NULL,
  `vet_id` int(11) DEFAULT NULL,
  `test_name` varchar(120) NOT NULL,
  `test_type` varchar(120) DEFAULT NULL,
  `result_summary` varchar(255) DEFAULT NULL,
  `raw_values` text DEFAULT NULL,
  `reference_ranges` text DEFAULT NULL,
  `interpretation` text DEFAULT NULL,
  `technical_data` longtext DEFAULT NULL,
  `abnormal_flags` text DEFAULT NULL,
  `follow_up_actions` text DEFAULT NULL,
  `vet_notes` text DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'pending',
  `report_date` date DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pet_id` (`pet_id`),
  KEY `vet_id` (`vet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `lab_reports`
  ADD COLUMN IF NOT EXISTS `test_type` varchar(120) DEFAULT NULL AFTER `test_name`,
  ADD COLUMN IF NOT EXISTS `raw_values` text DEFAULT NULL AFTER `result_summary`,
  ADD COLUMN IF NOT EXISTS `reference_ranges` text DEFAULT NULL AFTER `raw_values`,
  ADD COLUMN IF NOT EXISTS `technical_data` longtext DEFAULT NULL AFTER `interpretation`,
  ADD COLUMN IF NOT EXISTS `abnormal_flags` text DEFAULT NULL AFTER `technical_data`,
  ADD COLUMN IF NOT EXISTS `follow_up_actions` text DEFAULT NULL AFTER `abnormal_flags`,
  ADD COLUMN IF NOT EXISTS `vet_notes` text DEFAULT NULL AFTER `follow_up_actions`,
  ADD COLUMN IF NOT EXISTS `reviewed_at` datetime DEFAULT NULL AFTER `vet_notes`;

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
  PRIMARY KEY (`id`),
  KEY `pet_id` (`pet_id`),
  KEY `from_vet_id` (`from_vet_id`),
  KEY `to_vet_id` (`to_vet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `services` (`id`, `name`, `category`, `description`, `discount_percentage`) VALUES
(1, 'Veterinary Clinic', 'Healthcare', 'Clinic visits, diagnoses, and routine care.', 0),
(2, 'Pet Grooming', 'Grooming', 'Bathing, hair trimming, and nail care.', 0),
(3, 'Dog Walking', 'Pet Care', 'Trusted walks and pet sitting support.', 0),
(4, 'Pet Marketplace', 'Shopping', 'Pet food, accessories, and supplies.', 0),
(5, 'Pet Training', 'Training', 'Behavior and obedience training.', 0)
ON DUPLICATE KEY UPDATE
  `name` = IF(`name` = '' OR `name` IS NULL, VALUES(`name`), `name`),
  `category` = IF(`category` = '' OR `category` IS NULL, VALUES(`category`), `category`),
  `description` = IF(`description` IS NULL, VALUES(`description`), `description`);

INSERT INTO `vendors` (`id`, `name`, `balance`, `is_active`) VALUES
(1, 'Paw Hubs Store', 0, 1)
ON DUPLICATE KEY UPDATE
  `name` = IF(`name` = '' OR `name` IS NULL, VALUES(`name`), `name`),
  `is_active` = 1;

ALTER TABLE `vendors`
  ADD COLUMN IF NOT EXISTS `commission_rate` decimal(5,4) NOT NULL DEFAULT 0.1000 AFTER `balance`,
  ADD COLUMN IF NOT EXISTS `tax_rate` decimal(5,4) NOT NULL DEFAULT 0.1400 AFTER `commission_rate`;

CREATE TABLE IF NOT EXISTS `vendor_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `subtotal_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `commission_rate` decimal(5,4) NOT NULL DEFAULT 0.1000,
  `commission_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,4) NOT NULL DEFAULT 0.1400,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vendor_payout` decimal(10,2) NOT NULL DEFAULT 0.00,
  `platform_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(30) NOT NULL DEFAULT 'online',
  `payment_status` varchar(30) NOT NULL DEFAULT 'paid',
  `payout_status` varchar(30) NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `payout_released_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
