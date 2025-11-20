<!-- Cookie Consent Banner -->
<div id="cookieConsentBanner" class="cookie-banner" style="display:none;">
    <div class="cookie-content">
        <h3>Cookie Preferences</h3>
        <p>We use cookies to enhance your browsing experience and analyze site traffic.</p>
        
        <div class="cookie-categories">
            <label>
                <input type="checkbox" id="necessary" checked disabled> 
                Necessary (Always Active)
            </label>
            <label>
                <input type="checkbox" id="functional"> 
                Functional
            </label>
            <label>
                <input type="checkbox" id="analytics"> 
                Analytics
            </label>
            <label>
                <input type="checkbox" id="marketing"> 
                Marketing & Advertising
            </label>
        </div>
        
        <div class="cookie-buttons">
            <button onclick="acceptAllCookies()">Accept All</button>
            <button onclick="acceptSelectedCookies()">Save Preferences</button>
            <button onclick="rejectAllCookies()">Reject All</button>
        </div>
    </div>
</div>

<script>
function showCookieBanner() {
    if (!localStorage.getItem('cookieConsent')) {
        document.getElementById('cookieConsentBanner').style.display = 'block';
    }
}

function acceptAllCookies() {
    saveConsent({necessary: true, functional: true, analytics: true, marketing: true});
}

function acceptSelectedCookies() {
    saveConsent({
        necessary: true,
        functional: document.getElementById('functional').checked,
        analytics: document.getElementById('analytics').checked,
        marketing: document.getElementById('marketing').checked
    });
}

function rejectAllCookies() {
    saveConsent({necessary: true, functional: false, analytics: false, marketing: false});
}

function saveConsent(consent) {
    localStorage.setItem('cookieConsent', JSON.stringify(consent));
    document.getElementById('cookieConsentBanner').style.display = 'none';
    
    // Send to backend
    fetch('/api/save-consent.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(consent)
    });
    
    // Reload to apply tracking scripts
    location.reload();
}

window.addEventListener('load', showCookieBanner);
</script>
