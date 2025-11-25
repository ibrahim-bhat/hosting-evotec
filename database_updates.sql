-- SQL to update hosting_packages table structure
-- This removes old resource fields and adds a features textarea field

-- Step 1: Drop the old resource and feature columns
ALTER TABLE `hosting_packages` 
DROP COLUMN IF EXISTS `storage_gb`,
DROP COLUMN IF EXISTS `bandwidth_gb`,
DROP COLUMN IF EXISTS `allowed_websites`,
DROP COLUMN IF EXISTS `database_limit`,
DROP COLUMN IF EXISTS `email_accounts`,
DROP COLUMN IF EXISTS `ftp_accounts`,
DROP COLUMN IF EXISTS `ssh_access`,
DROP COLUMN IF EXISTS `ssl_free`,
DROP COLUMN IF EXISTS `daily_backups`,
DROP COLUMN IF EXISTS `dedicated_ip`;

-- Step 2: Add new features field as TEXT to store manual features
ALTER TABLE `hosting_packages` 
ADD COLUMN `features` TEXT NULL AFTER `short_description`;

-- Step 3: Make pricing fields nullable (so you can skip cycles)
ALTER TABLE `hosting_packages` 
MODIFY COLUMN `price_monthly` DECIMAL(10,2) NULL DEFAULT NULL,
MODIFY COLUMN `price_yearly` DECIMAL(10,2) NULL DEFAULT NULL,
MODIFY COLUMN `price_2years` DECIMAL(10,2) NULL DEFAULT NULL,
MODIFY COLUMN `price_4years` DECIMAL(10,2) NULL DEFAULT NULL;

-- Sample features for different package types (uncomment and modify as needed