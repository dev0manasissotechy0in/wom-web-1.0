<?php
// Sanitize Input
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Get Site Settings
function getSiteSettings($db) {
    try {
        $stmt = $db->query("SELECT * FROM site_settings LIMIT 1");
        $settings = $stmt->fetch();
        
        // Return default values if no settings found
        if (!$settings) {
            return [
                'site_name' => SITE_NAME,
                'site_logo' => '/assets/images/Logo.png',
                'theme_color' => '#0066FF',
                'contact_email' => ADMIN_EMAIL,
                'contact_phone' => '+91 1234567890',
                'address' => 'Your Address Here',
                'facebook_url' => 'https://facebook.com',
                'instagram_url' => 'https://instagram.com',
                'linkedin_url' => 'https://linkedin.com',
                'twitter_url' => 'https://twitter.com',
                'meta_title' => SITE_NAME . ' - Digital Marketing Solutions',
                'meta_description' => 'Leading digital marketing agency',
                'google_analytics_id' => '',
                'facebook_pixel_id' => ''
            ];
        }
        
        return $settings;
    } catch (PDOException $e) {
        error_log("Error getting site settings: " . $e->getMessage());
        return [
            'site_name' => SITE_NAME,
            'site_logo' => '/assets/images/Logo.png',
            'theme_color' => '#0066FF'
        ];
    }
}

// Get SEO Meta for Page
function getSeoMeta($db, $page) {
    try {
        $stmt = $db->prepare("SELECT * FROM seo_meta WHERE page_name = ?");
        $stmt->execute([$page]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting SEO meta: " . $e->getMessage());
        return false;
    }
}

// Generate Slug
function generateSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    return $slug;
}

// Track Page View
function trackPageView($db) {
    try {
        if (!isset($_SESSION['session_id'])) {
            $_SESSION['session_id'] = session_id();
        }
        
        $stmt = $db->prepare("INSERT INTO user_tracking (session_id, page_url, referrer_url, ip_address, user_agent, device_type, browser) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $device_type = isMobile() ? 'mobile' : (isTablet() ? 'tablet' : 'desktop');
        $browser = getBrowser();
        
        $stmt->execute([
            $_SESSION['session_id'],
            $_SERVER['REQUEST_URI'],
            $_SERVER['HTTP_REFERER'] ?? '',
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $device_type,
            $browser
        ]);
    } catch (PDOException $e) {
        error_log("Error tracking page view: " . $e->getMessage());
    }
}

// Device Detection
function isMobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $user_agent);
}

function isTablet() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match("/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i", $user_agent);
}

function getBrowser() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $browser = "Unknown";
    
    $browser_array = array(
        '/msie/i'      => 'Internet Explorer',
        '/trident/i'   => 'Internet Explorer',
        '/firefox/i'   => 'Firefox',
        '/safari/i'    => 'Safari',
        '/chrome/i'    => 'Chrome',
        '/edge/i'      => 'Edge',
        '/opera/i'     => 'Opera',
        '/netscape/i'  => 'Netscape',
        '/maxthon/i'   => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i'    => 'Mobile Browser'
    );
    
    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $browser = $value;
        }
    }
    
    return $browser;
}

// // Get Blog by Slug
// function getBlogBySlug($db, $slug) {
//     try {
//         $stmt = $db->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published'");
//         $stmt->execute([$slug]);
//         $blog = $stmt->fetch();
        
//         if ($blog) {
//             // Update view count
//             $updateStmt = $db->prepare("UPDATE blogs SET views = views + 1 WHERE slug = ?");
//             $updateStmt->execute([$slug]);
//         }
        
//         return $blog;
//     } catch (PDOException $e) {
//         error_log("Error getting blog: " . $e->getMessage());
//         return false;
//     }
// }


// Generate SEO-friendly blog URL
function getBlogUrl($slug, $category = null) {
if($category) {
$category_slug = strtolower(str_replace(' ', '-', $category));
return SITE_URL . '/blogs/' . $category_slug . '/' . $slug;
}
return SITE_URL . '/blogs/' . $slug;
}

// Get category slug
function getCategorySlug($category) {
return strtolower(str_replace(' ', '-', $category));
}

// Get Recent Blogs
function getRecentBlogs($db, $limit = 3) {
    try {
        $stmt = $db->prepare("SELECT * FROM blogs WHERE status = 'published' ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting recent blogs: " . $e->getMessage());
        return [];
    }
}

// Security - CSRF Token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Escape output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get all case studies
 */
function getAllCaseStudies($db, $limit = null, $featured_only = false) {
    try {
        $sql = "SELECT * FROM case_studies WHERE status = 'published'";
        
        if($featured_only) {
            $sql .= " AND featured = 1";
        }
        
        $sql .= " ORDER BY display_order ASC, created_at DESC";
        
        if($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        return $db->query($sql)->fetchAll();
    } catch(Exception $e) {
        error_log("Error fetching case studies: " . $e->getMessage());
        return [];
    }
}

/**
 * Get case study by slug
 */
function getCaseStudyBySlug($db, $slug) {
    try {
        $stmt = $db->prepare("SELECT * FROM case_studies WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $case_study = $stmt->fetch();
        
        if($case_study) {
            // Update view count
            $updateStmt = $db->prepare("UPDATE case_studies SET views = views + 1 WHERE id = ?");
            $updateStmt->execute([$case_study['id']]);
        }
        
        return $case_study;
    } catch(Exception $e) {
        error_log("Error fetching case study: " . $e->getMessage());
        return null;
    }
}

/**
 * Get featured case studies
 */
function getFeaturedCaseStudies($db, $limit = 3) {
    return getAllCaseStudies($db, $limit, true);
}


?>

