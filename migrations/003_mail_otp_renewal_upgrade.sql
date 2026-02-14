-- =====================================================
-- Migration 003: Mail System, OTP Verification, 
--               Universal Renewal Markup
-- =====================================================

-- 1. Add email verification flag to users table (skip if column already exists)
DROP PROCEDURE IF EXISTS _migration_003_add_is_verified;
DELIMITER //
CREATE PROCEDURE _migration_003_add_is_verified()
BEGIN
    IF (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_verified') = 0 THEN
        ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER status;
    END IF;
END //
DELIMITER ;
CALL _migration_003_add_is_verified();
DROP PROCEDURE _migration_003_add_is_verified;

-- 2. Create OTP verifications table
CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Insert SMTP / Email settings
INSERT INTO settings (setting_key, setting_value, setting_type, setting_group, description, is_public)
VALUES
    ('smtp_host', '', 'text', 'email', 'SMTP server hostname', 0),
    ('smtp_port', '587', 'number', 'email', 'SMTP server port', 0),
    ('smtp_username', '', 'text', 'email', 'SMTP username/email', 0),
    ('smtp_password', '', 'text', 'email', 'SMTP password or app password', 0),
    ('smtp_encryption', 'tls', 'text', 'email', 'SMTP encryption method (tls or ssl)', 0),
    ('smtp_from_email', '', 'text', 'email', 'Sender email address', 0),
    ('smtp_from_name', '', 'text', 'email', 'Sender display name', 0),
    ('smtp_enabled', '0', 'boolean', 'email', 'Enable or disable email sending', 0)
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- 4. Insert universal renewal markup setting
INSERT INTO settings (setting_key, setting_value, setting_type, setting_group, description, is_public)
VALUES ('renewal_markup_percentage', '0', 'number', 'payment', 'Renewal markup percentage added to base price (e.g. 60 means price + 60%)', 0)
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- 5. Drop per-package renewal price columns (now handled by global markup)
-- Uses a procedure so it works on MySQL 5.7 / MariaDB (no DROP COLUMN IF EXISTS)
DROP PROCEDURE IF EXISTS _migration_003_drop_renewal_columns;
DELIMITER //
CREATE PROCEDURE _migration_003_drop_renewal_columns()
BEGIN
    IF (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hosting_packages' AND COLUMN_NAME = 'renewal_price_monthly') > 0 THEN
        ALTER TABLE hosting_packages DROP COLUMN renewal_price_monthly;
    END IF;
    IF (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hosting_packages' AND COLUMN_NAME = 'renewal_price_yearly') > 0 THEN
        ALTER TABLE hosting_packages DROP COLUMN renewal_price_yearly;
    END IF;
    IF (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hosting_packages' AND COLUMN_NAME = 'renewal_price_2years') > 0 THEN
        ALTER TABLE hosting_packages DROP COLUMN renewal_price_2years;
    END IF;
    IF (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hosting_packages' AND COLUMN_NAME = 'renewal_price_4years') > 0 THEN
        ALTER TABLE hosting_packages DROP COLUMN renewal_price_4years;
    END IF;
END //
DELIMITER ;
CALL _migration_003_drop_renewal_columns();
DROP PROCEDURE _migration_003_drop_renewal_columns;

-- 6. Mark all existing users as verified (they should not need OTP)
UPDATE users SET is_verified = 1 WHERE is_verified = 0;
