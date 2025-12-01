<?php
/**
 * Newsletter Subscription Test
 * Debug version to test newsletter functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Newsletter.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Test - WOM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #000000;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border: 2px solid #000000;
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
        
        .result {
            padding: 15px;
            margin: 15px 0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .info {
            background: #f0f0f0;
            color: #000000;
            border-left: 4px solid #000000;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        input:focus {
            outline: none;
            border-color: #000000;
        }
        
        button {
            background: #000000;
            color: white;
            border: 2px solid #000000;
            padding: 14px 32px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }
        
        button:hover {
            background: white;
            color: #000000;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Newsletter Subscription Test</h1>
        
        <?php
        $message = '';
        $messageClass = '';
        $debugInfo = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $name = trim($_POST['name'] ?? '');
            
            $debugInfo[] = "Email: " . $email;
            $debugInfo[] = "Name: " . $name;
            
            // Validate
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = '❌ Please enter a valid email address';
                $messageClass = 'error';
            } else {
                try {
                    // Test database connection
                    $debugInfo[] = "Database connected: " . ($db ? 'YES' : 'NO');
                    
                    if (!$db) {
                        throw new Exception("Database connection is null");
                    }
                    
                    // Check if table exists
                    $tableCheck = $db->query("SHOW TABLES LIKE 'newsletter_subscribers'")->rowCount();
                    $debugInfo[] = "Table exists: " . ($tableCheck > 0 ? 'YES' : 'NO');
                    
                    if ($tableCheck == 0) {
                        throw new Exception("Table 'newsletter_subscribers' does not exist");
                    }
                    
                    // Initialize Newsletter
                    $debugInfo[] = "Initializing Newsletter class...";
                    $newsletter = new Newsletter($db);
                    $debugInfo[] = "Newsletter class initialized: YES";
                    
                    // Subscribe
                    $debugInfo[] = "Calling subscribe method...";
                    $result = $newsletter->subscribe($email, $name, 'main');
                    $debugInfo[] = "Subscribe result: " . json_encode($result);
                    
                    if ($result['success']) {
                        $message = '✅ ' . $result['message'];
                        $messageClass = 'success';
                    } else {
                        $message = '⚠️ ' . $result['message'];
                        $messageClass = 'error';
                    }
                    
                } catch (Exception $e) {
                    $message = '❌ Error: ' . $e->getMessage();
                    $messageClass = 'error';
                    $debugInfo[] = "Exception: " . $e->getMessage();
                    $debugInfo[] = "Exception File: " . $e->getFile() . " Line: " . $e->getLine();
                    $debugInfo[] = "Stack trace: " . $e->getTraceAsString();
                } catch (Error $e) {
                    $message = '❌ Fatal Error: ' . $e->getMessage();
                    $messageClass = 'error';
                    $debugInfo[] = "Fatal Error: " . $e->getMessage();
                    $debugInfo[] = "Error File: " . $e->getFile() . " Line: " . $e->getLine();
                    $debugInfo[] = "Stack trace: " . $e->getTraceAsString();
                }
            }
        }
        ?>
        
        <?php if ($message): ?>
            <div class="result <?php echo $messageClass; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       placeholder="Your name"
                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="your@email.com"
                       required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <button type="submit">📮 Subscribe to Newsletter</button>
        </form>
        
        <?php if (!empty($debugInfo)): ?>
            <div class="result info" style="margin-top: 20px;">
                <strong>Debug Information:</strong>
                <pre><?php echo implode("\n", $debugInfo); ?></pre>
            </div>
        <?php endif; ?>
        
        <a href="/" style="display: inline-block; margin-top: 20px; color: #667eea; text-decoration: none;">← Back to Home</a>
    </div>
</body>
</html>
