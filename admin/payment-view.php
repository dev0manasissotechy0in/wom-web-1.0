<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin/login.php");
    exit();
}

$page_title = 'Transaction Details';
$transaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$transaction_id) {
    header("Location: payment-transactions.php");
    exit();
}

// Get transaction details
$stmt = $db->prepare("SELECT b.*, pm.name as payment_method_name 
                     FROM book_call b 
                     LEFT JOIN payment_methods pm ON b.payment_method_id = pm.id 
                     WHERE b.id = ?");
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    header("Location: payment-transactions.php");
    exit();
}

// Handle status update
$update_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['payment_status'];
    $stmt = $db->prepare("UPDATE book_call SET payment_status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $transaction_id])) {
        $update_message = "Payment status updated successfully!";
        $transaction['payment_status'] = $new_status;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin-left: 260px;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-back {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
            font-weight: 500;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .amount-display {
            font-size: 32px;
            font-weight: 700;
            color: #000;
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn-update {
            width: 100%;
            padding: 12px;
            background: #000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-update:hover {
            background: #333;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #000;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #000;
        }
        
        .timeline-date {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }
        
        .timeline-content {
            font-size: 14px;
            color: #333;
        }
        
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-receipt"></i> Transaction #<?php echo $transaction['id']; ?></h1>
            <a href="payment-transactions.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Transactions
            </a>
        </div>
        
        <?php if ($update_message): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $update_message; ?>
        </div>
        <?php endif; ?>
        
        <div class="details-grid">
            <div>
                <div class="card">
                    <h2><i class="fas fa-user"></i> Customer Information</h2>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($transaction['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($transaction['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($transaction['phone']); ?></span>
                    </div>
                    <?php if ($transaction['enquiry']): ?>
                    <div class="info-row">
                        <span class="info-label">Enquiry:</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($transaction['enquiry'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="card" style="margin-top: 20px;">
                    <h2><i class="fas fa-credit-card"></i> Payment Information</h2>
                    <div class="info-row">
                        <span class="info-label">Payment ID:</span>
                        <span class="info-value"><?php echo htmlspecialchars($transaction['payment_id'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value"><?php echo htmlspecialchars($transaction['payment_method_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <span class="status-badge status-<?php echo $transaction['payment_status']; ?>">
                                <?php echo ucfirst($transaction['payment_status']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Amount:</span>
                        <span class="info-value" style="font-size: 20px; font-weight: 700;">₹<?php echo number_format($transaction['amount'], 2); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Time:</span>
                        <span class="info-value"><?php echo date('M d, Y H:i:s', strtotime($transaction['payment_time'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Created At:</span>
                        <span class="info-value"><?php echo date('M d, Y H:i:s', strtotime($transaction['created_at'])); ?></span>
                    </div>
                    <?php if ($transaction['calendly_link']): ?>
                    <div class="info-row">
                        <span class="info-label">Calendly Link:</span>
                        <span class="info-value">
                            <a href="<?php echo htmlspecialchars($transaction['calendly_link']); ?>" target="_blank" style="color: #007bff;">
                                View Scheduling Link <i class="fas fa-external-link-alt"></i>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <div class="card">
                    <div class="amount-display">
                        ₹<?php echo number_format($transaction['amount'], 2); ?>
                    </div>
                    
                    <h2><i class="fas fa-edit"></i> Update Status</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select name="payment_status">
                                <option value="pending" <?php echo $transaction['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $transaction['payment_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="failed" <?php echo $transaction['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn-update">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </form>
                </div>
                
                <div class="card" style="margin-top: 20px;">
                    <h2><i class="fas fa-clock"></i> Timeline</h2>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date"><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></div>
                            <div class="timeline-content"><strong>Booking Created</strong></div>
                        </div>
                        <?php if ($transaction['payment_time']): ?>
                        <div class="timeline-item">
                            <div class="timeline-date"><?php echo date('M d, Y H:i', strtotime($transaction['payment_time'])); ?></div>
                            <div class="timeline-content"><strong>Payment Processed</strong><br>Status: <?php echo ucfirst($transaction['payment_status']); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($transaction['expiry_time']): ?>
                        <div class="timeline-item">
                            <div class="timeline-date"><?php echo date('M d, Y H:i', strtotime($transaction['expiry_time'])); ?></div>
                            <div class="timeline-content"><strong>Booking Expires</strong></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
