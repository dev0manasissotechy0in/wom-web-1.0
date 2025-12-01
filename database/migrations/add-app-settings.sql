-- =====================================================
-- Add Application Settings Table (Separate from site_settings)
-- Date: 2025-11-24
-- =====================================================

-- Create app_settings table for application-specific configurations
CREATE TABLE IF NOT EXISTS `app_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default app settings (using INSERT IGNORE to avoid duplicates)
INSERT IGNORE INTO `app_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('dark_mode_enabled', '1', 'boolean', 'Enable/disable dark mode feature'),
('footer_legal_links_enabled', '1', 'boolean', 'Show legal links in footer'),
('resource_download_notify_admin', '1', 'boolean', 'Send email notifications for resource downloads'),
('newsletter_auto_send_welcome', '1', 'boolean', 'Automatically send welcome email to new subscribers');

-- Verify the settings were created
SELECT 'app_settings table created successfully' as status;
SELECT * FROM app_settings;
