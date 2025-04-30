-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 04:17 AM
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
-- Database: `newrent`
--

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('paid','unpaid','overdue') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `user_id`, `room_id`, `amount`, `description`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 13, 3500.00, 'Monthly Rent - January 2023', '2023-01-05', 'paid', '2025-04-14 00:14:23', '2025-04-14 00:14:23'),
(2, 2, 13, 3500.00, 'Monthly Rent - February 2023', '2023-02-05', 'paid', '2025-04-14 00:14:23', '2025-04-14 00:14:23'),
(3, 2, 13, 3500.00, 'Monthly Rent - March 2023', '2023-03-05', 'unpaid', '2025-04-14 00:14:23', '2025-04-14 00:14:23'),
(4, 11, 25, 3500.00, 'Initial Rent Payment for Reservation #1', '2025-04-29', 'unpaid', '2025-04-30 01:08:38', '2025-04-30 01:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `current_rentals`
--

CREATE TABLE `current_rentals` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `room_name` varchar(191) NOT NULL,
  `room_type` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `monthly_rent` decimal(10,2) NOT NULL,
  `security_deposit` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','expired','terminated') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `current_rentals`
--

INSERT INTO `current_rentals` (`id`, `user_id`, `room_id`, `room_name`, `room_type`, `start_date`, `end_date`, `monthly_rent`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 13, 'Cozy Room 1', 'Economy', '2023-01-01', '2023-12-31', 3500.00, 'active', '2025-04-14 08:14:23', '2025-04-14 08:14:23');

--
-- Update security deposit for current rentals
--

UPDATE `current_rentals`
SET `security_deposit` = monthly_rent
WHERE `security_deposit` IS NULL OR `security_deposit` = 0;

-- --------------------------------------------------------

--
-- Table structure for table `featured_properties`
--

CREATE TABLE `featured_properties` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `featured_properties`
--

INSERT INTO `featured_properties` (`id`, `name`, `description`, `image_url`, `rating`) VALUES
(1, 'Modern Apartment', 'Spacious 2BHK with great amenities', 'assets/img/modernapt.jpg', 4.5),
(2, 'Luxury Villa', 'Premium 3BHK with garden and pool', 'assets/img/luxuryapt.jpg', 5.0),
(3, 'Studio Apartment', 'Cozy studio perfect for singles', 'assets/img/studioapt.jpg', 4.0),
(4, 'Family Home', 'Spacious 4BHK for large families', 'assets/img/homeapt.jpg', 3.9),
(5, 'Beach House', 'Beautiful house with ocean view', 'assets/img/beachapt.jpg', 4.6);

-- --------------------------------------------------------

--
-- Table structure for table `lease_renewals`
--

CREATE TABLE `lease_renewals` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `current_end_date` date NOT NULL,
  `requested_end_date` date NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_comments`
--

CREATE TABLE `maintenance_comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `request_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_type` enum('tenant','staff','admin') NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `issue_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high','emergency') NOT NULL DEFAULT 'medium',
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_requests`
--

INSERT INTO `maintenance_requests` (`id`, `user_id`, `room_id`, `issue_type`, `description`, `priority`, `photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 13, 'Plumbing', 'Leaking faucet in the bathroom', 'medium', NULL, 'pending', '2025-04-14 08:14:23', '2025-04-14 08:14:23'),
(2, 2, 13, 'Electrical', 'Light fixture not working in bedroom', 'medium', NULL, 'in_progress', '2025-04-14 08:14:23', '2025-04-14 08:14:23');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('completed','failed','pending') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `payments`
ADD COLUMN `bill_id` int(10) UNSIGNED NOT NULL AFTER `id`,
ADD KEY `bill_id` (`bill_id`);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 11, 25, '2025-04-29', '2025-04-29', 'approved', '2025-04-29 05:11:44', '2025-04-30 01:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `room_rental_registrations`
--

CREATE TABLE `room_rental_registrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(191) NOT NULL,
  `mobile` varchar(191) NOT NULL,
  `alternat_mobile` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `country` varchar(191) NOT NULL,
  `state` varchar(191) NOT NULL,
  `city` varchar(191) NOT NULL,
  `landmark` varchar(191) NOT NULL,
  `rent` varchar(191) NOT NULL,
  `sale` varchar(190) DEFAULT NULL,
  `deposit` varchar(191) NOT NULL,
  `plot_number` varchar(191) NOT NULL,
  `rooms` varchar(100) DEFAULT NULL,
  `address` varchar(191) NOT NULL,
  `accommodation` varchar(191) NOT NULL,
  `description` varchar(191) NOT NULL,
  `image` longtext DEFAULT NULL,
  `open_for_sharing` varchar(191) DEFAULT NULL,
  `other` varchar(191) DEFAULT NULL,
  `vacant` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `user_id` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_rental_registrations`
--

INSERT INTO `room_rental_registrations` (`id`, `fullname`, `mobile`, `alternat_mobile`, `email`, `country`, `state`, `city`, `landmark`, `rent`, `sale`, `deposit`, `plot_number`, `rooms`, `address`, `accommodation`, `description`, `image`, `open_for_sharing`, `other`, `vacant`, `created_at`, `updated_at`, `user_id`) VALUES
(13, 'Ashish Gaikwazini', '2345676568', '2345676568', 'admin@admin.com', 'india', 'Maharashtra', 'Jalgaon', 'near ramanand police station', '1100', '20000', '5000', '77 nh', '2bhk', 'kolhe nagar, jalgaon', '4', 'nice house', 'uploads/img_680f0422e100e.jpg', NULL, 'zx', 1, '2022-05-05 12:21:43', '2022-05-05 12:21:43', NULL),
(16, 'Allain Ralph Legaspi', '5555555555', '666666666', 'allain@gmail.com', 'Philippines', 'Cebu', 'Cebu City', 'Near Brngy. Hall', '3500', '4000000000', '1500', '77', '20', 'Guadalupe, Cebu City', '5', 'hahahhaha', 'uploads/img_680f05e07f11a.jpg', NULL, NULL, 1, '2025-04-21 04:20:47', '2025-04-21 04:20:47', NULL),
(17, 'Sir Rockie', '9999999999', '666666666', 'rocky@gmail.com', 'Philippines', 'Cebu', 'Cebu City', 'Near IT', '5000', '500000000', '3000', '11', '20', 'Cebu Cuty', '5', 'hhhhh', 'uploads/modernapt.jpg', NULL, NULL, 1, '2025-04-21 05:55:07', '2025-04-21 05:55:07', NULL),
(19, 'kim', '00000000000', '98888787', 'kim@rome.com', 'Philippines', 'Cebu', 'Cebu City', 'IT park', '5000', '500000000', '3000', '1', '20', 'Cebu CIty', 'Wi-Fi, CR, Balcony', 'ffagagag', '[\"uploads\\/680c54d1e9eca_luxuryapt.jpg\"]', NULL, NULL, 1, '2025-04-26 03:36:49', '2025-04-26 03:36:49', NULL),
(20, 'Hanz Magbal', '09227777777', '09339999999', 'hanz@gmail.com', 'Philippines', 'Cebu', 'Cebu City', 'Shell', '6000', '4000000000', '3000', '21', '10', 'Cebu City', 'Wi-Fi, CR, Balcony', 'adadadad', '[\"uploads\\/680f0a9032ea9_luxuryapt.jpg\",\"uploads\\/680f0a90339eb_homeapt.jpg\"]', NULL, NULL, 1, '2025-04-28 04:56:48', '2025-04-28 04:56:48', NULL),
(21, 'Ayham Kalsam ', '096546215644', '0944895654895', 'ayham@gmail.com', 'Philippines', 'Cebu', 'Cebu City', 'Alvida Tower', '3999', '100000000', '1500', '8', '5', 'Cebu City', 'Wi-Fi, CR, Balcony', 'adadaadd', '[\"uploads\\/680f0a9032ea9_luxuryapt.jpg\",\"uploads\\/680f0a90339eb_homeapt.jpg\"]', NULL, NULL, 1, '2025-04-28 05:05:00', '2025-04-28 05:05:00', NULL),
(22, 'Kenshee', '097845168455', '09845166566', 'knshee@gmail.com', 'Philippines', 'Cebu', 'Cebu City', 'IT PARK', '4999', '300000000', '2599', '9', '10', 'Cebu City', 'Wi-Fi, CR, Balcony', 'jajajja', '[\"uploads\\/680f0a9032ea9_luxuryapt.jpg\",\"uploads\\/680f0a90339eb_homeapt.jpg\"]', NULL, NULL, 1, '2025-04-28 05:13:33', '2025-04-28 05:13:33', NULL),
(23, 'Bench', '095421658842', '095665819235', 'bnch@gmail.com', 'Philippines', 'Cebu', 'Cebu City', 'IT PARK', '6999', '4000000000', '3000', '8', '10', 'Cebu City', 'Wi-Fi, CR, Balcony', 'tralaleo tralala', '[\"uploads\\/680f0a9032ea9_luxuryapt.jpg\",\"uploads\\/680f0a90339eb_homeapt.jpg\"]', NULL, NULL, 1, '2025-04-28 05:20:07', '2025-04-28 05:20:07', NULL),
(24, 'Davis', '094545545665', '0955442255', 'dvs@gmail.com', 'Philippines', 'Cebu', 'Cebu City', 'Bus station', '2999', '100000000', '1500', '1', '10', 'Apas', 'Wi-Fi, CR, Balcony', 'lllll', '[\"uploads\\/680f128ae3a0d_homeapt.jpg\",\"uploads\\/680f0a9032ea9_luxuryapt.jpg\"]', NULL, NULL, 1, '2025-04-28 05:30:50', '2025-04-28 05:30:50', NULL),
(25, 'Kurt', '094512362514', '092514362514', 'krt@gmail.com', 'Philippines', 'Cebu', 'Cebu City', 'Cebu', '3500', '4000000000', '1500', '2', '20', 'Cebu', 'Wi-Fi, CR, Balcony', 'er', '[\"uploads\\/680f14217b770_kag.png\",\"uploads\\/680f14217b9d4_kag1.jpg\"]', NULL, NULL, 1, '2025-04-28 05:37:37', '2025-04-28 05:37:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `saved_rooms`
--

CREATE TABLE `saved_rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_info`
--

CREATE TABLE `tenant_info` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `emergency_contact` varchar(191) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `occupation` varchar(191) DEFAULT NULL,
  `id_type` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(191) NOT NULL,
  `mobile` varchar(191) NOT NULL,
  `username` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `role` varchar(100) DEFAULT 'user',
  `status` int(1) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `mobile`, `username`, `email`, `address`, `image`, `password`, `created_at`, `updated_at`, `role`, `status`) VALUES
(11, 'Testing Tenant', '9876543211', 'tenant', 'tenant@rome.com', 'Cebu City', '/ROME/uploads/profile_pictures/user_11_1745637368.jpg', '0192023a7bbd73250516f069df18b500', '2025-04-14 08:14:23', '2025-04-14 08:14:23', 'tenant', 1),
(10, 'Administrator', '9876543210', 'admin', 'admin@rome.com', NULL, NULL, '0192023a7bbd73250516f069df18b500', '2025-04-14 07:36:27', '2025-04-14 07:36:27', 'admin', 1),
(13, 'kim', '0941526378', 'kim', 'kim@rome.com', NULL, NULL, '202cb962ac59075b964b07152d234b70', '2025-04-30 02:07:42', '2025-04-30 02:07:42', 'tenant', 1),
(14, 'Davis', '0952146378', 'Davis', 'davis@rome.com', NULL, NULL, '202cb962ac59075b964b07152d234b70', '2025-04-30 02:16:57', '2025-04-30 02:16:57', 'tenant', 1);

-- --------------------------------------------------------

--
-- Table structure for table `visitor_logs`
--

CREATE TABLE `visitor_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `visitor_name` varchar(191) NOT NULL,
  `visitor_phone` varchar(20) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `check_in` datetime NOT NULL,
  `check_out` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visitor_logs`
--

INSERT INTO `visitor_logs` (`id`, `user_id`, `room_id`, `visitor_name`, `visitor_phone`, `purpose`, `check_in`, `check_out`, `created_at`, `updated_at`) VALUES
(1, 2, 13, 'John Smith', '9876543210', 'Family Visit', '2023-02-15 10:00:00', '2023-02-15 18:00:00', '2025-04-14 08:14:23', '2025-04-14 08:14:23'),
(2, 2, 13, 'Mary Johnson', '8765432109', 'Friend Visit', '2023-02-20 14:00:00', '2023-02-20 20:00:00', '2025-04-14 08:14:23', '2025-04-14 08:14:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `current_rentals`
--
ALTER TABLE `current_rentals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `current_rentals_user_id_unique` (`user_id`);

--
-- Indexes for table `featured_properties`
--
ALTER TABLE `featured_properties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lease_renewals`
--
ALTER TABLE `lease_renewals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_comments`
--
ALTER TABLE `maintenance_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_rental_registrations`
--
ALTER TABLE `room_rental_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_rental_registrations_mobile_unique` (`mobile`),
  ADD UNIQUE KEY `room_rental_registrations_email_unique` (`email`);

--
-- Indexes for table `saved_rooms`
--
ALTER TABLE `saved_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `saved_rooms_user_room_unique` (`user_id`,`room_id`);

--
-- Indexes for table `tenant_info`
--
ALTER TABLE `tenant_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_info_user_id_unique` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_mobile_unique` (`mobile`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- Indexes for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `current_rentals`
--
ALTER TABLE `current_rentals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `featured_properties`
--
ALTER TABLE `featured_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lease_renewals`
--
ALTER TABLE `lease_renewals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_comments`
--
ALTER TABLE `maintenance_comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `room_rental_registrations`
--
ALTER TABLE `room_rental_registrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `saved_rooms`
--
ALTER TABLE `saved_rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenant_info`
--
ALTER TABLE `tenant_info`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY COLUMN `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY;

--
-- Update the existing bill with ID 0 to have a proper ID
--
UPDATE `bills` SET `id` = 4 WHERE `id` = 0;

ALTER TABLE `current_rentals`
ADD COLUMN `security_deposit` decimal(10,2) DEFAULT 0.00R_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
AFTER `monthly_rent`;/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
1 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
COMMIT;/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
