<?php
require_once 'config/config.php';

echo "=== Setting Up Payment Settings ===\n\n";

try {
    // Check if settings already exist
    $check_stmt = $db->query("SELECT COUNT(*) as count FROM payment_settings");
    $count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count > 0) {
        echo "⚠ Payment settings already exist ({$count} records found).\n";
        echo "Do you want to reset all settings? (yes/no): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) !== 'yes') {
            echo "Aborted. No changes made.\n";
            exit;
        }
        fclose($handle);
        
        // Clear existing settings
        $db->exec("DELETE FROM payment_settings");
        echo "✓ Cleared existing settings\n\n";
    }
    
    // Default payment settings
    $default_settings = [
        [
            'key' => 'booking_price',
            'value' => '999',
            'type' => 'number',
            'description' => 'Price for consultation booking in INR'
        ],
        [
            'key' => 'booking_currency',
            'value' => 'INR',
            'type' => 'text',
            'description' => 'Currency code for bookings (INR, USD, EUR)'
        ],
        [
            'key' => 'resource_default_price',
            'value' => '499',
            'type' => 'number',
            'description' => 'Default price for paid resources'
        ],
        [
            'key' => 'enable_paid_resources',
            'value' => '1',
            'type' => 'boolean',
            'description' => 'Enable/disable paid resources feature'
        ],
        [
            'key' => 'calendly_link',
            'value' => 'https://calendly.com/your-username',
            'type' => 'url',
            'description' => 'Calendly scheduling link'
        ],
        [
            'key' => 'booking_confirmation_email',
            'value' => '1',
            'type' => 'boolean',
            'description' => 'Send confirmation email after booking'
        ],
        [
            'key' => 'booking_email_subject',
            'value' => 'Your Consultation Booking Confirmation',
            'type' => 'text',
            'description' => 'Subject line for booking confirmation emails'
        ],
        [
            'key' => 'razorpay_enabled',
            'value' => '1',
            'type' => 'boolean',
            'description' => 'Enable Razorpay payment gateway'
        ],
        [
            'key' => 'paypal_enabled',
            'value' => '1',
            'type' => 'boolean',
            'description' => 'Enable PayPal payment gateway'
        ],
        [
            'key' => 'tax_rate',
            'value' => '0',
            'type' => 'number',
            'description' => 'Tax rate percentage for transactions'
        ],
        [
            'key' => 'currency_symbol',
            'value' => '₹',
            'type' => 'text',
            'description' => 'Currency symbol to display'
        ]
    ];
    
    $stmt = $db->prepare("INSERT INTO payment_settings 
                         (setting_key, setting_value, setting_type, description, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, NOW(), NOW())");
    
    $inserted = 0;
    foreach ($default_settings as $setting) {
        if ($stmt->execute([
            $setting['key'],
            $setting['value'],
            $setting['type'],
            $setting['description']
        ])) {
            echo "✓ Added: {$setting['key']} = {$setting['value']}\n";
            $inserted++;
        } else {
            echo "✗ Failed to add: {$setting['key']}\n";
        }
    }
    
    echo "\n=== Setup Complete ===\n";
    echo "✅ Successfully inserted {$inserted} payment settings\n\n";
    
    // Display current settings
    echo "Current Payment Settings:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-30s | %-30s | %s\n", "Setting Key", "Value", "Type");
    echo str_repeat("-", 80) . "\n";
    
    $result_stmt = $db->query("SELECT * FROM payment_settings ORDER BY setting_key");
    while ($row = $result_stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-30s | %-30s | %s\n", 
            $row['setting_key'], 
            $row['setting_value'], 
            $row['setting_type']
        );
    }
    echo str_repeat("-", 80) . "\n";
    
    echo "\n💡 You can now manage these settings from:\n";
    echo "   Admin Panel > Payment Settings\n";
    echo "   URL: /admin/payment-settings.php\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
