<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Site Settings';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $site_name = trim($_POST['site_name'] ?? '');
        $site_url = trim($_POST['site_url'] ?? '');
        $site_logo = trim($_POST['site_logo'] ?? '');
        $theme_color = trim($_POST['theme_color'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        $facebook_url = trim($_POST['facebook_url'] ?? '');
        $instagram_url = trim($_POST['instagram_url'] ?? '');
        $linkedin_url = trim($_POST['linkedin_url'] ?? '');
        $twitter_url = trim($_POST['twitter_url'] ?? '');
        $youtube_url = trim($_POST['youtube_url'] ?? '');
        
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords = trim($_POST['meta_keywords'] ?? '');
        
        $google_analytics_id = trim($_POST['google_analytics_id'] ?? '');
        $facebook_pixel_id = trim($_POST['facebook_pixel_id'] ?? '');
        $google_tag_manager_id = trim($_POST['google_tag_manager_id'] ?? '');
        
        $dark_mode_enabled = isset($_POST['dark_mode_enabled']) ? 1 : 0;
        $footer_legal_links_enabled = isset($_POST['footer_legal_links_enabled']) ? 1 : 0;
        $resource_download_notify_admin = isset($_POST['resource_download_notify_admin']) ? 1 : 0;
        $newsletter_auto_send_welcome = isset($_POST['newsletter_auto_send_welcome']) ? 1 : 0;
        
        // Check if settings exist
        $checkStmt = $db->query("SELECT COUNT(*) as count FROM site_settings");
        $count = $checkStmt->fetch()['count'];
        
        if ($count > 0) {
            // Update existing settings
            $stmt = $db->prepare("UPDATE site_settings SET 
                site_name = ?, site_url = ?, site_logo = ?, theme_color = ?,
                contact_email = ?, contact_phone = ?, address = ?,
                facebook_url = ?, instagram_url = ?, linkedin_url = ?, twitter_url = ?, youtube_url = ?,
                meta_title = ?, meta_description = ?, meta_keywords = ?,
                google_analytics_id = ?, facebook_pixel_id = ?, google_tag_manager_id = ?,
                dark_mode_enabled = ?, footer_legal_links_enabled = ?, 
                resource_download_notify_admin = ?, newsletter_auto_send_welcome = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = 1");
            
            $stmt->execute([
                $site_name, $site_url, $site_logo, $theme_color,
                $contact_email, $contact_phone, $address,
                $facebook_url, $instagram_url, $linkedin_url, $twitter_url, $youtube_url,
                $meta_title, $meta_description, $meta_keywords,
                $google_analytics_id, $facebook_pixel_id, $google_tag_manager_id,
                $dark_mode_enabled, $footer_legal_links_enabled,
                $resource_download_notify_admin, $newsletter_auto_send_welcome
            ]);
        } else {
            // Insert new settings
            $stmt = $db->prepare("INSERT INTO site_settings (
                site_name, site_url, site_logo, theme_color,
                contact_email, contact_phone, address,
                facebook_url, instagram_url, linkedin_url, twitter_url, youtube_url,
                meta_title, meta_description, meta_keywords,
                google_analytics_id, facebook_pixel_id, google_tag_manager_id,
                dark_mode_enabled, footer_legal_links_enabled,
                resource_download_notify_admin, newsletter_auto_send_welcome
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $site_name, $site_url, $site_logo, $theme_color,
                $contact_email, $contact_phone, $address,
                $facebook_url, $instagram_url, $linkedin_url, $twitter_url, $youtube_url,
                $meta_title, $meta_description, $meta_keywords,
                $google_analytics_id, $facebook_pixel_id, $google_tag_manager_id,
                $dark_mode_enabled, $footer_legal_links_enabled,
                $resource_download_notify_admin, $newsletter_auto_send_welcome
            ]);
        }
        
        $success = "Settings saved successfully!";
        
    } catch(PDOException $e) {
        error_log("Error saving site settings: " . $e->getMessage());
        $error = "Error saving settings: " . $e->getMessage();
    }
}

// Fetch current settings
try {
    $stmt = $db->query("SELECT * FROM site_settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        // Set defaults if no settings exist
        $settings = [
            'site_name' => 'Digital Marketing Pro',
            'site_url' => 'https://wallofmarketing.co',
            'site_logo' => '/assets/images/logo.png',
            'theme_color' => '#0066FF',
            'contact_email' => '',
            'contact_phone' => '',
            'address' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'twitter_url' => '',
            'youtube_url' => '',
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'google_analytics_id' => '',
            'facebook_pixel_id' => '',
            'google_tag_manager_id' => '',
            'dark_mode_enabled' => 1,
            'footer_legal_links_enabled' => 1,
            'resource_download_notify_admin' => 1,
            'newsletter_auto_send_welcome' => 1
        ];
    }
    
} catch(PDOException $e) {
    error_log("Error fetching site settings: " . $e->getMessage());
    $error = "Error loading settings";
    $settings = [];
}
?>

<?php include 'includes/layout-start.php'; ?>

<style>
    .settings-form {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .settings-section {
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .settings-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .settings-section h3 {
        margin-bottom: 20px;
        color: #000;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .settings-section h3 i {
        color: #666;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #000;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="url"],
    .form-group input[type="color"],
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1.5px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s;
    }
    
    .form-group input[type="text"]:focus,
    .form-group input[type="email"]:focus,
    .form-group input[type="url"]:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #000;
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .form-group small {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 13px;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 15px;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .checkbox-group label {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
        flex: 1;
    }
    
    .checkbox-group small {
        color: #666;
        font-size: 13px;
    }
    
    .btn-save {
        background: #000;
        color: white;
        padding: 14px 40px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-save:hover {
        background: #333;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .color-preview {
        display: inline-block;
        width: 40px;
        height: 40px;
        border-radius: 6px;
        border: 2px solid #ddd;
        vertical-align: middle;
        margin-left: 10px;
    }
</style>

<div class="page-header">
    <h1><i class="fas fa-cog"></i> Site Settings</h1>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<form method="POST" class="settings-form">
    <!-- General Settings -->
    <div class="settings-section">
        <h3><i class="fas fa-info-circle"></i> General Settings</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Site Name</label>
                <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Site URL</label>
                <input type="url" name="site_url" value="<?php echo htmlspecialchars($settings['site_url'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Site Logo Path</label>
                <input type="text" name="site_logo" value="<?php echo htmlspecialchars($settings['site_logo'] ?? ''); ?>">
                <small>Example: /assets/images/logo.png</small>
            </div>
            <div class="form-group">
                <label>Theme Color</label>
                <input type="color" name="theme_color" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#0066FF'); ?>">
                <span class="color-preview" style="background: <?php echo htmlspecialchars($settings['theme_color'] ?? '#0066FF'); ?>"></span>
            </div>
        </div>
    </div>
    
    <!-- Contact Information -->
    <div class="settings-section">
        <h3><i class="fas fa-address-book"></i> Contact Information</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Contact Email</label>
                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Contact Phone</label>
                <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
        </div>
    </div>
    
    <!-- Social Media -->
    <div class="settings-section">
        <h3><i class="fas fa-share-alt"></i> Social Media Links</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Facebook URL</label>
                <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Instagram URL</label>
                <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>LinkedIn URL</label>
                <input type="url" name="linkedin_url" value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Twitter URL</label>
                <input type="url" name="twitter_url" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>YouTube URL</label>
            <input type="url" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>">
        </div>
    </div>
    
    <!-- SEO Settings -->
    <div class="settings-section">
        <h3><i class="fas fa-search"></i> SEO Settings</h3>
        <div class="form-group">
            <label>Meta Title</label>
            <input type="text" name="meta_title" value="<?php echo htmlspecialchars($settings['meta_title'] ?? ''); ?>">
            <small>Default title for pages without specific meta title</small>
        </div>
        <div class="form-group">
            <label>Meta Description</label>
            <textarea name="meta_description"><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
            <small>Default description for pages without specific meta description</small>
        </div>
        <div class="form-group">
            <label>Meta Keywords</label>
            <input type="text" name="meta_keywords" value="<?php echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?>">
            <small>Comma-separated keywords</small>
        </div>
    </div>
    
    <!-- Analytics & Tracking -->
    <div class="settings-section">
        <h3><i class="fas fa-chart-line"></i> Analytics & Tracking</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Google Analytics ID</label>
                <input type="text" name="google_analytics_id" value="<?php echo htmlspecialchars($settings['google_analytics_id'] ?? ''); ?>">
                <small>Example: G-XXXXXXXXXX or UA-XXXXXXXXX-X</small>
            </div>
            <div class="form-group">
                <label>Facebook Pixel ID</label>
                <input type="text" name="facebook_pixel_id" value="<?php echo htmlspecialchars($settings['facebook_pixel_id'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Google Tag Manager ID</label>
            <input type="text" name="google_tag_manager_id" value="<?php echo htmlspecialchars($settings['google_tag_manager_id'] ?? ''); ?>">
            <small>Example: GTM-XXXXXXX</small>
        </div>
    </div>
    
    <!-- Feature Toggles -->
    <div class="settings-section">
        <h3><i class="fas fa-toggle-on"></i> Feature Settings</h3>
        
        <div class="checkbox-group">
            <input type="checkbox" name="dark_mode_enabled" id="dark_mode_enabled" 
                   <?php echo (!empty($settings['dark_mode_enabled'])) ? 'checked' : ''; ?>>
            <label for="dark_mode_enabled">
                Enable Dark Mode
                <small>Allow users to switch between light and dark themes</small>
            </label>
        </div>
        
        <div class="checkbox-group">
            <input type="checkbox" name="footer_legal_links_enabled" id="footer_legal_links_enabled" 
                   <?php echo (!empty($settings['footer_legal_links_enabled'])) ? 'checked' : ''; ?>>
            <label for="footer_legal_links_enabled">
                Show Legal Links in Footer
                <small>Display Privacy Policy, Terms & Conditions, etc. in footer</small>
            </label>
        </div>
        
        <div class="checkbox-group">
            <input type="checkbox" name="resource_download_notify_admin" id="resource_download_notify_admin" 
                   <?php echo (!empty($settings['resource_download_notify_admin'])) ? 'checked' : ''; ?>>
            <label for="resource_download_notify_admin">
                Email Notifications for Resource Downloads
                <small>Send email to admin when someone downloads a resource</small>
            </label>
        </div>
        
        <div class="checkbox-group">
            <input type="checkbox" name="newsletter_auto_send_welcome" id="newsletter_auto_send_welcome" 
                   <?php echo (!empty($settings['newsletter_auto_send_welcome'])) ? 'checked' : ''; ?>>
            <label for="newsletter_auto_send_welcome">
                Auto-Send Welcome Email to Newsletter Subscribers
                <small>Automatically send welcome email when someone subscribes</small>
            </label>
        </div>
    </div>
    
    <button type="submit" class="btn-save">
        <i class="fas fa-save"></i> Save Settings
    </button>
</form>

<?php include 'includes/layout-end.php'; ?>
