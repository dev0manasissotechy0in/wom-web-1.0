<?php
/**
 * Tracking Scripts Manager - Updated for Existing Database
 * Loads tracking codes from your site_settings table
 */

// Get consent from cookie
$consent = [
    'necessary' => true,
    'analytics' => false,
    'marketing' => false
];

if (isset($_COOKIE['wom_cookie_consent'])) {
    $consent_value = $_COOKIE['wom_cookie_consent'];
    if ($consent_value === 'accepted') {
        $consent['analytics'] = true;
        $consent['marketing'] = true;
    }
}

// Get tracking IDs from database
$ga_id = '';
$fb_pixel_id = '';
$gtm_id = '';

try {
    if (isset($db) && $db instanceof PDO) {
        $stmt = $db->query("SELECT google_analytics_id, facebook_pixel_id, google_tag_manager_id 
                           FROM site_settings WHERE id = 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($settings) {
            $ga_id = $settings['google_analytics_id'] ?? '';
            $fb_pixel_id = $settings['facebook_pixel_id'] ?? '';
            $gtm_id = $settings['google_tag_manager_id'] ?? '';
        }
    }
} catch(PDOException $e) {
    error_log("Tracking scripts error: " . $e->getMessage());
}

$scripts = [];

// Google Tag Manager (Head) - Only if analytics consent given
if ($consent['analytics'] && !empty($gtm_id) && $gtm_id !== 'GTM-XXXXXX') {
    $scripts[] = <<<HTML
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$gtm_id}');</script>
<!-- End Google Tag Manager -->
HTML;
}

// Google Analytics 4 - Only if analytics consent given and no GTM
if ($consent['analytics'] && !empty($ga_id) && $ga_id !== 'G-XXXXXXXXXX' && empty($gtm_id)) {
    $scripts[] = <<<HTML
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$ga_id}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$ga_id}', {
    'anonymize_ip': true,
    'cookie_flags': 'SameSite=None;Secure'
  });
</script>
<!-- End Google Analytics 4 -->
HTML;
}

// Facebook Pixel - Only if marketing consent given
if ($consent['marketing'] && !empty($fb_pixel_id) && strlen($fb_pixel_id) > 10) {
    $scripts[] = <<<HTML
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$fb_pixel_id}');
fbq('track', 'PageView');
</script>
<noscript>
<img height="1" width="1" style="display:none" 
src="https://www.facebook.com/tr?id={$fb_pixel_id}&ev=PageView&noscript=1"/>
</noscript>
<!-- End Facebook Pixel Code -->
HTML;
}

// Output all scripts
echo implode("\n", $scripts);

// Track page view in database if user consented
if ($consent['analytics'] && isset($db) && $db instanceof PDO) {
    try {
        if (!isset($_SESSION['last_tracked_page']) || $_SESSION['last_tracked_page'] !== $_SERVER['REQUEST_URI']) {

            if (!isset($_SESSION['session_id'])) {
                $_SESSION['session_id'] = session_id();
            }

            // Detect device type
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $user_agent)) {
                $device_type = 'tablet';
            } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/i', $user_agent)) {
                $device_type = 'mobile';
            } else {
                $device_type = 'desktop';
            }

            // Detect browser
            $browser = 'Unknown';
            if (preg_match('/MSIE|Trident/i', $user_agent)) {
                $browser = 'IE';
            } elseif (preg_match('/Edg/i', $user_agent)) {
                $browser = 'Edge';
            } elseif (preg_match('/Chrome/i', $user_agent)) {
                $browser = 'Chrome';
            } elseif (preg_match('/Safari/i', $user_agent)) {
                $browser = 'Safari';
            } elseif (preg_match('/Firefox/i', $user_agent)) {
                $browser = 'Firefox';
            } elseif (preg_match('/Opera|OPR/i', $user_agent)) {
                $browser = 'Opera';
            }

            // Insert into user_tracking table
            $stmt = $db->prepare("INSERT INTO user_tracking 
                (session_id, page_url, referrer_url, ip_address, user_agent, device_type, browser) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $_SESSION['session_id'],
                $_SERVER['REQUEST_URI'],
                $_SERVER['HTTP_REFERER'] ?? '',
                $_SERVER['REMOTE_ADDR'],
                $user_agent,
                $device_type,
                $browser
            ]);

            $_SESSION['last_tracked_page'] = $_SERVER['REQUEST_URI'];
        }
    } catch (PDOException $e) {
        error_log("Error tracking page view: " . $e->getMessage());
    }
}

// Return Google Tag Manager body code if needed
function getGTMBodyCode() {
    global $consent, $gtm_id;

    if ($consent['analytics'] && !empty($gtm_id) && $gtm_id !== 'GTM-XXXXXX') {
        return <<<HTML
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$gtm_id}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
HTML;
    }
    return '';
}
?>