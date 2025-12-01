<?php
require_once 'config/config.php';

echo "=== Checking Case Studies ===\n\n";

try {
    // Check total case studies
    $stmt = $db->query('SELECT COUNT(*) as count FROM case_studies');
    $total = $stmt->fetch()['count'];
    echo "Total case studies in database: $total\n\n";
    
    if ($total > 0) {
        // Get recent case studies
        $stmt = $db->query('SELECT id, title, slug, industry, client_name, status FROM case_studies ORDER BY created_at DESC LIMIT 5');
        $studies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Recent case studies:\n";
        foreach ($studies as $study) {
            echo "  - ID: {$study['id']}, Title: {$study['title']}\n";
            echo "    Client: {$study['client_name']}, Industry: {$study['industry']}, Status: {$study['status']}\n\n";
        }
    } else {
        echo "No case studies found in the database.\n";
        echo "The homepage will show default/placeholder case studies.\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
