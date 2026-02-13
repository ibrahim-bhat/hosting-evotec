-- =====================================================
-- Migration 003: Mail System, OTP Verification, 
--               Universal Renewal Markup
-- =====================================================

-- 1. Add email verification flag to users table
ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER status;

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
    ('smtp_password', '', 'password', 'email', 'SMTP password or app password', 0),
    ('smtp_encryption', 'tls', 'text', 'email', 'SMTP encryption method (tls or ssl)', 0),
    ('smtp_from_email', '', 'email', 'email', 'Sender email address', 0),
    ('smtp_from_name', '', 'text', 'email', 'Sender display name', 0),
    ('smtp_enabled', '0', 'boolean', 'email', 'Enable or disable email sending', 0)
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- 4. Insert universal renewal markup setting
INSERT INTO settings (setting_key, setting_value, setting_type, setting_group, description, is_public)
VALUES ('renewal_markup_percentage', '0', 'decimal', 'payment', 'Renewal markup percentage added to base price (e.g. 60 means price + 60%)', 0)
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- 5. Drop per-package renewal price columns (now handled by global markup)
-- Note: Run these only after confirming the code changes are deployed
ALTER TABLE hosting_packages
    DROP COLUMN IF EXISTS renewal_price_monthly,
    DROP COLUMN IF EXISTS renewal_price_yearly,
    DROP COLUMN IF EXISTS renewal_price_2years,
    DROP COLUMN IF EXISTS renewal_price_4years;

-- 6. Mark all existing users as verified (they should not need OTP)
UPDATE users SET is_verified = 1 WHERE is_verified = 0;
