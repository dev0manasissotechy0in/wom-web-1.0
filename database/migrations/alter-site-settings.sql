-- =====================================================
-- Alter site_settings table to add new columns
-- Date: 2025-11-24
-- =====================================================

-- Add new columns to existing site_settings table
ALTER TABLE `site_settings` 
ADD COLUMN `dark_mode_enabled` TINYINT(1) DEFAULT 1 COMMENT 'Enable/disable dark mode feature' AFTER `google_tag_manager_id`,
ADD COLUMN `footer_legal_links_enabled` TINYINT(1) DEFAULT 1 COMMENT 'Show legal links in footer' AFTER `dark_mode_enabled`,
ADD COLUMN `resource_download_notify_admin` TINYINT(1) DEFAULT 1 COMMENT 'Send email notifications for resource downloads' AFTER `footer_legal_links_enabled`,
ADD COLUMN `newsletter_auto_send_welcome` TINYINT(1) DEFAULT 1 COMMENT 'Automatically send welcome email to new subscribers' AFTER `resource_download_notify_admin`;

-- Verify the columns were added
SELECT 'site_settings table altered successfully' as status;

-- Show the updated structure
DESCRIBE site_settings;
