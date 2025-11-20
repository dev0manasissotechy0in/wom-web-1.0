<?php
declare(strict_types=1);
$errorCode = $_GET['code'] ?? '404';
$errors = [
    '404' => ['title' => 'Page Not Found', 'message' => 'The page you are looking for does not exist.'],
    '500' => ['title' => 'Server Error', 'message' => 'Something went wrong on our end.'],
    '403' => ['title' => 'Forbidden', 'message' => 'You do not have permission to access this page.']
];

$error = $errors[$errorCode] ?? $errors['404'];
http_response_code((int)$errorCode);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $error['title'] ?></title>
</head>
<body>
    <div class="error-page">
        <h1><?= $errorCode ?></h1>
        <h2><?= $error['title'] ?></h2>
        <p><?= $error['message'] ?></p>
        <a href="/">Return to Homepage</a>
    </div>
</body>
</html>
