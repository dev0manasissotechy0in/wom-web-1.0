<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Settings';

$message = '';
$error = '';

// This page is being replaced by admin-settings.php which has better functionality
// Redirect to admin-settings.php
header('Location: admin-settings.php');
exit();

/**
 * Generate constants.php file from database settings
 */
function generateConstantsFile($db) {
    try {
        $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$settings) {
            return false;
        }
        
        // Get base URL
        $siteUrl = $settings['site_url'] ?? SITE_URL;
        
        $content = <<<PHP
<?php
/**
 * Site Constants & Configuration
 * Auto-generated from database settings
 * Last updated: {date('Y-m-d H:i:s')}
 */

// ================================================
// SITE INFORMATION
// ================================================
define('SITE_NAME', '{$settings['site_name']}');
define('SITE_TAGLINE', 'Transform Your Digital Presence');
define('SITE_URL', '{$siteUrl}');
define('SITE_DESCRIPTION', '{$settings['meta_description']}');
define('SITE_KEYWORDS', '{$settings['meta_keywords']}');

// ================================================
// CONTACT INFORMATION
// ================================================
define('CONTACT_EMAIL', '{$settings['contact_email']}');
define('CONTACT_PHONE', '{$settings['contact_phone']}');
define('CONTACT_ADDRESS', '{$settings['address']}');
define('ADMIN_EMAIL', '{$settings['contact_email']}');

// ================================================
// SOCIAL MEDIA LINKS
// ================================================
define('SOCIAL_FACEBOOK', '{$settings['facebook_url']}');
define('SOCIAL_TWITTER', '{$settings['twitter_url']}');
define('SOCIAL_LINKEDIN', '{$settings['linkedin_url']}');
define('SOCIAL_INSTAGRAM', '{$settings['instagram_url']}');
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
define('LOGO_URL', SITE_URL . '/assets/images/Logo.png');
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
define('GOOGLE_ANALYTICS_ID', '{$settings['google_analytics_id']}');
define('FACEBOOK_PIXEL_ID', '{$settings['facebook_pixel_id']}');
define('GOOGLE_SITE_VERIFICATION', '');
define('FACEBOOK_APP_ID', '');

// ================================================
// THEME SETTINGS
// ================================================
define('THEME_COLOR', '{$settings['theme_color']}');
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
PHP;
        
        return $content;
        
    } catch(PDOException $e) {
        error_log("Error generating constants file: " . $e->getMessage());
        return false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Site Settings</h1>
                <p>Manage your website configuration and constants</p>
            </div>
            
            <?php if(!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="content-card">
                <form method="POST" class="form" style="padding: 30px;">
                    <h3>General Settings</h3>
                    <p style="color: #666; margin-bottom: 20px;">These settings will update both the database and the constants.php file</p>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Theme Color</label>
                            <input type="color" name="theme_color" class="form-control" value="<?php echo htmlspecialchars($settings['theme_color']); ?>">
                        </div>
                    </div>
                    
                    <h3 style="margin-top: 30px;">Contact Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($settings['contact_phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($settings['address']); ?></textarea>
                    </div>
                    
                    <h3 style="margin-top: 30px;">Social Media Links</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fab fa-facebook"></i> Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="<?php echo htmlspecialchars($settings['facebook_url']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fab fa-instagram"></i> Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="<?php echo htmlspecialchars($settings['instagram_url']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fab fa-linkedin"></i> LinkedIn URL</label>
                            <input type="url" name="linkedin_url" class="form-control" value="<?php echo htmlspecialchars($settings['linkedin_url']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fab fa-twitter"></i> Twitter URL</label>
                            <input type="url" name="twitter_url" class="form-control" value="<?php echo htmlspecialchars($settings['twitter_url']); ?>">
                        </div>
                    </div>
                    
                    <h3 style="margin-top: 30px;">SEO Settings</h3>
                    
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($settings['meta_title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3" required><?php echo htmlspecialchars($settings['meta_description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($settings['meta_keywords']); ?>">
                    </div>
                    
                    <h3 style="margin-top: 30px;">Analytics & Tracking</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Google Analytics ID</label>
                            <input type="text" name="google_analytics_id" class="form-control" value="<?php echo htmlspecialchars($settings['google_analytics_id']); ?>" placeholder="G-XXXXXXXXXX">
                        </div>
                        
                        <div class="form-group">
                            <label>Facebook Pixel ID</label>
                            <input type="text" name="facebook_pixel_id" class="form-control" value="<?php echo htmlspecialchars($settings['facebook_pixel_id']); ?>" placeholder="123456789012345">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin-top: 30px;">
                        <i class="fas fa-save"></i> Update Settings & Regenerate Constants
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
