-- Update Payment Settings - Move Additional Charges to Global Settings
-- This file moves setup fee, GST, and processing fee from packages to global settings

-- Add global payment settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `description`, `is_public`) VALUES
-- Global Payment Settings
('global_setup_fee', '0.00', 'decimal', 'payment', 'Global setup fee applied to all orders', 0),
('global_gst_percentage', '18.00', 'decimal', 'payment', 'Global GST percentage applied to all orders', 0),
('global_processing_fee', '0.00', 'decimal', 'payment', 'Global processing fee applied to all orders', 0),
('currency_symbol', '₹', 'text', 'payment', 'Currency symbol for display', 1),
('currency_code', 'INR', 'text', 'payment', 'Currency code', 1)
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;

-- Create manual payments table
CREATE TABLE IF NOT EXISTS `manual_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL COMMENT 'Reference to hosting_orders if applicable',
  
  -- Payment Details
  `payment_reason` varchar(255) NOT NULL COMMENT 'Reason for manual payment',
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `payment_method` varchar(50) DEFAULT 'manual',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'paid',
  
  -- Date Information
  `order_date` date NOT NULL COMMENT 'Date when payment was made',
  `start_date` date DEFAULT NULL COMMENT 'Service start date if applicable',
  `end_date` date DEFAULT NULL COMMENT 'Service end date if applicable',
  
  -- Additional Information
  `description` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL COMMENT 'Internal admin notes',
  `created_by` int(11) DEFAULT NULL COMMENT 'Admin user who created this payment',
  
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_order_date` (`order_date`),
  KEY `idx_created_by` (`created_by`),
  
  CONSTRAINT `fk_manual_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_manual_payments_order` FOREIGN KEY (`order_id`) REFERENCES `hosting_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_manual_payments_admin` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Remove additional charges columns from hosting_packages table
-- (We'll keep them for backward compatibility but they won't be used)
-- ALTER TABLE `hosting_packages` 
--     DROP COLUMN `setup_fee`,
--     DROP COLUMN `gst_percentage`, 
--     DROP COLUMN `processing_fee`;

-- Update existing packages to remove additional charges (set to 0)
UPDATE `hosting_packages` SET 
    `setup_fee` = 0.00,
    `gst_percentage` = 0.00,
    `processing_fee` = 0.00;
