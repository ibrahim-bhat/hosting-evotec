-- SecureAuth Database Schema
-- Users Table

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','user','moderator') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `profile_picture` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','email','url','color','file','json') NOT NULL DEFAULT 'text',
  `setting_group` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether setting can be accessed publicly',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_setting_key` (`setting_key`),
  KEY `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `description`, `is_public`) VALUES
-- Company Information
('company_name', 'SecureAuth', 'text', 'company', 'Company name displayed across the application', 1),
('company_logo', NULL, 'file', 'company', 'Company logo - used everywhere including favicon (recommended: 200x200px, PNG/SVG)', 1),
('company_email', 'info@secureauth.com', 'email', 'company', 'Official company email address', 1),
('company_phone', '+1 (555) 123-4567', 'text', 'company', 'Company contact phone number', 1),
('company_address', '123 Main Street, City, State 12345', 'text', 'company', 'Company physical address', 1),

-- System Configuration
('timezone', 'UTC', 'text', 'system', 'System timezone', 0),
('date_format', 'Y-m-d', 'text', 'system', 'Date format for display', 0),
('time_format', 'H:i:s', 'text', 'system', 'Time format for display', 0),
('currency', 'USD', 'text', 'system', 'Default currency code', 1),
('currency_symbol', '$', 'text', 'system', 'Currency symbol', 1),

-- Email Settings
('smtp_enabled', '0', 'boolean', 'email', 'Enable SMTP email sending', 0),
('smtp_host', '', 'text', 'email', 'SMTP server host', 0),
('smtp_port', '587', 'number', 'email', 'SMTP server port', 0),
('smtp_username', '', 'text', 'email', 'SMTP authentication username', 0),
('smtp_password', '', 'text', 'email', 'SMTP authentication password', 0),
('smtp_encryption', 'tls', 'text', 'email', 'SMTP encryption type (tls/ssl/none)', 0),
('smtp_from_email', '', 'email', 'email', 'Email sender address', 0),
('smtp_from_name', 'SecureAuth', 'text', 'email', 'Email sender name', 0),

-- Security Settings
('session_timeout', '30', 'number', 'security', 'Session timeout in minutes (0 = browser session)', 0),
('two_factor_enabled', '0', 'boolean', 'security', 'Enable two-factor authentication', 0),

-- Maintenance
('maintenance_mode', '0', 'boolean', 'maintenance', 'Enable maintenance mode', 0),
('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.', 'text', 'maintenance', 'Message displayed during maintenance', 0),

-- Registration Settings
('allow_registration', '1', 'boolean', 'registration', 'Allow new user registration', 1),
('email_verification_required', '0', 'boolean', 'registration', 'Require email verification for new accounts', 0),
('default_user_role', 'user', 'text', 'registration', 'Default role for new users', 0),
('default_user_status', 'active', 'text', 'registration', 'Default status for new users', 0);


