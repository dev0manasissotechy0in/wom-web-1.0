<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin/login.php");
    exit();
}

$page_title = 'Payment Methods';
$success_message = '';
$error_message = '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_method'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $db->prepare("INSERT INTO payment_methods (name, created_at) VALUES (?, NOW())");
            if ($stmt->execute([$name])) {
                $success_message = "Payment method added successfully!";
            } else {
                $error_message = "Failed to add payment method.";
            }
        }
    } elseif (isset($_POST['edit_method'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        if (!empty($name) && $id > 0) {
            $stmt = $db->prepare("UPDATE payment_methods SET name = ? WHERE id = ?");
            if ($stmt->execute([$name, $id])) {
                $success_message = "Payment method updated successfully!";
            } else {
                $error_message = "Failed to update payment method.";
            }
        }
    } elseif (isset($_POST['delete_method'])) {
        $id = (int)$_POST['id'];
        // Check if method is in use
        $check_stmt = $db->prepare("SELECT COUNT(*) as count FROM book_call WHERE payment_method_id = ?");
        $check_stmt->execute([$id]);
        $usage_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($usage_count > 0) {
            $error_message = "Cannot delete payment method. It has {$usage_count} associated transaction(s).";
        } else {
            $stmt = $db->prepare("DELETE FROM payment_methods WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success_message = "Payment method deleted successfully!";
            } else {
                $error_message = "Failed to delete payment method.";
            }
        }
    }
}

// Get all payment methods with usage count
$stmt = $db->query("SELECT pm.*, COUNT(b.id) as usage_count 
                   FROM payment_methods pm 
                   LEFT JOIN book_call b ON pm.id = b.payment_method_id 
                   GROUP BY pm.id 
                   ORDER BY pm.name");
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get edit data
$edit_method = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM payment_methods WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_method = $stmt->fetch(PDO::FETCH_ASSOC);
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn-submit {
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
        
        .btn-submit:hover {
            background: #333;
        }
        
        .btn-cancel {
            width: 100%;
            padding: 12px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            margin-top: 10px;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
        }
        
        .methods-list {
            list-style: none;
        }
        
        .method-item {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .method-item:hover {
            background: #f8f9fa;
            border-color: #ddd;
        }
        
        .method-info {
            flex: 1;
        }
        
        .method-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .method-meta {
            font-size: 14px;
            color: #666;
        }
        
        .method-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit,
        .btn-delete {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .btn-edit:hover {
            background: #138496;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #e7f3ff;
            color: #0066cc;
        }
        
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .method-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .method-actions {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-credit-card"></i> <?php echo $page_title; ?></h1>
            <a href="payment-settings.php" class="btn-back">
                <i class="fas fa-cog"></i> Payment Settings
            </a>
        </div>
        
        <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <div class="content-grid">
            <div class="card">
                <h2>
                    <i class="fas fa-plus-circle"></i>
                    <?php echo $edit_method ? 'Edit Payment Method' : 'Add Payment Method'; ?>
                </h2>
                <form method="POST">
                    <?php if ($edit_method): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_method['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Method Name *</label>
                        <input type="text" name="name" required 
                               value="<?php echo htmlspecialchars($edit_method['name'] ?? ''); ?>"
                               placeholder="e.g., RazorPay, PayPal, Stripe">
                    </div>
                    
                    <button type="submit" name="<?php echo $edit_method ? 'edit_method' : 'add_method'; ?>" class="btn-submit">
                        <i class="fas fa-save"></i> <?php echo $edit_method ? 'Update Method' : 'Add Method'; ?>
                    </button>
                    
                    <?php if ($edit_method): ?>
                    <a href="payment-methods.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="card">
                <h2><i class="fas fa-list"></i> Payment Methods (<?php echo count($payment_methods); ?>)</h2>
                
                <?php if (count($payment_methods) > 0): ?>
                <ul class="methods-list">
                    <?php foreach ($payment_methods as $method): ?>
                    <li class="method-item">
                        <div class="method-info">
                            <div class="method-name">
                                <i class="fas fa-credit-card"></i> <?php echo htmlspecialchars($method['name']); ?>
                            </div>
                            <div class="method-meta">
                                <span class="badge">
                                    <i class="fas fa-shopping-cart"></i> <?php echo $method['usage_count']; ?> transactions
                                </span>
                                <span style="margin-left: 10px; color: #999;">
                                    Added: <?php echo date('M d, Y', strtotime($method['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="method-actions">
                            <a href="?edit=<?php echo $method['id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this payment method?');">
                                <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
                                <button type="submit" name="delete_method" class="btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-credit-card" style="font-size: 48px; margin-bottom: 10px;"></i>
                    <p>No payment methods found. Add one to get started.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
