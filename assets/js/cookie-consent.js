// Cookie Consent Management
(function() {
    'use strict';
    
    const COOKIE_NAME = 'wom_cookie_consent';
    const COOKIE_EXPIRY_DAYS = 365;
    
    // Check if consent already given
    function hasConsent() {
        return getCookie(COOKIE_NAME) !== null;
    }
    
    // Get cookie value
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
    
    // Set cookie
    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/;SameSite=Lax`;
    }
    
    // Create consent banner
    function createConsentBanner() {
        const banner = document.createElement('div');
        banner.id = 'cookie-consent-banner';
        banner.innerHTML = `
            <div class="cookie-consent-content">
                <p>We use cookies to enhance your browsing experience and analyze our traffic. By clicking "Accept", you consent to our use of cookies.</p>
                <div class="cookie-consent-buttons">
                    <button id="cookie-accept" class="btn-accept">Accept All</button>
                    <button id="cookie-reject" class="btn-reject">Reject</button>
                    <a href="/cookie-policy.php" class="btn-link">Learn More</a>
                </div>
            </div>
        `;
        
        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            #cookie-consent-banner {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(0, 0, 0, 0.95);
                color: white;
                padding: 20px;
                z-index: 99999;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
                animation: slideUp 0.3s ease-out;
            }
            
            @keyframes slideUp {
                from { transform: translateY(100%); }
                to { transform: translateY(0); }
            }
            
            .cookie-consent-content {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .cookie-consent-content p {
                margin: 0;
                flex: 1;
                min-width: 200px;
            }
            
            .cookie-consent-buttons {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .cookie-consent-buttons button,
            .cookie-consent-buttons a {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                text-decoration: none;
                transition: all 0.3s;
            }
            
            .btn-accept {
                background: #0066FF;
                color: white;
            }
            
            .btn-accept:hover {
                background: #0052cc;
            }
            
            .btn-reject {
                background: #666;
                color: white;
            }
            
            .btn-reject:hover {
                background: #555;
            }
            
            .btn-link {
                background: transparent;
                color: white;
                text-decoration: underline;
            }
            
            @media (max-width: 768px) {
                .cookie-consent-content {
                    flex-direction: column;
                    text-align: center;
                }
                
                .cookie-consent-buttons {
                    width: 100%;
                    justify-content: center;
                }
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(banner);
        
        // Event listeners
        document.getElementById('cookie-accept').addEventListener('click', function() {
            setCookie(COOKIE_NAME, 'accepted', COOKIE_EXPIRY_DAYS);
            removeBanner();
            loadAnalytics();
        });
        
        document.getElementById('cookie-reject').addEventListener('click', function() {
            setCookie(COOKIE_NAME, 'rejected', COOKIE_EXPIRY_DAYS);
            removeBanner();
        });
    }
    
    // Remove banner
    function removeBanner() {
        const banner = document.getElementById('cookie-consent-banner');
        if (banner) {
            banner.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => banner.remove(), 300);
        }
    }
    
    // Load analytics if consent given
    function loadAnalytics() {
        const consent = getCookie(COOKIE_NAME);
        if (consent === 'accepted') {
            // Load Google Analytics or other tracking scripts here
            console.log('Analytics loaded');
        }
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        if (!hasConsent()) {
            createConsentBanner();
        } else {
            loadAnalytics();
        }
    });
    
})();
