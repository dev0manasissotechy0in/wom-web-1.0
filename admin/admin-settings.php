<?php
/**
 * Admin Settings - Updated for Existing Database Structure
 * Compatible with your current site_settings table
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$admin_id = $_SESSION['admin_id'];
$message = '';
$error = '';

// Get admin data
try {
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching admin: " . $e->getMessage());
    $error = "Database error occurred.";
}

// Get site settings
try {
    $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
    $site_settings = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no settings exist, create default
    if (!$site_settings) {
        $db->exec("INSERT INTO site_settings (id, site_name, site_url, theme_color, created_at) 
                   VALUES (1, 'Wall of Marketing', 'https://wallofmarketing.co', '#0066FF', NOW())");
        $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
        $site_settings = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching settings: " . $e->getMessage());
    $site_settings = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Update Profile
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);

        if (empty($full_name) || empty($email) || empty($username)) {
            $error = 'All fields are required!';
        } else {
            $check = $db->prepare("SELECT id FROM admin_users WHERE (email = ? OR username = ?) AND id != ?");
            $check->execute([$email, $username, $admin_id]);

            if ($check->fetch()) {
                $error = 'Email or username already exists!';
            } else {
                $stmt = $db->prepare("UPDATE admin_users SET full_name = ?, email = ?, username = ? WHERE id = ?");
                if ($stmt->execute([$full_name, $email, $username, $admin_id])) {
                    $_SESSION['admin_name'] = $full_name;
                    $_SESSION['admin_username'] = $username;
                    $message = 'Profile updated successfully!';

                    $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
                    $stmt->execute([$admin_id]);
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        }
    }

    // Change Password
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!password_verify($current_password, $admin['password'])) {
            $error = 'Current password is incorrect!';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match!';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long!';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $admin_id])) {
                $message = 'Password changed successfully!';
            }
        }
    }

    // Update Site Settings
    if ($action === 'update_site_settings') {
        $site_name = trim($_POST['site_name']);
        $site_url = rtrim(trim($_POST['site_url']), '/'); // Remove trailing slash
        $site_logo = trim($_POST['site_logo']);
        $theme_color = trim($_POST['theme_color']);
        $contact_email = trim($_POST['contact_email']);
        $contact_phone = trim($_POST['contact_phone']);
        $address = trim($_POST['address']);

        $stmt = $db->prepare("UPDATE site_settings SET 
            site_name = ?, site_url = ?, site_logo = ?, theme_color = ?,
            contact_email = ?, contact_phone = ?, address = ?
            WHERE id = 1");

        if ($stmt->execute([$site_name, $site_url, $site_logo, $theme_color, 
                            $contact_email, $contact_phone, $address])) {
            $message = 'Site settings updated successfully!';

            $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
            $site_settings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // Update SEO Settings
    if ($action === 'update_seo_settings') {
        $meta_title = trim($_POST['meta_title']);
        $meta_description = trim($_POST['meta_description']);
        $meta_keywords = trim($_POST['meta_keywords']);

        $stmt = $db->prepare("UPDATE site_settings SET 
            meta_title = ?, meta_description = ?, meta_keywords = ?
            WHERE id = 1");

        if ($stmt->execute([$meta_title, $meta_description, $meta_keywords])) {
            $message = 'SEO settings updated successfully!';

            $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
            $site_settings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // Update Tracking Codes
    if ($action === 'update_tracking') {
        $google_analytics_id = trim($_POST['google_analytics_id']);
        $facebook_pixel_id = trim($_POST['facebook_pixel_id']);
        $google_tag_manager_id = trim($_POST['google_tag_manager_id'] ?? '');

        $stmt = $db->prepare("UPDATE site_settings SET 
            google_analytics_id = ?, facebook_pixel_id = ?, google_tag_manager_id = ?
            WHERE id = 1");

        if ($stmt->execute([$google_analytics_id, $facebook_pixel_id, $google_tag_manager_id])) {
            $message = 'Tracking codes updated successfully!';

            $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
            $site_settings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // Update Social Media Links
    if ($action === 'update_social_media') {
        $facebook_url = trim($_POST['facebook_url']);
        $instagram_url = trim($_POST['instagram_url']);
        $linkedin_url = trim($_POST['linkedin_url']);
        $twitter_url = trim($_POST['twitter_url']);
        $youtube_url = trim($_POST['youtube_url'] ?? '');

        $stmt = $db->prepare("UPDATE site_settings SET 
            facebook_url = ?, instagram_url = ?, linkedin_url = ?, twitter_url = ?, youtube_url = ?
            WHERE id = 1");

        if ($stmt->execute([$facebook_url, $instagram_url, $linkedin_url, $twitter_url, $youtube_url])) {
            $message = 'Social media links updated successfully!';

            $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
            $site_settings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

$page_title = 'Settings';
?>
<?php include 'includes/layout-start.php'; ?>

    <div class="page-header">
        <h1>⚙️ Settings</h1>
        <p style="color: #666; margin-top: 5px;">Manage your site configuration and preferences</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">✗ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="settings-tabs">
        <button class="tab-btn active" onclick="switchTab('profile')">👤 Profile</button>
        <button class="tab-btn" onclick="switchTab('site')">🌐 Site Settings</button>
        <button class="tab-btn" onclick="switchTab('seo')">🔍 SEO</button>
        <button class="tab-btn" onclick="switchTab('tracking')">📊 Tracking</button>
        <button class="tab-btn" onclick="switchTab('social')">📱 Social Media</button>
    </div>

    <!-- Profile Tab -->
    <div id="profile-tab" class="tab-content active">
        <div class="settings-card">
            <h2>Profile Information</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($admin['username'] ?? '') ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">💾 Save Profile</button>
            </form>
        </div>

        <div class="settings-card">
            <h2>Change Password</h2>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>Current Password *</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="password" name="new_password" required minlength="6">
                        <small>Minimum 6 characters</small>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password *</label>
                            <input type="password" name="confirm_password" required minlength="6">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">🔐 Change Password</button>
                </form>
            </div>
        </div>

        <!-- Site Settings Tab -->
        <div id="site-tab" class="tab-content">
            <div class="settings-card">
                <h2>General Site Settings</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_site_settings">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Site Name *</label>
                            <input type="text" name="site_name" value="<?= htmlspecialchars($site_settings['site_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Theme Color</label>
                            <input type="color" name="theme_color" value="<?= htmlspecialchars($site_settings['theme_color'] ?? '#0066FF') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Site URL *</label>
                        <input type="url" name="site_url" value="<?= htmlspecialchars($site_settings['site_url'] ?? '') ?>" required>
                        <small>Without trailing slash. Example: https://wallofmarketing.co</small>
                    </div>
                    <div class="form-group">
                        <label>Site Logo Path</label>
                        <input type="text" name="site_logo" value="<?= htmlspecialchars($site_settings['site_logo'] ?? '') ?>">
                        <small>Path to logo file, e.g., /assets/images/logo.png</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($site_settings['contact_email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" name="contact_phone" value="<?= htmlspecialchars($site_settings['contact_phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Business Address</label>
                        <textarea name="address"><?= htmlspecialchars($site_settings['address'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Save Settings</button>
                </form>
            </div>
        </div>

        <!-- SEO Tab -->
        <div id="seo-tab" class="tab-content">
            <div class="settings-card">
                <h2>Default SEO Meta Tags</h2>
                <div class="info-box">
                    ℹ️ These are default meta tags for your site. Individual pages can override these in the seo_meta table.
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_seo_settings">
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title" value="<?= htmlspecialchars($site_settings['meta_title'] ?? '') ?>" maxlength="60">
                        <small>Recommended: 50-60 characters (Currently: <span id="title-count">0</span>)</small>
                    </div>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="meta_description" maxlength="160"><?= htmlspecialchars($site_settings['meta_description'] ?? '') ?></textarea>
                        <small>Recommended: 150-160 characters (Currently: <span id="desc-count">0</span>)</small>
                    </div>
                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="<?= htmlspecialchars($site_settings['meta_keywords'] ?? '') ?>">
                        <small>Comma separated keywords (e.g., digital marketing, SEO, social media)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Save SEO Settings</button>
                </form>
            </div>

            <div class="settings-card">
                <h2>🗺️ Sitemap</h2>
                <div class="info-box">
                    Your dynamic sitemap is available at:<br>
                    <strong><?= htmlspecialchars($site_settings['site_url'] ?? 'https://wallofmarketing.co') ?>/sitemap.php</strong>
                </div>
                <p style="margin-top: 15px; color: #666;">
                    Submit this URL to Google Search Console and Bing Webmaster Tools for better indexing.
                </p>
                <a href="/sitemap.php" target="_blank" class="btn btn-primary" style="display: inline-block; margin-top: 10px; text-decoration: none;">
                    📄 View Sitemap
                </a>
            </div>
        </div>

        <!-- Tracking Tab -->
        <div id="tracking-tab" class="tab-content">
            <div class="settings-card">
                <h2>Tracking & Analytics Codes</h2>
                <div class="info-box">
                    ℹ️ These codes will be automatically loaded on your site. Users must accept cookies to enable tracking.
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_tracking">
                    <div class="form-group">
                        <label>Google Analytics 4 Measurement ID</label>
                        <input type="text" name="google_analytics_id" value="<?= htmlspecialchars($site_settings['google_analytics_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX">
                        <small>Format: G-XXXXXXXXXX (Find this in Google Analytics → Admin → Data Streams)</small>
                    </div>
                    <div class="form-group">
                        <label>Facebook Pixel ID</label>
                        <input type="text" name="facebook_pixel_id" value="<?= htmlspecialchars($site_settings['facebook_pixel_id'] ?? '') ?>" placeholder="123456789012345">
                        <small>Your Facebook Pixel ID (numbers only, 15 digits)</small>
                    </div>
                    <div class="form-group">
                        <label>Google Tag Manager ID (Optional)</label>
                        <input type="text" name="google_tag_manager_id" value="<?= htmlspecialchars($site_settings['google_tag_manager_id'] ?? '') ?>" placeholder="GTM-XXXXXX">
                        <small>Format: GTM-XXXXXX (If using GTM, it will replace individual GA4/FB Pixel codes)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Save Tracking Codes</button>
                </form>
            </div>
        </div>

        <!-- Social Media Tab -->
        <div id="social-tab" class="tab-content">
            <div class="settings-card">
                <h2>Social Media Profile Links</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_social_media">
                    <div class="form-group">
                        <label>📘 Facebook URL</label>
                        <input type="url" name="facebook_url" value="<?= htmlspecialchars($site_settings['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/yourpage">
                    </div>
                    <div class="form-group">
                        <label>📸 Instagram URL</label>
                        <input type="url" name="instagram_url" value="<?= htmlspecialchars($site_settings['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/yourpage">
                    </div>
                    <div class="form-group">
                        <label>💼 LinkedIn URL</label>
                        <input type="url" name="linkedin_url" value="<?= htmlspecialchars($site_settings['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/company/yourpage">
                    </div>
                    <div class="form-group">
                        <label>🐦 Twitter/X URL</label>
                        <input type="url" name="twitter_url" value="<?= htmlspecialchars($site_settings['twitter_url'] ?? '') ?>" placeholder="https://twitter.com/yourpage">
                    </div>
                    <div class="form-group">
                        <label>🎥 YouTube URL (Optional)</label>
                        <input type="url" name="youtube_url" value="<?= htmlspecialchars($site_settings['youtube_url'] ?? '') ?>" placeholder="https://youtube.com/@yourchannel">
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Save Social Links</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        // Character counters
        const titleInput = document.querySelector('input[name="meta_title"]');
        const descInput = document.querySelector('textarea[name="meta_description"]');

        if (titleInput) {
            const titleCount = document.getElementById('title-count');
            titleInput.addEventListener('input', () => {
                titleCount.textContent = titleInput.value.length;
                titleCount.style.color = titleInput.value.length > 60 ? '#d32f2f' : '#2e7d32';
            });
            titleCount.textContent = titleInput.value.length;
        }

        if (descInput) {
            const descCount = document.getElementById('desc-count');
            descInput.addEventListener('input', () => {
                descCount.textContent = descInput.value.length;
                descCount.style.color = descInput.value.length > 160 ? '#d32f2f' : '#2e7d32';
            });
            descCount.textContent = descInput.value.length;
        }
    </script>

<?php include 'includes/layout-end.php'; ?>