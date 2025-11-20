<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: /admin/login.php');
    exit;
}

// Load config and database connection
require_once __DIR__ . '/../config/config.php';

try {
    $stmt = $db->query("SELECT bc.*, pm.name as payment_method_name FROM book_call bc JOIN payment_methods pm ON bc.payment_method_id = pm.id ORDER BY bc.created_at DESC");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Bookings page error: " . $e->getMessage());
    $bookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        .admin-header {
            background: #000;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .bookings-table {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .btn-link {
            display: inline-block;
            padding: 8px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-link:hover {
            background: #0056b3;
        }
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pending {
            background: #ffc107;
            color: #000;
        }
        .badge-completed {
            background: #28a745;
            color: white;
        }
        .badge-failed {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-shield-alt"></i> Admin Panel</h1>
        <a href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="container">
        <h2>Bookings</h2>
        
        <div class="bookings-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Enquiry</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Calendly</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($booking['id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                            <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                            <td><?php echo htmlspecialchars(substr($booking['enquiry'], 0, 50)); ?>...</td>
                            <td><?php echo htmlspecialchars($booking['payment_method_name']); ?></td>
                            <td>₹<?php echo number_format($booking['amount'], 2); ?></td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars($booking['payment_status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($booking['payment_status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($booking['calendly_link']): ?>
                                    <a href="<?php echo htmlspecialchars($booking['calendly_link']); ?>" class="btn-link" target="_blank">View</a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
