# Site Settings Implementation Guide

## Overview
The site settings system allows you to manage all site configuration from the database through an admin interface.

## Files Created

### 1. Admin Interface
- **`admin/site-settings.php`** - Admin page to manage all site settings
- Added "Site Settings" link to admin sidebar

### 2. Settings Class
- **`classes/Settings.php`** - Helper class for easy access to settings throughout the application

### 3. Database Changes
- **Altered `site_settings` table** - Added 4 new columns:
  - `dark_mode_enabled` (TINYINT)
  - `footer_legal_links_enabled` (TINYINT)
  - `resource_download_notify_admin` (TINYINT)
  - `newsletter_auto_send_welcome` (TINYINT)

### 4. Page Router
- **`page.php`** - Dynamic page router for displaying pages from database

### 5. Updated Files
- **`config/config.php`** - Loads Settings class automatically
- **`includes/footer.php`** - Uses settings to conditionally show legal links from database

## How to Use Settings in Your Code

### Basic Usage

```php
// Settings are automatically loaded in config.php
// Access via global $siteSettings variable

// Get a specific setting
$siteName = $siteSettings->get('site_name');
$contactEmail = $siteSettings->get('contact_email');

// Get with default value if not set
$phone = $siteSettings->get('contact_phone', '+91 1234567890');

// Check if boolean setting is enabled
if ($siteSettings->isEnabled('dark_mode_enabled')) {
    // Dark mode is enabled
}
```

### Helper Methods

```php
// Site Information
$siteSettings->getSiteName();           // Get site name
$siteSettings->getSiteUrl();            // Get site URL
$siteSettings->getContactEmail();       // Get contact email

// Feature Checks
$siteSettings->isDarkModeEnabled();     // Check if dark mode enabled
$siteSettings->showFooterLegalLinks();  // Check if footer links should show
$siteSettings->notifyAdminOnDownload(); // Check if admin notifications enabled
$siteSettings->autoSendWelcomeEmail();  // Check if welcome email enabled

// Get Multiple Settings
$socialLinks = $siteSettings->getSocialLinks();
// Returns: ['facebook' => '...', 'instagram' => '...', etc.]

$trackingIds = $siteSettings->getTrackingIds();
// Returns: ['google_analytics' => '...', 'facebook_pixel' => '...', etc.]

// Get All Settings
$allSettings = $siteSettings->getAll();
```

### Example Usage in Code

#### 1. Conditional Features
```php
// In process-download.php
if ($siteSettings->notifyAdminOnDownload()) {
    // Send email notification to admin
    $adminEmail = $siteSettings->getContactEmail();
    // ... send email code ...
}
```

#### 2. Newsletter Welcome Email
```php
// In Newsletter.php subscribe() method
if ($siteSettings->autoSendWelcomeEmail()) {
    $this->sendWelcomeEmail($email, $name);
}
```

#### 3. Dark Mode Toggle
```php
// In header.php
<?php if ($siteSettings->isDarkModeEnabled()): ?>
    <button id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </button>
<?php endif; ?>
```

#### 4. Footer Legal Links
```php
// Already implemented in footer.php
<?php if ($siteSettings->showFooterLegalLinks()): ?>
    <div class="legal-links">
        <!-- Show legal pages -->
    </div>
<?php endif; ?>
```

#### 5. Tracking Scripts
```php
// In header.php or tracking.php
$tracking = $siteSettings->getTrackingIds();

if (!empty($tracking['google_analytics'])) {
    echo "<script async src='https://www.googletagmanager.com/gtag/js?id={$tracking['google_analytics']}'></script>";
}

if (!empty($tracking['facebook_pixel'])) {
    echo "<script>fbq('init', '{$tracking['facebook_pixel']}');</script>";
}
```

## Admin Panel Usage

1. **Access Admin Panel**: `http://localhost/admin/site-settings.php`

2. **Settings Sections**:
   - General Settings (site name, URL, logo, theme color)
   - Contact Information (email, phone, address)
   - Social Media Links (Facebook, Instagram, LinkedIn, Twitter, YouTube)
   - SEO Settings (meta title, description, keywords)
   - Analytics & Tracking (Google Analytics, Facebook Pixel, GTM)
   - Feature Toggles (dark mode, footer links, notifications, welcome emails)

3. **Save Settings**: Click "Save Settings" button at bottom

## Database Structure

```sql
site_settings table columns:
- id
- site_name
- site_url
- site_logo
- theme_color
- contact_email
- contact_phone
- address
- facebook_url
- instagram_url
- linkedin_url
- twitter_url
- youtube_url
- meta_title
- meta_description
- meta_keywords
- google_analytics_id
- facebook_pixel_id
- google_tag_manager_id
- created_at
- updated_at
- dark_mode_enabled (NEW)
- footer_legal_links_enabled (NEW)
- resource_download_notify_admin (NEW)
- newsletter_auto_send_welcome (NEW)
```

## Testing

1. **Test Settings Page**:
   - Visit: `http://localhost/admin/site-settings.php`
   - Update some settings
   - Click "Save Settings"
   - Verify success message

2. **Test Settings Usage**:
   ```php
   // Create test file: test-settings.php
   <?php
   require_once 'config/config.php';
   
   echo "Site Name: " . $siteSettings->getSiteName() . "\n";
   echo "Dark Mode: " . ($siteSettings->isDarkModeEnabled() ? 'Enabled' : 'Disabled') . "\n";
   echo "Footer Links: " . ($siteSettings->showFooterLegalLinks() ? 'Visible' : 'Hidden') . "\n";
   
   print_r($siteSettings->getSocialLinks());
   ?>
   ```

3. **Test Footer Links**:
   - Visit any page
   - Check if Legal section appears in footer
   - Toggle `footer_legal_links_enabled` in admin
   - Verify section shows/hides accordingly

## Dynamic Pages

Pages created in admin panel are accessible via:
- URL format: `http://localhost/page.php?slug=privacy-policy`
- Footer links automatically generated from `pages` table
- Only published pages with `show_in_footer = 1` appear in footer

## Next Steps

To complete the implementation:

1. ✅ Site settings admin interface - DONE
2. ✅ Settings class helper - DONE
3. ✅ Dynamic footer legal links - DONE
4. ⏳ Implement dark mode CSS and JavaScript
5. ⏳ Update Newsletter class to use settings
6. ⏳ Update download process to use notification settings
7. ⏳ Add tracking scripts to header based on settings

## Benefits

- ✅ Centralized configuration management
- ✅ No need to edit code to change settings
- ✅ Easy to add new settings
- ✅ Settings cached in memory for performance
- ✅ Type-safe boolean checks
- ✅ Default values for missing settings
- ✅ Admin-friendly interface
