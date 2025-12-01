<?php
session_start();
require_once __DIR__ . '/config/config.php';

$payment_id = $_GET['payment_id'] ?? '';
$download_id = (int)($_GET['download_id'] ?? 0);

if (empty($payment_id) || $download_id <= 0) {
    header('Location: /resources');
    exit;
}

try {
    // Get download record
    $stmt = $db->prepare("SELECT rd.*, r.title, r.file_path, r.slug 
                         FROM resource_downloads rd 
                         JOIN resources r ON rd.resource_id = r.id 
                         WHERE rd.id = ?");
    $stmt->execute([$download_id]);
    $download = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$download) {
        throw new Exception('Download record not found');
    }
    
    // Update payment status
    $updateStmt = $db->prepare("UPDATE resource_downloads 
                               SET payment_status = 'completed', 
                                   payment_method = 'Razorpay',
                                   razorpay_payment_id = ?,
                                   downloaded_at = NOW()
                               WHERE id = ?");
    $updateStmt->execute([$payment_id, $download_id]);
    
    // Increment download count
    $db->prepare("UPDATE resources SET downloads = downloads + 1 WHERE id = ?")
       ->execute([$download['resource_id']]);
    
    // Generate download link
    $download_token = md5($download['email'] . $download['resource_id'] . 'secret');
    $download_url = '/download.php?r=' . $download['resource_id'] . '&t=' . $download_token;
    
} catch (Exception $e) {
    error_log("Razorpay resource success error: " . $e->getMessage());
    header('Location: /resources');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .success-container {
            background: white;
            border-radius: 16px;
            padding: 50px 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            text-align: center;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: scaleIn 0.5s ease;
        }
        
        .success-icon i {
            font-size: 40px;
            color: white;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 15px;
        }
        
        .success-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .payment-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #666;
            font-weight: 600;
        }
        
        .detail-value {
            color: #333;
            font-weight: 500;
        }
        
        .btn-download {
            display: inline-block;
            padding: 16px 40px;
            background: #000;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        
        .btn-download:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            display: inline-block;
            padding: 12px 30px;
            background: transparent;
            color: #666;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 0 5px;
        }
        
        .btn-secondary:hover {
            background: #f8f9fa;
        }
        
        .note {
            margin-top: 25px;
            padding: 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            color: #856404;
            font-size: 0.9rem;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>Payment Successful!</h1>
        <p class="success-message">
            Thank you for your purchase. Your resource is ready to download.
        </p>
        
        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">Resource:</span>
                <span class="detail-value"><?php echo htmlspecialchars($download['title']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment ID:</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment_id); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value"><?php echo htmlspecialchars($download['email']); ?></span>
            </div>
        </div>
        
        <a href="<?php echo $download_url; ?>" class="btn-download">
            <i class="fas fa-download"></i> Download Now
        </a>
        
        <div>
            <a href="/resources/<?php echo $download['slug']; ?>" class="btn-secondary">View Resource Page</a>
            <a href="/resources" class="btn-secondary">Browse More Resources</a>
        </div>
        
        <div class="note">
            <strong><i class="fas fa-info-circle"></i> Note:</strong> 
            A confirmation email has been sent to your email address with the download link. 
            You can access this resource anytime using the same email.
        </div>
    </div>
</body>
</html>
