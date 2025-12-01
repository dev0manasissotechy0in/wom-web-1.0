<?php
// 1. Load Configuration & Auth
require_once __DIR__ . '/includes/auth.php';

// Database Connection - Aligned with contact-submit.php approach
try {
    // Try to find config file (Handles both root and admin/ subfolder placement)
    if (file_exists(__DIR__ . '/../config/config.php')) {
        require_once __DIR__ . '/../config/config.php';
    } elseif (file_exists(__DIR__ . '/config/config.php')) {
        require_once __DIR__ . '/config/config.php';
    } else {
        die("Error: Database configuration file not found.");
    }
} catch (Exception $e) {
    error_log('Config error: ' . $e->getMessage());
    die("Database connection error. Please check configuration.");
}

$page_title = 'Contact Inquiries';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Verify database connection exists
if (!isset($db)) {
    die("Error: Database connection not established.");
}

// 2. Handle Status Update
if(isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    // Validate status enum to prevent errors
    $valid_statuses = ['new', 'read', 'responded', 'closed'];
    if (in_array($status, $valid_statuses)) {
        try {
            $stmt = $db->prepare("UPDATE contact_inquiries SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            header('Location: inquiries.php?msg=updated');
            exit();
        } catch (Exception $e) {
            error_log("Update error: " . $e->getMessage());
            header('Location: inquiries.php?msg=error');
            exit();
        }
    }
}

// 3. Handle Delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("DELETE FROM contact_inquiries WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: inquiries.php?msg=deleted');
        exit();
    } catch (Exception $e) {
        error_log("Delete error: " . $e->getMessage());
        header('Location: inquiries.php?msg=error');
        exit();
    }
}

// 4. Fetch Data (Matches contact_inquiries table structure)
try {
    $stmt = $db->query("SELECT * FROM contact_inquiries ORDER BY created_at DESC");
    $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Fetch error: " . $e->getMessage());
    $inquiries = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Inquiries - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>

        <div class="content">
            <div class="page-header">
                <h1><i class="fas fa-envelope"></i> Contact Inquiries</h1>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-<?php echo $_GET['msg'] === 'error' ? 'danger' : 'success'; ?>">
                    <?php
                    switch($_GET['msg']) {
                        case 'updated': echo 'Status updated successfully!'; break;
                        case 'deleted': echo 'Inquiry deleted successfully!'; break;
                        case 'error': echo 'An error occurred. Please try again.'; break;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>All Inquiries (<?php echo count($inquiries); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if(empty($inquiries)): ?>
                        <p class="text-muted">No inquiries yet.</p>
                    <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($inquiries as $inquiry): ?>
                            <tr class="status-<?php echo $inquiry['status']; ?>">
                                <td><?php echo htmlspecialchars($inquiry['id']); ?></td>
                                <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                <td>
                                    <?php 
                                    $phone = htmlspecialchars($inquiry['phone']);
                                    $country_code = htmlspecialchars($inquiry['country_code'] ?? '');
                                    echo $country_code ? "+$country_code $phone" : $phone;
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                                <td class="message-cell">
                                    <div class="message-preview">
                                        <?php echo htmlspecialchars(substr($inquiry['message'], 0, 100)); ?>
                                        <?php if(strlen($inquiry['message']) > 100): ?>...<?php endif; ?>
                                    </div>
                                    <?php if(strlen($inquiry['message']) > 100): ?>
                                    <button class="btn-link" onclick="alert('<?php echo htmlspecialchars(addslashes($inquiry['message'])); ?>')">Read More</button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select class="status-select" onchange="location.href='?status=' + this.value + '&id=<?php echo $inquiry['id']; ?>'">
                                        <option value="new" <?php echo $inquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="read" <?php echo $inquiry['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                        <option value="responded" <?php echo $inquiry['status'] === 'responded' ? 'selected' : ''; ?>>Responded</option>
                                        <option value="closed" <?php echo $inquiry['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </td>
                                <td>
                                    <small><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></small><br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($inquiry['created_at'])); ?></small>
                                </td>
                                <td>
                                    <a href="?delete=<?php echo $inquiry['id']; ?>" class="btn-icon btn-danger" onclick="return confirm('Are you sure you want to delete this inquiry?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
        .status-select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            background: white;
        }

        .status-select:hover {
            border-color: #3498db;
        }

        /* Status color coding */
        .status-new { border-left: 3px solid #3498db; }
        .status-read { border-left: 3px solid #f1c40f; }
        .status-responded { border-left: 3px solid #2ecc71; }
        .status-closed { border-left: 3px solid #95a5a6; }

        .message-cell {
            max-width: 300px;
        }

        .message-preview {
            word-wrap: break-word;
            line-height: 1.4;
        }

        .btn-link {
            background: none;
            border: none;
            color: #3498db;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
            font-size: 12px;
        }

        .btn-link:hover {
            color: #2980b9;
        }

        .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .text-muted {
            color: #6c757d;
            text-align: center;
            padding: 20px;
        }

        .btn-icon {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</body>
</html>
