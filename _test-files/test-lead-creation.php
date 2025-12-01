<?php
// Simulate a new download with a fresh email to test lead creation
require_once 'config/config.php';

$test_email = 'newtest_' . time() . '@test.com';
$resource_id = 1;

echo "Testing lead creation with new email: $test_email\n\n";

try {
    // Check if email exists
    $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM resource_downloads WHERE resource_id = ? AND email = ?");
    $checkStmt->execute([$resource_id, $test_email]);
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
    echo "Existing downloads for this email: {$result['count']}\n";
    
    if ($result['count'] == 0) {
        echo "This is a NEW download - will create lead record\n\n";
        
        // Insert new lead
        $insertStmt = $db->prepare("INSERT INTO resource_downloads (resource_id, name, email, phone, company, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertResult = $insertStmt->execute([
            $resource_id,
            'Test User',
            $test_email,
            '1234567890',
            'Test Company',
            '127.0.0.1',
            'Test Browser'
        ]);
        
        if ($insertResult) {
            $newId = $db->lastInsertId();
            echo "✅ SUCCESS! Lead created with ID: $newId\n\n";
            
            // Verify it was created
            $verifyStmt = $db->prepare("SELECT * FROM resource_downloads WHERE id = ?");
            $verifyStmt->execute([$newId]);
            $lead = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            
            echo "Lead details:\n";
            print_r($lead);
        } else {
            echo "❌ FAILED to insert lead\n";
            print_r($insertStmt->errorInfo());
        }
    } else {
        echo "This email already downloaded this resource (duplicate)\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
