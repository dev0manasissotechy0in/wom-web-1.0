<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin/login.php");
    exit();
}

$page_title = 'Payment Dashboard';

// Get date range (default: last 30 days)
$date_from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');

// Get overall statistics
$stats_query = "SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                AVG(CASE WHEN payment_status = 'completed' THEN amount ELSE NULL END) as avg_transaction,
                MAX(amount) as highest_transaction
                FROM book_call
                WHERE DATE(created_at) BETWEEN ? AND ?";
$stmt = $db->prepare($stats_query);
$stmt->execute([$date_from, $date_to]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get payment method breakdown
$method_query = "SELECT pm.name, 
                 COUNT(b.id) as count,
                 SUM(CASE WHEN b.payment_status = 'completed' THEN b.amount ELSE 0 END) as revenue
                 FROM payment_methods pm
                 LEFT JOIN book_call b ON pm.id = b.payment_method_id 
                 AND DATE(b.created_at) BETWEEN ? AND ?
                 GROUP BY pm.id, pm.name";
$stmt = $db->prepare($method_query);
$stmt->execute([$date_from, $date_to]);
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get daily revenue (last 7 days)
$daily_query = "SELECT DATE(created_at) as date, 
                SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as revenue,
                COUNT(*) as transactions
                FROM book_call
                WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
$daily_revenue = $db->query($daily_query)->fetchAll(PDO::FETCH_ASSOC);

// Get recent transactions
$recent_query = "SELECT b.*, pm.name as payment_method_name 
                FROM book_call b 
                LEFT JOIN payment_methods pm ON b.payment_method_id = pm.id 
                ORDER BY b.created_at DESC 
                LIMIT 10";
$recent_transactions = $db->query($recent_query)->fetchAll(PDO::FETCH_ASSOC);

// Calculate success rate
$success_rate = $stats['total_transactions'] > 0 
    ? round(($stats['completed'] / $stats['total_transactions']) * 100, 1) 
    : 0;
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
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .date-filter {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .date-filter input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .btn-filter {
            padding: 8px 16px;
            background: #000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .quick-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .quick-link {
            padding: 10px 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .quick-link:hover {
            background: #000;
            color: white;
            border-color: #000;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-card.success::before {
            background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);
        }
        
        .stat-card.warning::before {
            background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.info::before {
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-subtext {
            font-size: 12px;
            color: #999;
        }
        
        .content-grid {
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
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px 8px;
            color: #666;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid #eee;
        }
        
        td {
            padding: 12px 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
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
        
        .method-item {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .method-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .method-stats {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #666;
        }
        
        .revenue-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .revenue-item:last-child {
            border-bottom: none;
        }
        
        .revenue-date {
            font-weight: 600;
            color: #333;
        }
        
        .revenue-amount {
            font-size: 18px;
            font-weight: 700;
            color: #11998e;
        }
        
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .header-top {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <div class="header-top">
                <h1><i class="fas fa-chart-pie"></i> <?php echo $page_title; ?></h1>
                <form method="GET" class="date-filter">
                    <input type="date" name="from" value="<?php echo $date_from; ?>" required>
                    <span>to</span>
                    <input type="date" name="to" value="<?php echo $date_to; ?>" required>
                    <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                </form>
            </div>
            <div class="quick-links">
                <a href="payment-transactions.php" class="quick-link">
                    <i class="fas fa-receipt"></i> All Transactions
                </a>
                <a href="payment-methods.php" class="quick-link">
                    <i class="fas fa-credit-card"></i> Payment Methods
                </a>
                <a href="payment-settings.php" class="quick-link">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-subtext"><?php echo $stats['total_transactions']; ?> transactions</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?php echo number_format($stats['completed']); ?></div>
                <div class="stat-subtext">Success rate: <?php echo $success_rate; ?>%</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
                <div class="stat-subtext">Awaiting payment</div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-label">Average Transaction</div>
                <div class="stat-value">₹<?php echo number_format($stats['avg_transaction'] ?? 0, 2); ?></div>
                <div class="stat-subtext">Highest: ₹<?php echo number_format($stats['highest_transaction'] ?? 0, 2); ?></div>
            </div>
        </div>
        
        <div class="content-grid">
            <div class="card">
                <h2><i class="fas fa-clock"></i> Recent Transactions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transactions as $txn): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($txn['name']); ?></td>
                            <td><strong>₹<?php echo number_format($txn['amount'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($txn['payment_method_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $txn['payment_status']; ?>">
                                    <?php echo ucfirst($txn['payment_status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, H:i', strtotime($txn['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div>
                <div class="card">
                    <h2><i class="fas fa-credit-card"></i> Payment Methods</h2>
                    <?php foreach ($payment_methods as $method): ?>
                    <div class="method-item">
                        <div class="method-name">
                            <i class="fas fa-check-circle" style="color: #11998e;"></i>
                            <?php echo htmlspecialchars($method['name']); ?>
                        </div>
                        <div class="method-stats">
                            <span><?php echo $method['count']; ?> transactions</span>
                            <span><strong>₹<?php echo number_format($method['revenue'], 2); ?></strong></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="card" style="margin-top: 20px;">
                    <h2><i class="fas fa-calendar-alt"></i> Last 7 Days</h2>
                    <?php foreach ($daily_revenue as $day): ?>
                    <div class="revenue-item">
                        <div>
                            <div class="revenue-date"><?php echo date('M d, D', strtotime($day['date'])); ?></div>
                            <div style="font-size: 12px; color: #999;"><?php echo $day['transactions']; ?> transactions</div>
                        </div>
                        <div class="revenue-amount">₹<?php echo number_format($day['revenue'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
