-- Migration: Move fees to global settings & add renewal pricing columns
-- Date: 2026-02-10
-- Description: 
--   1. Add renewal price columns to hosting_packages
--   2. Remove per-package fee columns (setup_fee, gst_percentage, processing_fee)
--      from hosting_packages since fees are now managed universally via settings table
--   3. Ensure global fee settings exist in the settings table

-- Step 1: Add renewal price columns to hosting_packages (if not exists)
ALTER TABLE `hosting_packages`
  ADD COLUMN IF NOT EXISTS `renewal_price_monthly` decimal(10,2) DEFAULT NULL AFTER `price_4years`,
  ADD COLUMN IF NOT EXISTS `renewal_price_yearly` decimal(10,2) DEFAULT NULL AFTER `renewal_price_monthly`,
  ADD COLUMN IF NOT EXISTS `renewal_price_2years` decimal(10,2) DEFAULT NULL AFTER `renewal_price_yearly`,
  ADD COLUMN IF NOT EXISTS `renewal_price_4years` decimal(10,2) DEFAULT NULL AFTER `renewal_price_2years`;

-- Step 2: Drop per-package fee columns (fees now come from global settings)
ALTER TABLE `hosting_packages`
  DROP COLUMN IF EXISTS `setup_fee`,
  DROP COLUMN IF EXISTS `gst_percentage`,
  DROP COLUMN IF EXISTS `processing_fee`;

-- Step 3: Ensure global payment settings exist
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `description`, `is_public`)
VALUES 
  ('global_setup_fee', '0.00', 'number', 'payment', 'One-time setup fee applied to all new orders', 0),
  ('global_gst_percentage', '18.00', 'number', 'payment', 'GST percentage applied to all orders', 0),
  ('global_processing_fee', '0.00', 'number', 'payment', 'Processing fee applied to all orders', 0),
  ('currency_symbol', '₹', 'text', 'payment', 'Currency symbol for display', 1),
  ('currency_code', 'INR', 'text', 'payment', 'Currency code for transactions', 1)
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- Step 4: Update db.sql hosting_orders - the columns setup_fee, gst_amount, processing_fee 
-- STAY in hosting_orders because they record what was actually charged at time of purchase.
-- They are just populated from global settings now instead of per-package settings.
