-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2026 at 06:54 PM
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
  `renewed_from_order_id` int(11) DEFAULT NULL COMMENT 'Previous order ID if this is a renewal/upgrade',
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

--
-- Dumping data for table `hosting_orders`
--

INSERT INTO `hosting_orders` (`id`, `order_number`, `renewed_from_order_id`, `user_id`, `package_id`, `billing_cycle`, `base_price`, `setup_fee`, `gst_amount`, `processing_fee`, `subtotal`, `total_amount`, `domain_name`, `payment_method`, `payment_status`, `order_status`, `start_date`, `expiry_date`, `renewal_date`, `auto_renewal`, `payment_id`, `razorpay_order_id`, `razorpay_payment_id`, `payment_date`, `notes`, `admin_notes`, `created_at`, `updated_at`) VALUES
(3, 'ORD202510264B1FAD', NULL, 4, 1, 'monthly', 299.00, 0.00, 53.82, 0.00, 299.00, 352.82, NULL, NULL, 'paid', 'expired', '2022-10-05', '2024-11-06', '2025-11-26', 0, 'pay_RY5v2nb8LVXzfO', NULL, NULL, '2025-10-26 12:05:43', NULL, NULL, '2025-10-26 12:02:12', '2026-01-14 18:45:41'),
(5, 'ORD20251026675E93', NULL, 4, 3, 'monthly', 1499.00, 299.00, 323.64, 0.00, 1798.00, 2121.64, NULL, NULL, 'paid', 'expired', '2025-10-26', '2025-11-26', '2025-11-26', 0, 'pay_RY66USrRmbWH29', NULL, NULL, '2025-10-26 12:13:32', NULL, NULL, '2025-10-26 12:13:10', '2026-01-14 18:45:41'),
(8, 'ORD20260114B509CB', NULL, 6, 1, 'monthly', 299.00, 0.00, 0.00, 0.00, 299.00, 299.00, NULL, NULL, 'paid', 'expired', '2025-01-15', '2024-02-01', '2024-02-01', 0, 'pay_S3r5a2gfhoPWRC', NULL, NULL, '2026-01-14 18:20:20', NULL, NULL, '2026-01-14 18:19:55', '2026-01-14 18:45:41'),
(10, 'ORD20260114D7B076', 8, 6, 4, 'monthly', 2999.00, 0.00, 0.00, 0.00, 2999.00, 2999.00, NULL, NULL, 'paid', 'expired', '2023-01-03', '2024-02-01', '2024-02-01', 0, 'pay_S3rIlQJv2ObRoi', NULL, NULL, '2026-01-14 18:32:49', NULL, NULL, '2026-01-14 18:32:29', '2026-01-14 18:45:41'),
(13, 'ORD202601140A2073', 10, 6, 4, 'monthly', 2999.00, 0.00, 0.00, 0.00, 2999.00, 2999.00, NULL, NULL, 'paid', 'active', '2026-01-14', '2026-02-14', '2026-02-14', 0, 'pay_S3rTX4TzTlL8Kq', NULL, NULL, '2026-01-14 18:43:01', NULL, NULL, '2026-01-14 18:42:40', '2026-01-14 18:43:03'),
(14, 'ORD202601179228EF', NULL, 1, 2, 'yearly', 5980.00, 0.00, 0.00, 0.00, 5980.00, 5980.00, NULL, NULL, 'paid', 'active', '2026-01-17', '2027-01-17', '2027-01-17', 0, 'pay_S512NJgnqOsQSz', NULL, NULL, '2026-01-17 16:43:14', NULL, NULL, '2026-01-17 16:42:49', '2026-01-17 16:43:14'),
(15, 'ORD2026020771747E', NULL, 7, 2, 'yearly', 5980.00, 0.00, 0.00, 0.00, 5980.00, 5980.00, NULL, NULL, 'paid', 'active', '2026-02-03', '2026-02-06', '2026-02-06', 0, 'pay_SDLOARaverc0DH', NULL, NULL, '2026-02-07 17:49:45', NULL, NULL, '2026-02-07 17:49:11', '2026-02-07 17:50:31');

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
  ADD KEY `idx_expiry_date` (`expiry_date`),
  ADD KEY `idx_renewed_from` (`renewed_from_order_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hosting_orders`
--
ALTER TABLE `hosting_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hosting_orders`
--
ALTER TABLE `hosting_orders`
  ADD CONSTRAINT `fk_orders_package` FOREIGN KEY (`package_id`) REFERENCES `hosting_packages` (`id`),
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
