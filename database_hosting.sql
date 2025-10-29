-- Hosting Packages Management
-- Create hosting packages table

CREATE TABLE IF NOT EXISTS `hosting_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  
  -- Pricing for different billing cycles
  `price_monthly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_yearly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_2years` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_4years` decimal(10,2) NOT NULL DEFAULT 0.00,
  
  -- Package Resources
  `storage_gb` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Disk space in GB',
  `bandwidth_gb` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Bandwidth in GB',
  `allowed_websites` int(11) NOT NULL DEFAULT 1,
  `database_limit` int(11) NOT NULL DEFAULT 1,
  `ftp_accounts` int(11) NOT NULL DEFAULT 1,
  `email_accounts` int(11) NOT NULL DEFAULT 1,
  
  -- Features
  `ssh_access` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'SSH Access',
  `ssl_free` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Free SSL Certificate',
  `daily_backups` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Daily Backups',
  `dedicated_ip` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Dedicated IP',
  `php_version` varchar(50) DEFAULT '7.4' COMMENT 'PHP Version',
  `mysql_version` varchar(50) DEFAULT '5.7' COMMENT 'MySQL Version',
  
  -- Additional Charges
  `setup_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'One-time setup fee',
  `gst_percentage` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'GST percentage',
  `processing_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Processing fee',
  
  -- Status
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'inactive',
  `is_popular` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Mark as popular package',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order',
  
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Management
-- Create orders table

CREATE TABLE IF NOT EXISTS `hosting_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL UNIQUE,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  
  -- Pricing Details
  `billing_cycle` enum('monthly','yearly','2years','4years') NOT NULL DEFAULT 'monthly',
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `setup_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `processing_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  
  -- Order Details
  `domain_name` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','processing','active','suspended','cancelled','expired') NOT NULL DEFAULT 'pending',
  
  -- Subscription Details
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `renewal_date` date DEFAULT NULL,
  `auto_renewal` tinyint(1) NOT NULL DEFAULT 0,
  
  -- Payment Gateway Details
  `payment_id` varchar(255) DEFAULT NULL COMMENT 'Payment gateway order ID',
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL COMMENT 'Internal admin notes',
  
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_number` (`order_number`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_package_id` (`package_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_order_status` (`order_status`),
  KEY `idx_expiry_date` (`expiry_date`),
  
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orders_package` FOREIGN KEY (`package_id`) REFERENCES `hosting_packages` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Hosting Packages
INSERT INTO `hosting_packages` (
  `name`, `slug`, `description`, `short_description`,
  `price_monthly`, `price_yearly`, `price_2years`, `price_4years`,
  `storage_gb`, `bandwidth_gb`, `allowed_websites`, `database_limit`, 
  `ftp_accounts`, `email_accounts`, `ssh_access`, `ssl_free`, 
  `daily_backups`, `gst_percentage`, `setup_fee`, `status`, `is_popular`, `sort_order`
) VALUES 
-- Starter Package
('Starter', 'starter', 'Perfect for small personal websites and blogs', 'Perfect for beginners',
  299.00, 2990.00, 5580.00, 10760.00,
  5.00, 50.00, 1, 1, 1, 5, 0, 1, 0, 18.00, 0.00, 'active', 0, 1),

-- Professional Package
('Professional', 'professional', 'Ideal for growing businesses and e-commerce sites', 'Best for businesses',
  599.00, 5980.00, 11362.00, 22324.00,
  25.00, 200.00, 5, 10, 5, 50, 1, 1, 1, 18.00, 99.00, 'active', 1, 2),

-- Enterprise Package
('Enterprise', 'enterprise', 'For high-traffic websites and applications', 'For enterprise solutions',
  1499.00, 14980.00, 28862.00, 56624.00,
  100.00, 1000.00, 20, 50, 20, 250, 1, 1, 1, 18.00, 299.00, 'active', 0, 3),

-- Ultimate Package
('Ultimate', 'ultimate', 'Unlimited resources for power users', 'Unlimited everything',
  2999.00, 29980.00, 57562.00, 112724.00,
  500.00, 5000.00, 999999, 999999, 999999, 999999, 1, 1, 1, 18.00, 599.00, 'active', 0, 4);

-- Websites Management Table
CREATE TABLE IF NOT EXISTS `hosting_websites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  
  -- Website Details
  `website_name` varchar(255) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `website_url` varchar(500) DEFAULT NULL,
  
  -- SSH Access
  `ssh_username` varchar(100) DEFAULT NULL,
  `ssh_password` varchar(255) DEFAULT NULL,
  `ssh_host` varchar(255) DEFAULT NULL,
  `ssh_port` int(11) DEFAULT 22,
  
  -- Database Access
  `db_name` varchar(100) DEFAULT NULL,
  `db_username` varchar(100) DEFAULT NULL,
  `db_password` varchar(255) DEFAULT NULL,
  `db_host` varchar(255) DEFAULT NULL,
  
  -- FTP Access (if different from SSH)
  `ftp_username` varchar(100) DEFAULT NULL,
  `ftp_password` varchar(255) DEFAULT NULL,
  `ftp_host` varchar(255) DEFAULT NULL,
  `ftp_port` int(11) DEFAULT 21,
  
  -- Control Panel Access
  `cpanel_url` varchar(500) DEFAULT NULL,
  `cpanel_username` varchar(100) DEFAULT NULL,
  `cpanel_password` varchar(255) DEFAULT NULL,
  
  -- Status & Info
  `status` enum('active','suspended','deleted') NOT NULL DEFAULT 'active',
  `payment_status` enum('paid','pending','overdue') NOT NULL DEFAULT 'pending',
  `server_ip` varchar(45) DEFAULT NULL,
  `nameservers` text DEFAULT NULL COMMENT 'Comma separated nameserver addresses',
  
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_package_id` (`package_id`),
  KEY `idx_status` (`status`),
  KEY `idx_domain` (`domain_name`),
  
  CONSTRAINT `fk_websites_order` FOREIGN KEY (`order_id`) REFERENCES `hosting_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_websites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_websites_package` FOREIGN KEY (`package_id`) REFERENCES `hosting_packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment History Table
CREATE TABLE IF NOT EXISTS `payment_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  
  -- Payment Details
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `payment_method` varchar(50) DEFAULT 'razorpay',
  `payment_status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  
  -- Razorpay Details
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `razorpay_signature` varchar(255) DEFAULT NULL,
  
  -- Transaction Details
  `transaction_id` varchar(255) DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  
  -- Additional Info
  `payment_description` text DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_date` timestamp NULL DEFAULT NULL,
  
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_razorpay_order` (`razorpay_order_id`),
  
  CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `hosting_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Renewal History Table
CREATE TABLE IF NOT EXISTS `renewal_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `previous_order_id` int(11) DEFAULT NULL COMMENT 'Previous order that was renewed',
  
  -- Renewal Details
  `renewal_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` enum('monthly','yearly','2years','4years') NOT NULL,
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  
  -- Dates
  `renewal_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `auto_renewal` tinyint(1) NOT NULL DEFAULT 0,
  
  -- Payment Reference
  `payment_id` int(11) DEFAULT NULL COMMENT 'Reference to payment_history',
  
  `notes` text DEFAULT NULL,
  
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_renewal_date` (`renewal_date`),
  
  CONSTRAINT `fk_renewal_order` FOREIGN KEY (`order_id`) REFERENCES `hosting_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_renewal_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

