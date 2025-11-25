-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 02:34 PM
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
-- Database: `hosting`
--

-- --------------------------------------------------------

--
-- Table structure for table `hosting_orders`
--

CREATE TABLE `hosting_orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `billing_cycle` enum('monthly','yearly','2years','4years') NOT NULL DEFAULT 'monthly',
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `setup_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `processing_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `domain_name` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','processing','active','suspended','cancelled','expired') NOT NULL DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `renewal_date` date DEFAULT NULL,
  `auto_renewal` tinyint(1) NOT NULL DEFAULT 0,
  `payment_id` varchar(255) DEFAULT NULL COMMENT 'Payment gateway order ID',
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL COMMENT 'Internal admin notes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosting_packages`
--

CREATE TABLE `hosting_packages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `price_monthly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_yearly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_2years` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_4years` decimal(10,2) NOT NULL DEFAULT 0.00,
  `storage_gb` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Disk space in GB',
  `bandwidth_gb` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Bandwidth in GB',
  `allowed_websites` int(11) NOT NULL DEFAULT 1,
  `database_limit` int(11) NOT NULL DEFAULT 1,
  `ftp_accounts` int(11) NOT NULL DEFAULT 1,
  `email_accounts` int(11) NOT NULL DEFAULT 1,
  `ssh_access` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'SSH Access',
  `ssl_free` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Free SSL Certificate',
  `daily_backups` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Daily Backups',
  `dedicated_ip` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Dedicated IP',
  `php_version` varchar(50) DEFAULT '7.4' COMMENT 'PHP Version',
  `mysql_version` varchar(50) DEFAULT '5.7' COMMENT 'MySQL Version',
  `setup_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'One-time setup fee',
  `gst_percentage` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'GST percentage',
  `processing_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Processing fee',
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'inactive',
  `is_popular` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Mark as popular package',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosting_websites`
--

CREATE TABLE `hosting_websites` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `website_name` varchar(255) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `website_url` varchar(500) DEFAULT NULL,
  `ssh_username` varchar(100) DEFAULT NULL,
  `ssh_password` varchar(255) DEFAULT NULL,
  `ssh_host` varchar(255) DEFAULT NULL,
  `ssh_port` int(11) DEFAULT 22,
  `db_name` varchar(100) DEFAULT NULL,
  `db_username` varchar(100) DEFAULT NULL,
  `db_password` varchar(255) DEFAULT NULL,
  `db_host` varchar(255) DEFAULT NULL,
  `ftp_username` varchar(100) DEFAULT NULL,
  `ftp_password` varchar(255) DEFAULT NULL,
  `ftp_host` varchar(255) DEFAULT NULL,
  `ftp_port` int(11) DEFAULT 21,
  `cpanel_url` varchar(500) DEFAULT NULL,
  `cpanel_username` varchar(100) DEFAULT NULL,
  `cpanel_password` varchar(255) DEFAULT NULL,
  `status` enum('active','suspended','deleted') NOT NULL DEFAULT 'active',
  `payment_status` enum('paid','pending','overdue') NOT NULL DEFAULT 'pending',
  `server_ip` varchar(45) DEFAULT NULL,
  `nameservers` text DEFAULT NULL COMMENT 'Comma separated nameserver addresses',
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manual_payments`
--

CREATE TABLE `manual_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL COMMENT 'Reference to hosting_orders if applicable',
  `payment_reason` varchar(255) NOT NULL COMMENT 'Reason for manual payment',
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `payment_method` varchar(50) DEFAULT 'manual',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'paid',
  `order_date` date NOT NULL COMMENT 'Date when payment was made',
  `start_date` date DEFAULT NULL COMMENT 'Service start date if applicable',
  `end_date` date DEFAULT NULL COMMENT 'Service end date if applicable',
  `description` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL COMMENT 'Internal admin notes',
  `created_by` int(11) DEFAULT NULL COMMENT 'Admin user who created this payment',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `payment_method` varchar(50) DEFAULT 'razorpay',
  `payment_status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `razorpay_signature` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  `payment_description` text DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `renewal_history`
--

CREATE TABLE `renewal_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `previous_order_id` int(11) DEFAULT NULL COMMENT 'Previous order that was renewed',
  `renewal_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` enum('monthly','yearly','2years','4years') NOT NULL,
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `renewal_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `auto_renewal` tinyint(1) NOT NULL DEFAULT 0,
  `payment_id` int(11) DEFAULT NULL COMMENT 'Reference to payment_history',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','email','url','color','file','json') NOT NULL DEFAULT 'text',
  `setting_group` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether setting can be accessed publicly',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','user','moderator') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `profile_picture` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hosting_orders`
--
ALTER TABLE `hosting_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD UNIQUE KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_package_id` (`package_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_expiry_date` (`expiry_date`);

--
-- Indexes for table `hosting_packages`
--
ALTER TABLE `hosting_packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Indexes for table `hosting_websites`
--
ALTER TABLE `hosting_websites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_package_id` (`package_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_domain` (`domain_name`);

--
-- Indexes for table `manual_payments`
--
ALTER TABLE `manual_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_order_date` (`order_date`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_razorpay_order` (`razorpay_order_id`);

--
-- Indexes for table `renewal_history`
--
ALTER TABLE `renewal_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_renewal_date` (`renewal_date`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD UNIQUE KEY `idx_setting_key` (`setting_key`),
  ADD KEY `idx_setting_group` (`setting_group`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hosting_orders`
--
ALTER TABLE `hosting_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hosting_packages`
--
ALTER TABLE `hosting_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hosting_websites`
--
ALTER TABLE `hosting_websites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manual_payments`
--
ALTER TABLE `manual_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `renewal_history`
--
ALTER TABLE `renewal_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hosting_orders`
--
ALTER TABLE `hosting_orders`
  ADD CONSTRAINT `fk_orders_package` FOREIGN KEY (`package_id`) REFERENCES `hosting_packages` (`id`),
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hosting_websites`
--
ALTER TABLE `hosting_websites`
  ADD CONSTRAINT `fk_websites_order` FOREIGN KEY (`order_id`) REFERENCES `hosting_orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_websites_package` FOREIGN KEY (`package_id`) REFERENCES `hosting_packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_websites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `manual_payments`
--
ALTER TABLE `manual_payments`
  ADD CONSTRAINT `fk_manual_payments_admin` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_manual_payments_order` FOREIGN KEY (`order_id`) REFERENCES `hosting_orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_manual_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `hosting_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `renewal_history`
--
ALTER TABLE `renewal_history`
  ADD CONSTRAINT `fk_renewal_order` FOREIGN KEY (`order_id`) REFERENCES `hosting_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_renewal_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
