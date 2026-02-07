-- Migration: Add Admin Plans and Server Credentials Support
-- Date: 2026-02-07
-- Description: Adds support for admin-only plans, manual plan assignments, and server credentials

-- Add server credentials to users table
ALTER TABLE `users` 
ADD COLUMN `server_username` VARCHAR(255) DEFAULT NULL COMMENT 'CloudPanel server username',
ADD COLUMN `server_password` VARCHAR(255) DEFAULT NULL COMMENT 'CloudPanel server password (encrypted)',
ADD COLUMN `server_url` VARCHAR(255) DEFAULT 'https://server.infralabs.cloud' COMMENT 'Server URL for CloudPanel access';

-- Add admin plan flags to hosting_packages table
ALTER TABLE `hosting_packages`
ADD COLUMN `is_admin_only` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If 1, plan is only visible to admin',
ADD COLUMN `is_private` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If 1, package is hidden from public (admin only)',
ADD COLUMN `renewal_price_monthly` DECIMAL(10,2) DEFAULT NULL COMMENT 'Renewal price for monthly billing',
ADD COLUMN `renewal_price_yearly` DECIMAL(10,2) DEFAULT NULL COMMENT 'Renewal price for yearly billing',
ADD COLUMN `renewal_price_2years` DECIMAL(10,2) DEFAULT NULL COMMENT 'Renewal price for 2 years billing',
ADD COLUMN `renewal_price_4years` DECIMAL(10,2) DEFAULT NULL COMMENT 'Renewal price for 4 years billing',
ADD COLUMN `created_by_admin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If 1, plan was created by admin for manual assignment';

-- Add assignment tracking to hosting_orders table
ALTER TABLE `hosting_orders`
ADD COLUMN `assigned_by_admin_id` INT(11) DEFAULT NULL COMMENT 'Admin user ID who manually assigned this plan',
ADD COLUMN `is_manual_assignment` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If 1, plan was manually assigned by admin',
ADD COLUMN `assignment_notes` TEXT DEFAULT NULL COMMENT 'Admin notes about the assignment',
ADD COLUMN `upgraded_from_order_id` INT(11) DEFAULT NULL COMMENT 'Previous order ID if this is an upgrade',
ADD INDEX `idx_assigned_by` (`assigned_by_admin_id`),
ADD INDEX `idx_manual_assignment` (`is_manual_assignment`),
ADD INDEX `idx_upgraded_from` (`upgraded_from_order_id`);

-- Create plan_assignments table for tracking manual assignments
CREATE TABLE IF NOT EXISTS `plan_assignments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `package_id` INT(11) NOT NULL,
  `assigned_by_admin_id` INT(11) NOT NULL,
  `billing_cycle` ENUM('monthly','yearly','2years','4years') NOT NULL DEFAULT 'monthly',
  `start_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `renewal_date` DATE DEFAULT NULL,
  `auto_renewal` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `assignment_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_package_id` (`package_id`),
  KEY `idx_assigned_by` (`assigned_by_admin_id`),
  KEY `idx_status` (`status`),
  KEY `idx_expiry_date` (`expiry_date`),
  CONSTRAINT `fk_assignment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assignment_package` FOREIGN KEY (`package_id`) REFERENCES `hosting_packages` (`id`),
  CONSTRAINT `fk_assignment_admin` FOREIGN KEY (`assigned_by_admin_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better query performance
ALTER TABLE `hosting_packages`
ADD INDEX `idx_admin_only` (`is_admin_only`),
ADD INDEX `idx_created_by_admin` (`created_by_admin`);

-- Add comments to existing columns for clarity
ALTER TABLE `hosting_orders`
MODIFY COLUMN `renewed_from_order_id` INT(11) DEFAULT NULL COMMENT 'Previous order ID if this is a renewal/upgrade';
