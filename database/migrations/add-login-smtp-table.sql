-- =====================================================
-- Admin Login SMTP Settings Table
-- Separate SMTP configuration for OTP authentication emails
-- Date: 2025-11-30
-- =====================================================

CREATE TABLE IF NOT EXISTS `login_smtp_settings` (
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

-- Insert default Outlook SMTP settings for admin login
INSERT INTO `login_smtp_settings` 
(`smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption`, `from_email`, `from_name`, `is_active`) 
VALUES 
('smtp-mail.outlook.com', 587, 'wallofmarketing@outlook.com', '', 'tls', 'wallofmarketing@outlook.com', 'Wall of Marketing - Admin', 1);

-- Note: Update the smtp_password with your actual Outlook App Password
-- Generate App Password at: https://account.microsoft.com/security
-- Advanced security options > App passwords > Create new app password
