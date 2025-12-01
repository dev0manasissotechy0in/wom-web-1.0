-- =====================================================
-- Quick Fix for site_settings table error
-- Run this if you're getting "Unknown column" error
-- =====================================================

-- Drop the table if it exists with wrong structure
DROP TABLE IF EXISTS `site_settings`;

-- Create site_settings table with correct structure
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('dark_mode_enabled', '1', 'boolean', 'Enable/disable dark mode feature'),
('footer_legal_links_enabled', '1', 'boolean', 'Show legal links in footer'),
('resource_download_notify_admin', '1', 'boolean', 'Send email notifications for resource downloads'),
('newsletter_auto_send_welcome', '1', 'boolean', 'Automatically send welcome email to new subscribers');

-- Verify it worked
SELECT * FROM site_settings;
