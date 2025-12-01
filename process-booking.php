<?php
// Clean API Response for Booking
header('Content-Type: application/json; charset=utf-8');

// Clear any buffered output
ob_start();
ob_clean();

// Error configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Request validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Load database
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $db = $database->connect();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    // Get POST data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $enquiry = trim($_POST['enquiry'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone is required';
    if (empty($enquiry)) $errors[] = 'Enquiry is required';
    if (empty($payment_method)) $errors[] = 'Payment method is required';
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['error' => implode(', ', $errors)]);
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }
    
    // Validate phone
    if (!preg_match('/^\d{10}$/', $phone)) {
        http_response_code(400);
        echo json_encode(['error' => 'Phone must be 10 digits']);
        exit;
    }
    
    // Validate payment method
    if (!in_array($payment_method, ['Razorpay', 'PayPal'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payment method']);
        exit;
    }
    
    // Get dynamic booking price from payment_settings
    $stmt = $db->prepare("SELECT setting_value FROM payment_settings WHERE setting_key = 'booking_price' LIMIT 1");
    $stmt->execute();
    $price_setting = $stmt->fetch(PDO::FETCH_ASSOC);
    $booking_price = $price_setting ? floatval($price_setting['setting_value']) : 999;
    
    // Get or create payment method
    $stmt = $db->prepare("SELECT id FROM payment_methods WHERE name = ? LIMIT 1");
    $stmt->execute([$payment_method]);
    $method = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$method) {
        $stmt = $db->prepare("INSERT INTO payment_methods (name, is_active) VALUES (?, 1)");
        $stmt->execute([$payment_method]);
        $payment_method_id = $db->lastInsertId();
    } else {
        $payment_method_id = $method['id'];
    }
    
    // Insert booking
    $stmt = $db->prepare("
        INSERT INTO book_call 
        (name, email, phone, enquiry, payment_method_id, amount, payment_status, expiry_time, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())
    ");
    
    $stmt->execute([
        $name,
        $email,
        $phone,
        $enquiry,
        $payment_method_id,
        $booking_price
    ]);
    
    $booking_id = $db->lastInsertId();
    
    if (!$booking_id) {
        throw new Exception('Failed to create booking');
    }
    
    // Get Calendly link from settings
    $stmt = $db->prepare("SELECT setting_value FROM payment_settings WHERE setting_key = 'calendly_link' LIMIT 1");
    $stmt->execute();
    $calendly_setting = $stmt->fetch(PDO::FETCH_ASSOC);
    $calendly_base = $calendly_setting ? $calendly_setting['setting_value'] : 'https://calendly.com/wallofmarketing';
    $calendly_link = $calendly_base . "?booking_id=" . $booking_id;
    
    // Update Calendly link
    $stmt = $db->prepare("UPDATE book_call SET calendly_link = ? WHERE id = ?");
    $stmt->execute([$calendly_link, $booking_id]);
    
    // Generate payment URL based on payment method
    $payment_url = '';
    if ($payment_method === 'Razorpay') {
        $payment_url = '/razorpay-process.php?type=booking&id=' . $booking_id;
    } else if ($payment_method === 'PayPal') {
        $payment_url = '/paypal-process.php?type=booking&id=' . $booking_id;
    }
    
    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'booking_id' => intval($booking_id),
        'payment_url' => $payment_url,
        'calendly_link' => $calendly_link,
        'payment_method' => $payment_method,
        'amount' => $booking_price
    ]);
    
} catch (PDOException $e) {
    error_log('PDO Error in process-booking.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
} catch (Exception $e) {
    error_log('Error in process-booking.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;
