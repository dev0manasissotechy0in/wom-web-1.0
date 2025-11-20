// Main JavaScript file for Wall of Marketing - FIXED
// Prevents null reference errors with proper checks

document.addEventListener('DOMContentLoaded', function() {
    console.log('Wall of Marketing - Main JS Loaded');

    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    if (anchorLinks.length > 0) {
        anchorLinks.forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href && href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }

    // Mobile menu toggle (if exists) - FIXED: Added null checks
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }

    // Cookie consent buttons - FIXED: Proper error handling
    const acceptAllBtn = document.getElementById('acceptAllCookies');
    const rejectAllBtn = document.getElementById('rejectAllCookies');
    const savePreferencesBtn = document.getElementById('savePreferences');

    if (acceptAllBtn) {
        acceptAllBtn.addEventListener('click', function() {
            console.log('Accept all cookies clicked');
            acceptAllCookies();
        });
    }

    if (rejectAllBtn) {
        rejectAllBtn.addEventListener('click', function() {
            console.log('Reject all cookies clicked');
            rejectAllCookies();
        });
    }

    if (savePreferencesBtn) {
        savePreferencesBtn.addEventListener('click', function() {
            console.log('Save preferences clicked');
            savePreferences();
        });
    }
});

// Cookie consent functions
function acceptAllCookies() {
    const consent = {
        necessary: true,
        analytics: true,
        marketing: true
    };
    saveCookieConsent(consent);
    hideCookieBanner();
    // Reload to activate tracking
    setTimeout(() => location.reload(), 500);
}

function rejectAllCookies() {
    const consent = {
        necessary: true,
        analytics: false,
        marketing: false
    };
    saveCookieConsent(consent);
    hideCookieBanner();
}

function savePreferences() {
    const necessaryCheck = document.getElementById('necessary');
    const analyticsCheck = document.getElementById('analytics');
    const marketingCheck = document.getElementById('marketing');

    const consent = {
        necessary: true, // Always true
        analytics: analyticsCheck ? analyticsCheck.checked : false,
        marketing: marketingCheck ? marketingCheck.checked : false
    };

    saveCookieConsent(consent);
    hideCookieBanner();

    // Reload if analytics was enabled
    if (consent.analytics) {
        setTimeout(() => location.reload(), 500);
    }
}

function saveCookieConsent(consent) {
    // Save to cookie
    const consentValue = consent.analytics && consent.marketing ? 'accepted' : 'partial';
    document.cookie = `wom_cookie_consent=${consentValue}; max-age=31536000; path=/; SameSite=Lax`;

    // Also save detailed preferences
    document.cookie = `cookie_preferences=${JSON.stringify(consent)}; max-age=31536000; path=/; SameSite=Lax`;

    // Save to database via API (optional - won't break if fails)
    fetch('/api/save-cookie-consent.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(consent)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Cookie consent saved to database:', data);
    })
    .catch(error => {
        console.log('Cookie consent saved locally (DB save failed):', error);
        // Not a critical error - consent is already saved in cookie
    });
}

function hideCookieBanner() {
    const banner = document.getElementById('cookieConsentBanner');
    if (banner) {
        banner.style.display = 'none';
        // Also add a class for CSS transitions
        banner.classList.add('hidden');
    }
}

// Form validation helper
window.validateForm = function(formElement) {
    if (!formElement) return false;

    const inputs = formElement.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });

    return isValid;
};

// Add loading state to buttons
window.setButtonLoading = function(button, loading) {
    if (!button) return;

    if (loading) {
        button.dataset.originalText = button.textContent;
        button.textContent = 'Processing...';
        button.disabled = true;
        button.classList.add('loading');
    } else {
        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
        button.classList.remove('loading');
    }
};

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    // Don't show errors to users in production
});

// Global promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    // Prevent unhandled rejection from breaking the page
    e.preventDefault();
});