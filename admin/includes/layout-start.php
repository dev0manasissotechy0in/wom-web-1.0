<?php
/**
 * Admin Page Template Generator
 * Provides standardized start/end markup for all admin pages
 * 
 * USAGE EXAMPLE:
 * 
 * <?php
 * require_once __DIR__ . '/includes/auth.php';
 * require_once __DIR__ . '/../config/config.php';
 * 
 * $page_title = 'Page Title';
 * 
 * // Your PHP logic here
 * $data = $db->query("SELECT...")->fetchAll();
 * ?>
 * <?php include 'includes/admin-layout-start.php'; ?>
 * 
 * <div class="content">
 *     <div class="page-header">
 *         <h1><?php echo $page_title; ?></h1>
 *     </div>
 *     <!-- Your content here -->
 * </div>
 * 
 * <?php include 'includes/admin-layout-end.php'; ?>
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Admin' : 'Admin Panel'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Summernote for rich text editing -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
</head>
<body class="admin-body">
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'topbar.php'; ?>
        <div class="content">

