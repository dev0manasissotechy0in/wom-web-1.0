<?php
/**
 * Settings Helper Class
 * Provides easy access to site settings stored in database
 */
class Settings {
    private static $instance = null;
    private $settings = [];
    private $db;
    
    private function __construct($db) {
        $this->db = $db;
        $this->loadSettings();
    }
    
    public static function getInstance($db) {
        if (self::$instance === null) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }
    
    private function loadSettings() {
        try {
            $stmt = $this->db->query("SELECT * FROM site_settings LIMIT 1");
            $this->settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$this->settings) {
                // Set defaults if no settings exist
                $this->settings = $this->getDefaults();
            }
        } catch (PDOException $e) {
            error_log("Error loading site settings: " . $e->getMessage());
            $this->settings = $this->getDefaults();
        }
    }
    
    private function getDefaults() {
        return [
            'site_name' => 'Digital Marketing Pro',
            'site_url' => 'https://wallofmarketing.co',
            'site_logo' => '/assets/images/logo.png',
            'theme_color' => '#0066FF',
            'contact_email' => 'info@wallofmarketing.co',
            'contact_phone' => '',
            'address' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'twitter_url' => '',
            'youtube_url' => '',
            'meta_title' => 'Wall of Marketing - Digital Marketing Solutions',
            'meta_description' => 'Professional digital marketing services and resources',
            'meta_keywords' => 'digital marketing, SEO, social media marketing',
            'google_analytics_id' => '',
            'facebook_pixel_id' => '',
            'google_tag_manager_id' => '',
            'dark_mode_enabled' => 1,
            'footer_legal_links_enabled' => 1,
            'resource_download_notify_admin' => 1,
            'newsletter_auto_send_welcome' => 1
        ];
    }
    
    /**
     * Get a specific setting value
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }
    
    /**
     * Get all settings
     * @return array
     */
    public function getAll() {
        return $this->settings;
    }
    
    /**
     * Check if a boolean setting is enabled
     * @param string $key Setting key
     * @return bool
     */
    public function isEnabled($key) {
        return !empty($this->settings[$key]);
    }
    
    /**
     * Reload settings from database (useful after updates)
     */
    public function reload() {
        $this->loadSettings();
    }
    
    /**
     * Get site name
     */
    public function getSiteName() {
        return $this->get('site_name', 'Wall of Marketing');
    }
    
    /**
     * Get site URL
     */
    public function getSiteUrl() {
        return $this->get('site_url', 'https://wallofmarketing.co');
    }
    
    /**
     * Get contact email
     */
    public function getContactEmail() {
        return $this->get('contact_email', 'info@wallofmarketing.co');
    }
    
    /**
     * Check if dark mode is enabled
     */
    public function isDarkModeEnabled() {
        return $this->isEnabled('dark_mode_enabled');
    }
    
    /**
     * Check if footer legal links should be displayed
     */
    public function showFooterLegalLinks() {
        return $this->isEnabled('footer_legal_links_enabled');
    }
    
    /**
     * Check if admin should be notified on resource downloads
     */
    public function notifyAdminOnDownload() {
        return $this->isEnabled('resource_download_notify_admin');
    }
    
    /**
     * Check if welcome email should be sent to new subscribers
     */
    public function autoSendWelcomeEmail() {
        return $this->isEnabled('newsletter_auto_send_welcome');
    }
    
    /**
     * Get social media links as an array
     */
    public function getSocialLinks() {
        return [
            'facebook' => $this->get('facebook_url'),
            'instagram' => $this->get('instagram_url'),
            'linkedin' => $this->get('linkedin_url'),
            'twitter' => $this->get('twitter_url'),
            'youtube' => $this->get('youtube_url')
        ];
    }
    
    /**
     * Get tracking IDs as an array
     */
    public function getTrackingIds() {
        return [
            'google_analytics' => $this->get('google_analytics_id'),
            'facebook_pixel' => $this->get('facebook_pixel_id'),
            'google_tag_manager' => $this->get('google_tag_manager_id')
        ];
    }
}
?>
