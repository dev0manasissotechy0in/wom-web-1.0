<?php
session_start();
require_once __DIR__ . '/config/config.php';

// Get parameters
$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($type !== 'resource' || $id <= 0) {
    die('Invalid request');
}

// Get download record
$stmt = $db->prepare("SELECT rd.*, r.title, r.price 
                     FROM resource_downloads rd 
                     JOIN resources r ON rd.resource_id = r.id 
                     WHERE rd.id = ?");
$stmt->execute([$id]);
$download = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$download) {
    die('Download record not found');
}

$amount = $download['price'] * 100; // Convert to paise
$currency = 'INR';

// Get Razorpay settings
$razorpay_key_id = defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '';
$razorpay_key_secret = defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : '';

if (empty($razorpay_key_id) || empty($razorpay_key_secret)) {
    die('Razorpay credentials not configured. Please contact admin.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?php echo htmlspecialchars($download['title']); ?></title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .payment-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-header h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .resource-title {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .amount-display {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .amount-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .amount-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000;
        }
        
        .btn-pay {
            width: 100%;
            padding: 16px;
            background: #000;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-pay:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        
        .btn-cancel {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: #666;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #f8f9fa;
        }
        
        .secure-badge {
            text-align: center;
            margin-top: 20px;
            color: #28a745;
            font-size: 0.9rem;
        }
        
        .secure-badge i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>Complete Your Purchase</h1>
            <div class="resource-title"><?php echo htmlspecialchars($download['title']); ?></div>
        </div>
        
        <div class="amount-display">
            <div class="amount-label">Amount to Pay</div>
            <div class="amount-value">₹<?php echo number_format($download['price'], 2); ?></div>
        </div>
        
        <button id="payButton" class="btn-pay">
            <i class="fas fa-lock"></i> Pay with Razorpay
        </button>
        
        <button onclick="window.history.back()" class="btn-cancel">
            Cancel
        </button>
        
        <div class="secure-badge">
            <i class="fas fa-shield-alt"></i> Secure Payment Gateway
        </div>
    </div>

    <script>
    document.getElementById('payButton').onclick = function(e) {
        var options = {
            "key": "<?php echo $razorpay_key_id; ?>",
            "amount": "<?php echo $amount; ?>",
            "currency": "<?php echo $currency; ?>",
            "name": "<?php echo SITE_NAME; ?>",
            "description": "<?php echo htmlspecialchars($download['title']); ?>",
            "handler": function (response) {
                // Send payment details to server
                window.location.href = '/razorpay-resource-success.php?payment_id=' + response.razorpay_payment_id + '&download_id=<?php echo $id; ?>';
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($download['name']); ?>",
                "email": "<?php echo htmlspecialchars($download['email']); ?>",
                "contact": "<?php echo htmlspecialchars($download['phone']); ?>"
            },
            "theme": {
                "color": "#000000"
            }
        };
        
        var rzp = new Razorpay(options);
        rzp.open();
        e.preventDefault();
    };
    </script>
</body>
</html>
