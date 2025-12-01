<?php
require_once 'config/config.php';

echo "=== Testing Site Settings System ===\n\n";

echo "1. Settings Class Loaded: " . (isset($siteSettings) ? "✅ YES" : "❌ NO") . "\n\n";

if (isset($siteSettings)) {
    echo "2. Site Information:\n";
    echo "   - Site Name: " . $siteSettings->getSiteName() . "\n";
    echo "   - Site URL: " . $siteSettings->getSiteUrl() . "\n";
    echo "   - Contact Email: " . $siteSettings->getContactEmail() . "\n\n";
    
    echo "3. Feature Toggles:\n";
    echo "   - Dark Mode: " . ($siteSettings->isDarkModeEnabled() ? "✅ Enabled" : "❌ Disabled") . "\n";
    echo "   - Footer Legal Links: " . ($siteSettings->showFooterLegalLinks() ? "✅ Visible" : "❌ Hidden") . "\n";
    echo "   - Download Notifications: " . ($siteSettings->notifyAdminOnDownload() ? "✅ Enabled" : "❌ Disabled") . "\n";
    echo "   - Welcome Emails: " . ($siteSettings->autoSendWelcomeEmail() ? "✅ Enabled" : "❌ Disabled") . "\n\n";
    
    echo "4. Social Media Links:\n";
    $social = $siteSettings->getSocialLinks();
    foreach ($social as $platform => $url) {
        $status = !empty($url) ? "✅ " . $url : "❌ Not set";
        echo "   - " . ucfirst($platform) . ": $status\n";
    }
    
    echo "\n5. Tracking IDs:\n";
    $tracking = $siteSettings->getTrackingIds();
    foreach ($tracking as $service => $id) {
        $status = !empty($id) ? "✅ " . $id : "❌ Not set";
        echo "   - " . str_replace('_', ' ', ucwords($service, '_')) . ": $status\n";
    }
    
    echo "\n6. Test Getting Specific Setting:\n";
    echo "   - Theme Color: " . $siteSettings->get('theme_color', '#0066FF') . "\n";
    echo "   - Contact Phone: " . ($siteSettings->get('contact_phone') ?: 'Not set') . "\n";
    
    echo "\n✅ All tests completed successfully!\n";
    echo "\nYou can now:\n";
    echo "- Visit: http://localhost/admin/site-settings.php to manage settings\n";
    echo "- Use \$siteSettings in any PHP file after including config.php\n";
    echo "- Check footer.php to see dynamic legal links in action\n";
}
?>
