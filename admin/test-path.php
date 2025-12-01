<?php
echo "Current directory: " . __DIR__ . "\n";
echo "Auth path: " . __DIR__ . '/includes/auth.php' . "\n";
echo "File exists: " . (file_exists(__DIR__ . '/includes/auth.php') ? 'YES' : 'NO') . "\n";
echo "Real path: " . realpath(__DIR__ . '/includes/auth.php') . "\n";
?>
