<?php
// Connect to config and database
require_once __DIR__ . '/config/config.php';

// Check if booking ID is provided
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    die("Invalid booking ID");
}

$booking_id = (int)$_GET['booking_id'];

try {
    // Fetch booking details
    $stmt = $db->prepare("SELECT * FROM book_call WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        die("Booking not found");
    }
    
    // Check if booking has expired
    if (strtotime($booking['expiry_time']) < time()) {
        die("This booking has expired. Please create a new booking.");
    }
    
    // Check if already paid
    if ($booking['payment_status'] === 'completed') {
        header("Location: /thank-you.php?booking_id=" . $booking_id);
        exit();
    }
    
    // PayPal details (Add these to your config.php)
    $paypal_email = PAYPAL_EMAIL ?? 'your-paypal@business.com';
    $amount = $booking['amount'];
    $currency = 'INR';
    $success_url = SITE_URL . '/paypal-success.php';
    $cancel_url = SITE_URL . '/book-call.php';
    $notify_url = SITE_URL . '/paypal-ipn.php'; // IPN handler
    
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
        
        .booking-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .booking-info p {
            margin: 10px 0;
            color: #555;
        }
        
        .booking-info strong {
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
            <h1>Complete Your Payment</h1>
            <p>Booking ID: #<?php echo $booking_id; ?></p>
        </div>
        
        <div class="booking-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
        </div>
        
        <div class="amount-display">
            <h2>₹<?php echo number_format($booking['amount'], 2); ?></h2>
            <p>Consultation Fee</p>
        </div>
        
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="paypal-form">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?php echo $paypal_email; ?>">
            <input type="hidden" name="item_name" value="Consultation Booking #<?php echo $booking_id; ?>">
            <input type="hidden" name="item_number" value="<?php echo $booking_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="currency_code" value="<?php echo $currency; ?>">
            <input type="hidden" name="return" value="<?php echo $success_url; ?>?booking_id=<?php echo $booking_id; ?>">
            <input type="hidden" name="cancel_return" value="<?php echo $cancel_url; ?>">
            <input type="hidden" name="notify_url" value="<?php echo $notify_url; ?>">
            <input type="hidden" name="custom" value="<?php echo $booking_id; ?>">
            
            <button type="submit" class="pay-button">Pay with PayPal</button>
        </form>
        
        <div class="secure-badge">
            🔒 Secured by PayPal
        </div>
    </div>
</body>
</html>
