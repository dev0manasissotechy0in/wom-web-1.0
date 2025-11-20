<?php
// Output buffering to catch any unexpected output
ob_start();

// Disable error display, log only
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Connect to config and database BEFORE setting headers
try {
    require_once __DIR__ . '/config/config.php';
} catch (Exception $e) {
    ob_clean(); // Clear any output
    header('Content-Type: application/json');
    error_log('Config load error: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'System configuration error']));
}

// Set JSON header AFTER config is loaded
header('Content-Type: application/json');

// Rate limiting function
function checkRateLimit($identifier, $max_attempts = 5, $timeframe = 900) {
    $rate_limit_file = __DIR__ . '/logs/rate_limits.log';
    $dir = dirname($rate_limit_file);
    
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
    
    if (!file_exists($rate_limit_file)) {
        @touch($rate_limit_file);
    }
    
    $logs = @file($rate_limit_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($logs === false) {
        $logs = [];
    }
    
    $time = time();
    $attempts = 0;
    
    foreach ($logs as $log) {
        $parts = explode('|', $log);
        if (count($parts) === 2) {
            list($log_identifier, $log_time) = $parts;
            if ($log_identifier === $identifier && $time - $log_time < $timeframe) {
                $attempts++;
            }
        }
    }
    
    if ($attempts >= $max_attempts) {
        return false;
    }
    
    // Log attempt
    @file_put_contents($rate_limit_file, $identifier . '|' . $time . "\n", FILE_APPEND | LOCK_EX);
    return true;
}

// Sanitize input function
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    http_response_code(405);
    die(json_encode(['error' => 'Invalid request method']));
}

try {
    // Check database connection
    if (!isset($db) || $db === null) {
        throw new Exception('Database connection not available');
    }
    
    // Rate limiting
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($ip, 5, 900)) {
        ob_clean();
        http_response_code(429);
        die(json_encode(['error' => 'Too many attempts. Please try again later.']));
    }
    
    // Sanitize and validate input
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $enquiry = sanitize($_POST['enquiry'] ?? '');
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($enquiry) || empty($payment_method)) {
        ob_clean();
        http_response_code(400);
        die(json_encode(['error' => 'All fields are required']));
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ob_clean();
        http_response_code(400);
        die(json_encode(['error' => 'Invalid email format']));
    }
    
    // Validate phone (Indian format - 10 digits)
    if (!preg_match('/^\d{10}$/', $phone)) {
        ob_clean();
        http_response_code(400);
        die(json_encode(['error' => 'Invalid phone number. Please enter 10 digits.']));
    }
    
    // Validate payment method
    if (!in_array($payment_method, ['Razorpay', 'PayPal'])) {
        ob_clean();
        http_response_code(400);
        die(json_encode(['error' => 'Invalid payment method']));
    }
    
    // Get payment method ID from database
    $stmt = $db->prepare("SELECT id FROM payment_methods WHERE name = ? LIMIT 1");
    $stmt->execute([$payment_method]);
    $method = $stmt->fetch();
    
    if (!$method) {
        // Insert payment method if not exists
        $stmt = $db->prepare("INSERT INTO payment_methods (name, is_active) VALUES (?, 1)");
        $stmt->execute([$payment_method]);
        $payment_method_id = $db->lastInsertId();
    } else {
        $payment_method_id = $method['id'];
    }
    
    // Insert booking into database
    $stmt = $db->prepare("
        INSERT INTO book_call 
        (name, email, phone, enquiry, payment_method_id, amount, payment_status, expiry_time, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())
    ");
    
    $amount = 999.00;
    $payment_status = 'pending';
    
    $result = $stmt->execute([
        $name, 
        $email, 
        $phone, 
        $enquiry, 
        $payment_method_id, 
        $amount, 
        $payment_status
    ]);
    
    if (!$result) {
        throw new Exception('Failed to create booking');
    }
    
    $booking_id = $db->lastInsertId();
    
    // Generate Calendly link
    $calendly_link = "https://calendly.com/wallofmarketing?booking_id=" . $booking_id;
    
    // Update booking with Calendly link
    $stmt = $db->prepare("UPDATE book_call SET calendly_link = ? WHERE id = ?");
    $stmt->execute([$calendly_link, $booking_id]);
    
    // Log success
    error_log("Booking created successfully: ID=$booking_id, Email=$email");
    
    // Clean buffer and send success response
    ob_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'booking_id' => $booking_id,
        'calendly_link' => $calendly_link,
        'payment_method' => $payment_method,
        'amount' => $amount
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in process-booking.php: ' . $e->getMessage());
    ob_clean();
    http_response_code(500);
    die(json_encode(['error' => 'Database error. Please try again later.']));
} catch (Exception $e) {
    error_log('Error in process-booking.php: ' . $e->getMessage());
    ob_clean();
    http_response_code(500);
    die(json_encode(['error' => 'An error occurred. Please try again.']));
}

// Flush output buffer
ob_end_flush();
?>
