<?php
require_once '../config/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Handle status update
if(isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $db->prepare("UPDATE contact_inquiries SET status = ? WHERE id = ?")->execute([$status, $id]);
    header('Location: inquiries.php?msg=updated');
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM contact_inquiries WHERE id = ?")->execute([$id]);
    header('Location: inquiries.php?msg=deleted');
    exit();
}

$inquiries = $db->query("SELECT * FROM contact_inquiries ORDER BY created_at DESC")->fetchAll();
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
                <h1>Contact Inquiries</h1>
            </div>
            
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    if($_GET['msg'] == 'updated') echo 'Inquiry status updated!';
                    if($_GET['msg'] == 'deleted') echo 'Inquiry deleted!';
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="content-card">
                <div class="table-responsive">
                    <table class="data-table">
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
                            <tr>
                                <td><?php echo $inquiry['id']; ?></td>
                                <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                <td><?php echo htmlspecialchars($inquiry['phone']); ?></td>
                                <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                                <td><?php echo htmlspecialchars(substr($inquiry['message'], 0, 50)); ?>...</td>
                                <td>
                                    <select onchange="window.location='?status='+this.value+'&id=<?php echo $inquiry['id']; ?>'" class="status-select">
                                        <option value="new" <?php echo $inquiry['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="read" <?php echo $inquiry['status'] == 'read' ? 'selected' : ''; ?>>Read</option>
                                        <option value="responded" <?php echo $inquiry['status'] == 'responded' ? 'selected' : ''; ?>>Responded</option>
                                        <option value="closed" <?php echo $inquiry['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $inquiry['id']; ?>" class="btn-icon btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
        }
    </style>
</body>
</html>
