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
    
    // Razorpay credentials (Add these to your config.php)
    $razorpay_key_id = RAZORPAY_KEY_ID ?? 'your_razorpay_key_id';
    $razorpay_key_secret = RAZORPAY_KEY_SECRET ?? 'your_razorpay_key_secret';
    
} catch (PDOException $e) {
    error_log('Database error in razorpay-process.php: ' . $e->getMessage());
    die("Database error occurred");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with Razorpay - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
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
            color: #000000;
        }
        
        .pay-button {
            width: 100%;
            padding: 18px;
            background: #000000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .pay-button:hover {
            background: #17181a;
        }
        
        .secure-badge {
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 14px;
        }
        
        .secure-badge svg {
            width: 16px;
            height: 16px;
            vertical-align: middle;
            margin-right: 5px;
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
        
        <button id="rzp-button" class="pay-button">Pay with Razorpay</button>
        
        <div class="secure-badge">
            🔒 Secured by Razorpay
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const options = {
            "key": "<?php echo $razorpay_key_id; ?>",
            "amount": "<?php echo $booking['amount'] * 100; ?>", // Amount in paise
            "currency": "INR",
            "name": "<?php echo htmlspecialchars(SITE_NAME); ?>",
            "description": "Consultation Booking #<?php echo $booking_id; ?>",
            "order_id": "", // You can generate order_id via Razorpay API
            "handler": function (response) {
                // Payment successful
                window.location.href = `/razorpay-success.php?booking_id=<?php echo $booking_id; ?>&payment_id=` + response.razorpay_payment_id;
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($booking['name']); ?>",
                "email": "<?php echo htmlspecialchars($booking['email']); ?>",
                "contact": "<?php echo htmlspecialchars($booking['phone']); ?>"
            },
            "theme": {
                "color": "#000000"
            },
            "modal": {
                "ondismiss": function() {
                    alert('Payment cancelled. You can try again.');
                }
            }
        };
        
        const rzp = new Razorpay(options);
        
        document.getElementById('rzp-button').onclick = function(e) {
            rzp.open();
            e.preventDefault();
        };
    </script>
</body>
</html>
