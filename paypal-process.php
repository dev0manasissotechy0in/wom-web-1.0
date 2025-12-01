<?php
// Unified PayPal Payment Processing
// Works for both Appointment Bookings and Paid Resources

require_once __DIR__ . '/config/config.php';

// Validate parameters
$payment_type = $_GET['type'] ?? ''; // 'booking' or 'resource'
$item_id = (int)($_GET['id'] ?? 0);

if (!in_array($payment_type, ['booking', 'resource']) || empty($item_id)) {
    die("Invalid payment request");
}

try {
    // Fetch payment details based on type
    if ($payment_type === 'booking') {
        $stmt = $db->prepare("SELECT * FROM book_call WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            die("Booking not found");
        }
        
        // Check expiry
        if (strtotime($item['expiry_time']) < time()) {
            die("This booking has expired. Please create a new booking.");
        }
        
        // Check if already paid
        if ($item['payment_status'] === 'completed') {
            header("Location: /thank-you.php?type=booking&id=" . $item_id);
            exit();
        }
        
        $customer_name = $item['name'];
        $customer_email = $item['email'];
        $customer_phone = $item['phone'];
        $amount = $item['amount'];
        $description = "Consultation Booking #" . $item_id;
        
    } else if ($payment_type === 'resource') {
        // Fetch resource download record
        $stmt = $db->prepare("
            SELECT rd.*, r.title, r.price 
            FROM resource_downloads rd 
            JOIN resources r ON rd.resource_id = r.id 
            WHERE rd.id = ?
        ");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            die("Resource download not found");
        }
        
        // Check if already paid
        if ($item['payment_status'] === 'completed') {
            header("Location: /download.php?r=" . $item['resource_id']);
            exit();
        }
        
        $customer_name = $item['name'];
        $customer_email = $item['email'];
        $customer_phone = $item['phone'] ?? '';
        $amount = $item['price'];
        $description = "Resource: " . $item['title'];
    }
    
    // PayPal configuration
    $paypal_email = PAYPAL_EMAIL ?? 'your-paypal@business.com';
    $currency = 'INR';
    $success_url = SITE_URL . '/paypal-success.php';
    $cancel_url = SITE_URL . ($payment_type === 'booking' ? '/book-call.php' : '/resources.php');
    $notify_url = SITE_URL . '/paypal-ipn.php';
    
} catch (PDOException $e) {
    error_log('Database error in paypal-process.php: ' . $e->getMessage());
    die("Database error occurred");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with PayPal - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0070ba 0%, #003087 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .payment-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .payment-type-badge {
            display: inline-block;
            padding: 6px 15px;
            background: #0070ba;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .item-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .item-info p {
            margin: 10px 0;
            color: #555;
        }
        
        .item-info strong {
            color: #333;
        }
        
        .amount-display {
            text-align: center;
            margin: 30px 0;
        }
        
        .amount-display h2 {
            font-size: 48px;
            color: #0070ba;
        }
        
        .paypal-form {
            text-align: center;
        }
        
        .pay-button {
            width: 100%;
            padding: 18px;
            background: #0070ba;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .pay-button:hover {
            background: #005ea6;
        }
        
        .secure-badge {
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <span class="payment-type-badge"><?php echo ucfirst($payment_type); ?></span>
            <h1>Complete Your Payment</h1>
            <p><?php echo $description; ?></p>
        </div>
        
        <div class="item-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer_email); ?></p>
            <?php if ($customer_phone): ?>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer_phone); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="amount-display">
            <h2>₹<?php echo number_format($amount, 2); ?></h2>
            <p><?php echo $payment_type === 'booking' ? 'Consultation Fee' : 'Resource Price'; ?></p>
        </div>
        
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="paypal-form">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?php echo $paypal_email; ?>">
            <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($description); ?>">
            <input type="hidden" name="item_number" value="<?php echo $item_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="currency_code" value="<?php echo $currency; ?>">
            <input type="hidden" name="return" value="<?php echo $success_url; ?>?type=<?php echo $payment_type; ?>&id=<?php echo $item_id; ?>">
            <input type="hidden" name="cancel_return" value="<?php echo $cancel_url; ?>">
            <input type="hidden" name="notify_url" value="<?php echo $notify_url; ?>">
            <input type="hidden" name="custom" value="<?php echo $payment_type . '|' . $item_id; ?>">
            
            <button type="submit" class="pay-button">Pay with PayPal</button>
        </form>
        
        <div class="secure-badge">
            🔒 Secured by PayPal
        </div>
    </div>
</body>
</html>
