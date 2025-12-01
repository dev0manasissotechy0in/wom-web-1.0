<?php
require_once 'auth.php';
require_once '../config.php';

$settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM site_settings LIMIT 1"));
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description']);
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    $meta_robots = mysqli_real_escape_string($conn, $_POST['meta_robots']);
    $canonical_url = mysqli_real_escape_string($conn, $_POST['canonical_url']);

    $ga_measurement_id = mysqli_real_escape_string($conn, $_POST['ga_measurement_id']);
    $meta_pixel_id = mysqli_real_escape_string($conn, $_POST['meta_pixel_id']);
    $gtm_container_id = mysqli_real_escape_string($conn, $_POST['gtm_container_id']);

    $custom_head = mysqli_real_escape_string($conn, $_POST['custom_head']);
    $custom_body_top = mysqli_real_escape_string($conn, $_POST['custom_body_top']);
    $custom_body_bottom = mysqli_real_escape_string($conn, $_POST['custom_body_bottom']);

    if ($settings) {
        $id = (int)$settings['id'];
        $sql = "UPDATE site_settings SET 
                meta_title='$meta_title',
                meta_description='$meta_description',
                meta_keywords='$meta_keywords',
                meta_robots='$meta_robots',
                canonical_url='$canonical_url',
                ga_measurement_id='$ga_measurement_id',
                meta_pixel_id='$meta_pixel_id',
                gtm_container_id='$gtm_container_id',
                custom_head='$custom_head',
                custom_body_top='$custom_body_top',
                custom_body_bottom='$custom_body_bottom'
                WHERE id=$id";
    } else {
        $sql = "INSERT INTO site_settings 
                (meta_title, meta_description, meta_keywords, meta_robots, canonical_url,
                 ga_measurement_id, meta_pixel_id, gtm_container_id,
                 custom_head, custom_body_top, custom_body_bottom)
                VALUES (
                 '$meta_title','$meta_description','$meta_keywords','$meta_robots','$canonical_url',
                 '$ga_measurement_id','$meta_pixel_id','$gtm_container_id',
                 '$custom_head','$custom_body_top','$custom_body_bottom')";
    }
    mysqli_query($conn, $sql);
    $message = 'Settings updated successfully';
    $settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM site_settings LIMIT 1"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SEO & Tracking Settings</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="admin-container">
    <h1>SEO & Tracking</h1>

    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" class="admin-form">
        <div class="form-section">
            <h2>Meta & SEO</h2>
            <div class="form-group">
                <label>Meta Title *</label>
                <input type="text" name="meta_title" required
                       value="<?php echo htmlspecialchars($settings['meta_title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Meta Description *</label>
                <textarea name="meta_description" rows="3" required><?php 
                    echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Meta Keywords</label>
                <textarea name="meta_keywords" rows="2"><?php 
                    echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Robots</label>
                    <input type="text" name="meta_robots"
                           value="<?php echo htmlspecialchars($settings['meta_robots'] ?? 'index,follow'); ?>">
                </div>
                <div class="form-group">
                    <label>Canonical URL</label>
                    <input type="url" name="canonical_url"
                           value="<?php echo htmlspecialchars($settings['canonical_url'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>Analytics & Pixels</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Google Analytics Measurement ID (G-XXXX)</label>
                    <input type="text" name="ga_measurement_id"
                           value="<?php echo htmlspecialchars($settings['ga_measurement_id'] ?? ''); ?>">
                    <small>Leave empty if using only Google Tag Manager.</small>
                </div>
                <div class="form-group">
                    <label>Google Tag Manager Container ID (GTM-XXXX)</label>
                    <input type="text" name="gtm_container_id"
                           value="<?php echo htmlspecialchars($settings['gtm_container_id'] ?? ''); ?>">
                    <small>Recommended: use GTM to manage GA, Pixels, Hotjar, etc. [Head + Body snippets] [web:51][web:52][web:55]</small>
                </div>
            </div>
            <div class="form-group">
                <label>Meta (Facebook) Pixel ID</label>
                <input type="text" name="meta_pixel_id"
                       value="<?php echo htmlspecialchars($settings['meta_pixel_id'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-section">
            <h2>Custom Snippets (Advanced)</h2>
            <div class="form-group">
                <label>Custom Head HTML</label>
                <textarea name="custom_head" rows="4" placeholder="Extra meta tags, verification codes, etc."><?php 
                    echo htmlspecialchars($settings['custom_head'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Custom Body Top HTML</label>
                <textarea name="custom_body_top" rows="3" placeholder="Scripts right after <body>"><?php 
                    echo htmlspecialchars($settings['custom_body_top'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Custom Body Bottom HTML</label>
                <textarea name="custom_body_bottom" rows="3" placeholder="Scripts before </body>"><?php 
                    echo htmlspecialchars($settings['custom_body_bottom'] ?? ''); ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn-primary">Save Settings</button>
    </form>
</div>
</body>
</html>
