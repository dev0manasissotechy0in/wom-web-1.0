<?php
/**
 * Site Constants & Configuration
 * Auto-generated from database settings
 * Last updated: {date('Y-m-d H:i:s')}
 */

// ================================================
// SITE INFORMATION
// ================================================
define('SITE_NAME', 'Wall of Marketing - WOM is a SaaS Marketing and Video Creative Agency');
define('SITE_TAGLINE', 'Transform Your Digital Presence');
define('SITE_URL', 'https://wallofmarketing.co');
define('SITE_DESCRIPTION', 'Wall of Marketing - WOM is a SaaS Marketing and Video Creative Agency. Software as a Services Marketing made easy');
define('SITE_KEYWORDS', 'SaaS Marketing, Wall of Marketing, Digital Marketing, Performance Marketing, WOM, Video Production &amp;amp; Editing.');

// ================================================
// CONTACT INFORMATION
// ================================================
define('CONTACT_EMAIL', 'connect@wallofmarketing.co');
define('CONTACT_PHONE', '');
define('CONTACT_ADDRESS', 'Banglore, KA');
define('ADMIN_EMAIL', 'connect@wallofmarketing.co');

// ================================================
// SOCIAL MEDIA LINKS
// ================================================
define('SOCIAL_FACEBOOK', 'https://www.facebook.com/profile.php?id=61582836824950');
define('SOCIAL_TWITTER', 'https://x.com/womglobal');
define('SOCIAL_LINKEDIN', 'https://www.linkedin.com/company/wall-of-marketing');
define('SOCIAL_INSTAGRAM', 'https://www.instagram.com/wallofmarketing.global/');
define('SOCIAL_YOUTUBE', '');

// ================================================
// BUSINESS HOURS
// ================================================
define('BUSINESS_HOURS_WEEKDAY', 'Monday - Friday: 9:00 AM - 6:00 PM');
define('BUSINESS_HOURS_SATURDAY', 'Saturday: 10:00 AM - 4:00 PM');
define('BUSINESS_HOURS_SUNDAY', 'Sunday: Closed');

// ================================================
// FILE PATHS
// ================================================
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('ASSETS_PATH', __DIR__ . '/../assets/');
define('ASSETS_URL', SITE_URL . '/assets/');

// ================================================
// IMAGES & MEDIA
// ================================================
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('DEFAULT_IMAGE', SITE_URL . '/assets/images/default.jpg');
define('LOGO_URL', SITE_URL . '/assets/images/logo.png');
define('FAVICON_URL', SITE_URL . '/assets/images/favicon.ico');

// ================================================
// PAGINATION & LIMITS
// ================================================
define('POSTS_PER_PAGE', 12);
define('BLOGS_PER_PAGE', 9);
define('CASE_STUDIES_PER_PAGE', 6);
define('TESTIMONIALS_PER_PAGE', 6);
define('RELATED_POSTS_LIMIT', 3);

// ================================================
// SEO SETTINGS
// ================================================
define('META_TITLE_SUFFIX', ' | ' . SITE_NAME);
define('DEFAULT_META_DESCRIPTION', SITE_DESCRIPTION);
define('DEFAULT_META_IMAGE', LOGO_URL);
define('META_ROBOTS', 'index, follow');
define('GOOGLE_ANALYTICS_ID', 'G-XGTB1QBQV3');
define('FACEBOOK_PIXEL_ID', 'WOM');
define('GOOGLE_SITE_VERIFICATION', '');
define('FACEBOOK_APP_ID', '');

// ================================================
// THEME SETTINGS
// ================================================
define('THEME_COLOR', '#000000');
define('THEME_FONT', 'Inter, system-ui, -apple-system, sans-serif');

// ================================================
// EMAIL SETTINGS
// ================================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('MAIL_FROM_ADDRESS', CONTACT_EMAIL);
define('MAIL_FROM_NAME', SITE_NAME);

// ================================================
// SECURITY SETTINGS
// ================================================
define('SESSION_LIFETIME', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900);
define('CSRF_TOKEN_LENGTH', 32);

// ================================================
// DEVELOPMENT SETTINGS
// ================================================
define('APP_ENV', 'production');
define('DEBUG_MODE', false);
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/../logs/error.log');
?>