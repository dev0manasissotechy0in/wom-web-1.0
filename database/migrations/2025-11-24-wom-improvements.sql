-- =====================================================
-- WOM Website Improvements - Database Migration
-- Date: 2025-11-24
-- =====================================================

-- 1. SMTP Settings Table for Newsletter
CREATE TABLE IF NOT EXISTS `smtp_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(11) NOT NULL DEFAULT 587,
  `smtp_username` varchar(255) NOT NULL,
  `smtp_password` varchar(255) NOT NULL,
  `smtp_encryption` enum('tls','ssl','none') DEFAULT 'tls',
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default SMTP settings
INSERT INTO `smtp_settings` 
(`smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption`, `from_email`, `from_name`, `is_active`) 
VALUES 
('smtp.hostinger.com', 465, 'thesaasinsider@wallofmarketing.co', 'U~4nAR1G$9|m', 'ssl', 'thesaasinsider@wallofmarketing.co', 'Wall of Marketing', 1);

-- 2. Page Management System
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `page_type` enum('legal','standard','custom') DEFAULT 'standard',
  `show_in_footer` tinyint(1) DEFAULT 0,
  `footer_order` int(11) DEFAULT 0,
  `status` enum('published','draft') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `page_type` (`page_type`),
  KEY `show_in_footer` (`show_in_footer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default legal pages
INSERT INTO `pages` (`title`, `slug`, `content`, `page_type`, `show_in_footer`, `footer_order`, `status`) VALUES
('Privacy Policy', 'privacy-policy', '<h1>Privacy Policy</h1><p>This page will be populated with your privacy policy content.</p>', 'legal', 1, 1, 'published'),
('Terms & Conditions', 'terms-conditions', '<h1>Terms & Conditions</h1><p>This page will be populated with your terms and conditions content.</p>', 'legal', 1, 2, 'published'),
('Cookie Policy', 'cookie-policy', '<h1>Cookie Policy</h1><p>This page will be populated with your cookie policy content.</p>', 'legal', 1, 3, 'published'),
('Refund Policy', 'refund-policy', '<h1>Refund Policy</h1><p>This page will be populated with your refund policy content.</p>', 'legal', 1, 4, 'published'),
('Disclaimer', 'disclaimer', '<h1>Disclaimer</h1><p>This page will be populated with your disclaimer content.</p>', 'legal', 1, 5, 'published');

-- 3. User Preferences (for Dark Mode)
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Fix resource_downloads table if needed
CREATE TABLE IF NOT EXISTS `resource_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `resource_id` (`resource_id`),
  KEY `email` (`email`),
  KEY `downloaded_at` (`downloaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Ensure newsletter_subscribers table exists
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT 'Anonymous',
  `newsletter_type` varchar(100) DEFAULT 'main',
  `status` enum('subscribed','unsubscribed','pending') DEFAULT 'subscribed',
  `ip_address` varchar(45) DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_newsletter` (`email`,`newsletter_type`),
  KEY `status` (`status`),
  KEY `newsletter_type` (`newsletter_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Settings table for general configurations
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('dark_mode_enabled', '1', 'boolean', 'Enable/disable dark mode feature'),
('footer_legal_links_enabled', '1', 'boolean', 'Show legal links in footer'),
('resource_download_notify_admin', '1', 'boolean', 'Send email notifications for resource downloads'),
('newsletter_auto_send_welcome', '1', 'boolean', 'Automatically send welcome email to new subscribers');

-- =====================================================
-- End of Migration
-- =====================================================
