-- Add Razorpay Payment Settings to the settings table

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `description`, `is_public`) VALUES
-- Razorpay Configuration
('razorpay_key_id', 'YOUR_RAZORPAY_KEY_ID', 'text', 'payment', 'Razorpay Key ID from dashboard', 0),
('razorpay_key_secret', 'YOUR_RAZORPAY_KEY_SECRET', 'text', 'payment', 'Razorpay Key Secret from dashboard', 0),
('razorpay_enabled', '0', 'boolean', 'payment', 'Enable Razorpay payment gateway', 0),
('razorpay_currency', 'INR', 'text', 'payment', 'Razorpay payment currency', 0)
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;

-- Update hosting_websites table to allow NULL for order_id and package_id
ALTER TABLE `hosting_websites` 
    MODIFY `order_id` int(11) DEFAULT NULL,
    MODIFY `package_id` int(11) DEFAULT NULL;

-- Update foreign key constraints
ALTER TABLE `hosting_websites`
    DROP FOREIGN KEY IF EXISTS `fk_websites_order`,
    DROP FOREIGN KEY IF EXISTS `fk_websites_package`;

ALTER TABLE `hosting_websites`
    ADD CONSTRAINT `fk_websites_order` FOREIGN KEY (`order_id`) REFERENCES `hosting_orders` (`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_websites_package` FOREIGN KEY (`package_id`) REFERENCES `hosting_packages` (`id`) ON DELETE SET NULL;

