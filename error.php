<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';

$errorCode = $_GET['code'] ?? '404';
$errors = [
    '400' => ['title' => 'Bad Request', 'message' => 'The request could not be understood by the server.', 'icon' => 'fa-exclamation-triangle'],
    '401' => ['title' => 'Unauthorized', 'message' => 'You need to be logged in to access this page.', 'icon' => 'fa-lock'],
    '403' => ['title' => 'Forbidden', 'message' => 'You do not have permission to access this page.', 'icon' => 'fa-ban'],
    '404' => ['title' => 'Page Not Found', 'message' => 'The page you are looking for does not exist or has been moved.', 'icon' => 'fa-search'],
    '500' => ['title' => 'Server Error', 'message' => 'Something went wrong on our end. We\'re working to fix it.', 'icon' => 'fa-tools'],
    '503' => ['title' => 'Service Unavailable', 'message' => 'The service is temporarily unavailable. Please try again later.', 'icon' => 'fa-server']
];

$error = $errors[$errorCode] ?? $errors['404'];
http_response_code((int)$errorCode);

$customSeoData = [
    'title' => $error['title'] . ' | ' . SITE_NAME,
    'description' => $error['message'],
    'robots' => 'noindex, nofollow'
];
?>

<?php require_once 'includes/header.php'; ?>

<style>
.error-page {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 60px 20px;
}

.error-content {
    max-width: 600px;
}

.error-code {
    font-size: 120px;
    font-weight: 900;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.error-icon {
    font-size: 80px;
    color: var(--primary-color);
    margin-bottom: 20px;
}

.error-title {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 15px;
    color: var(--text-dark);
}

.error-message {
    font-size: 18px;
    color: var(--text-light);
    margin-bottom: 30px;
    line-height: 1.6;
}

.error-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.error-btn {
    padding: 14px 28px;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.error-btn:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.error-btn.secondary {
    background: var(--bg-light);
    color: var(--text-dark);
}

.error-btn.secondary:hover {
    background: var(--border-color);
}

@media (max-width: 768px) {
    .error-code {
        font-size: 80px;
    }
    
    .error-icon {
        font-size: 60px;
    }
    
    .error-title {
        font-size: 24px;
    }
    
    .error-message {
        font-size: 16px;
    }
    
    .error-actions {
        flex-direction: column;
    }
    
    .error-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="error-page">
    <div class="error-content">
        <div class="error-code"><?= $errorCode ?></div>
        <div class="error-icon">
            <i class="fas <?= $error['icon'] ?>"></i>
        </div>
        <h1 class="error-title"><?= htmlspecialchars($error['title']) ?></h1>
        <p class="error-message"><?= htmlspecialchars($error['message']) ?></p>
        
        <div class="error-actions">
            <a href="/" class="error-btn">
                <i class="fas fa-home"></i> Return to Homepage
            </a>
            <a href="javascript:history.back()" class="error-btn secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
            <a href="/contact.php" class="error-btn secondary">
                <i class="fas fa-envelope"></i> Contact Us
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
