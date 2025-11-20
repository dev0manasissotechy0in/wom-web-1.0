<?php
define('SECURE_ACCESS', true);
require_once '../includes/config.php';

// Create admin users table
$db->exec("CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    status ENUM('active', 'disabled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    last_ip VARCHAR(50),
    INDEX idx_username (username),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Create first admin user
$username = 'admin'; // Change this
$password = 'SecurePassword123!'; // Change this immediately!
$email = '[email protected]'; // Change this

$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $stmt = $db->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hashedPassword, $email]);
    echo "Admin user created successfully!<br>";
    echo "Username: $username<br>";
    echo "Password: (the one you set above)<br><br>";
    echo "<strong style='color:red;'>IMPORTANT: Delete this file immediately!</strong><br>";
    echo "<a href='login.php'>Go to Login</a>";
} catch(PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "Admin user already exists!";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
