<?php
// Connect to admin config
// require_once __DIR__ . '/config.php';
require_once '../config/config.php';
// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin/login.php");
    exit();
}

$page_title = 'Manage Bookings';

// Handle actions (delete, update status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $booking_id = (int)$_POST['booking_id'];
        
        switch ($_POST['action']) {
            case 'delete':
                $stmt = $db->prepare("DELETE FROM book_call WHERE id = ?");
                $stmt->execute([$booking_id]);
                $success_message = "Booking deleted successfully";
                break;
                
            case 'update_status':
                $status = $_POST['status'];
                $stmt = $db->prepare("UPDATE book_call SET payment_status = ? WHERE id = ?");
                $stmt->execute([$status, $booking_id]);
                $success_message = "Status updated successfully";
                break;
        }
    }
}

// Fetch all bookings with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filter options
$filter_status = $_GET['status'] ?? 'all';
$filter_payment = $_GET['payment_method'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where_clauses = [];
$params = [];

if ($filter_status !== 'all') {
    $where_clauses[] = "bc.payment_status = ?";
    $params[] = $filter_status;
}

if ($filter_payment !== 'all') {
    $where_clauses[] = "pm.name = ?";
    $params[] = $filter_payment;
}

if (!empty($search)) {
    $where_clauses[] = "(bc.name LIKE ? OR bc.email LIKE ? OR bc.phone LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count total bookings
$count_query = "SELECT COUNT(*) as total FROM book_call bc 
                LEFT JOIN payment_methods pm ON bc.payment_method_id = pm.id 
                $where_sql";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_bookings = $count_stmt->fetch()['total'];
$total_pages = ceil($total_bookings / $per_page);

// Fetch bookings
$query = "SELECT bc.*, pm.name as payment_method_name 
          FROM book_call bc 
          LEFT JOIN payment_methods pm ON bc.payment_method_id = pm.id 
          $where_sql 
          ORDER BY bc.created_at DESC 
          LIMIT $per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed,
    SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_revenue
    FROM book_call";
$stats = $db->query($stats_query)->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .header h1 {
            color: #333;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            opacity: 0.9;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filters select,
        .filters input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .filters button {
            padding: 10px 20px;
            background: #0066FF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .filters button:hover {
            background: #0052cc;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
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
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #000;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        
        .pagination a:hover {
            background: #0066FF;
            color: white;
        }
        
        .pagination span.current {
            background: #0066FF;
            color: white;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $page_title; ?></h1>
            <a href="/admin/dashboard.php" class="btn btn-view">← Back to Dashboard</a>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card success">
                <h3><?php echo $stats['completed']; ?></h3>
                <p>Completed</p>
            </div>
            <div class="stat-card warning">
                <h3><?php echo $stats['pending']; ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-card info">
                <h3>₹<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
        
        <form class="filters" method="GET">
            <select name="status">
                <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="failed" <?php echo $filter_status === 'failed' ? 'selected' : ''; ?>>Failed</option>
            </select>
            
            <select name="payment_method">
                <option value="all" <?php echo $filter_payment === 'all' ? 'selected' : ''; ?>>All Payment Methods</option>
                <option value="Razorpay" <?php echo $filter_payment === 'Razorpay' ? 'selected' : ''; ?>>Razorpay</option>
                <option value="PayPal" <?php echo $filter_payment === 'PayPal' ? 'selected' : ''; ?>>PayPal</option>
            </select>
            
            <input type="text" name="search" placeholder="Search by name, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
            
            <button type="submit">Apply Filters</button>
            <a href="/admin/manage-bookings.php" class="btn btn-view">Clear Filters</a>
        </form>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">No bookings found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>#<?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['email']); ?></td>
                        <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                        <td><?php echo htmlspecialchars($booking['payment_method_name']); ?></td>
                        <td>₹<?php echo number_format($booking['amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $booking['payment_status']; ?>">
                                <?php echo ucfirst($booking['payment_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></td>
                        <td class="actions">
                            <a href="/admin/view-booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-view">View</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&payment_method=<?php echo $filter_payment; ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
