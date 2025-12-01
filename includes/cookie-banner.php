<!-- PHP-Based Cookie Consent Banner -->
<?php
// Check if consent has been saved in database
$hasConsent = false;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($db) && $db instanceof PDO) {
    try {
        $stmt = $db->prepare("SELECT id FROM cookie_consent WHERE session_id = ?");
        $stmt->execute([session_id()]);
        $hasConsent = (bool)$stmt->fetch();
    } catch (PDOException $e) {
        // Fail silently
    }
}

$showBanner = !$hasConsent ? 'block' : 'none';
?>

<div id="cookieConsentBanner" class="cookie-banner" style="display:<?php echo $showBanner; ?>;">
    <div class="cookie-container">
        <button class="cookie-close" id="cookieClose" onclick="closeCookieBanner()" title="Close">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="cookie-header">
            <i class="fas fa-cookie-bite"></i>
            <h3>Cookie Preferences</h3>
        </div>
        
        <p class="cookie-description">We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. Please choose your cookie preferences:</p>
        
        <div class="cookie-options">
            <label class="cookie-option">
                <input type="checkbox" id="necessary" checked disabled> 
                <span class="cookie-label">
                    <strong>Necessary Cookies</strong>
                    <small>Required for basic site functionality (Always Active)</small>
                </span>
            </label>
            <label class="cookie-option">
                <input type="checkbox" id="functional"> 
                <span class="cookie-label">
                    <strong>Functional Cookies</strong>
                    <small>Enhance your browsing experience with additional features</small>
                </span>
            </label>
            <label class="cookie-option">
                <input type="checkbox" id="analytics"> 
                <span class="cookie-label">
                    <strong>Analytics Cookies</strong>
                    <small>Help us understand how visitors use our site</small>
                </span>
            </label>
            <label class="cookie-option">
                <input type="checkbox" id="marketing"> 
                <span class="cookie-label">
                    <strong>Marketing & Advertising</strong>
                    <small>Used to deliver personalized advertisements</small>
                </span>
            </label>
        </div>
        
        <div class="cookie-actions">
            <button id="acceptAllBtn" class="btn-accept-all" onclick="acceptAllCookies()">Accept All</button>
            <button id="savePreferencesBtn" class="btn-save" onclick="acceptSelectedCookies()">Save Preferences</button>
            <button id="rejectAllBtn" class="btn-reject" onclick="rejectAllCookies()">Reject All</button>
            <a href="/page?slug=cookie-policy" class="btn-learn-more">Learn More</a>
        </div>
    </div>
</div>

<script>
// PHP-BASED COOKIE CONSENT - Server-Side Management
// Uses PHP backend for cookie consent storage (database-driven)

// Close banner without saving (user can choose later)
function closeCookieBanner() {
    const banner = document.getElementById('cookieConsentBanner');
    if (banner) {
        banner.style.display = 'none';
    }
}

// Accept all cookies
function acceptAllCookies() {
    const consent = {
        necessary: true,
        functional: true,
        analytics: true,
        marketing: true
    };
    saveConsent(consent);
}

// Accept selected cookies with proper null checks
function acceptSelectedCookies() {
    const functionalCheckbox = document.getElementById('functional');
    const analyticsCheckbox = document.getElementById('analytics');
    const marketingCheckbox = document.getElementById('marketing');
    
    const consent = {
        necessary: true,
        functional: functionalCheckbox ? functionalCheckbox.checked : false,
        analytics: analyticsCheckbox ? analyticsCheckbox.checked : false,
        marketing: marketingCheckbox ? marketingCheckbox.checked : false
    };
    
    saveConsent(consent);
}

// Reject all non-necessary cookies
function rejectAllCookies() {
    const consent = {
        necessary: true,
        functional: false,
        analytics: false,
        marketing: false
    };
    saveConsent(consent);
}

// Save consent to backend (PHP-based management)
function saveConsent(consent) {
    // Hide banner immediately
    const banner = document.getElementById('cookieConsentBanner');
    if (banner) {
        banner.style.display = 'none';
    }
    
    // Send to backend PHP API for database storage
    fetch('/api/save-cookie-consent.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(consent)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Cookie consent saved successfully:', data);
    })
    .catch(err => console.error('Error saving cookie consent:', err));
}
</script>
