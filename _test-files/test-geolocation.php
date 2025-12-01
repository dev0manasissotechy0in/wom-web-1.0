<?php
/**
 * Test Geolocation API
 * Verify IP to location conversion is working
 */

require_once __DIR__ . '/classes/GeoLocation.php';

// Test with different IPs
$testIPs = [
    '8.8.8.8',           // Google DNS - USA
    '1.1.1.1',           // Cloudflare - USA
    '208.67.222.222',    // OpenDNS - USA
    '185.228.168.9',     // Example EU IP
    '202.12.29.175',     // Example Asia IP
    '127.0.0.1',         // Localhost
    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'  // Your actual IP
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geolocation Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
        }

        .test-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .ip-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .ip-address {
            font-size: 20px;
            font-weight: 700;
            color: #000;
            font-family: 'Courier New', monospace;
        }

        .location-data {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .data-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #000;
        }

        .data-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .data-value {
            font-size: 16px;
            color: #000;
            font-weight: 600;
        }

        .formatted-location {
            background: #000;
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
            margin-top: 15px;
        }

        .formatted-location i {
            margin-right: 10px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 30px;
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            color: #0c5460;
        }

        .success {
            background: #d4edda;
            border-left-color: #155724;
            color: #155724;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        @media (max-width: 768px) {
            .location-data {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌍 Geolocation API Test</h1>
        <p class="subtitle">Testing IP to Location Conversion</p>

        <div class="alert success">
            <strong>✓ GeoLocation Class Loaded Successfully</strong><br>
            Using ip-api.com free API (45 requests/minute limit)
        </div>

        <div class="alert">
            <strong>📍 Testing Multiple IP Addresses:</strong><br>
            Converting IP addresses to city/location names for newsletter subscribers
        </div>

        <?php foreach ($testIPs as $ip): ?>
            <?php
            $startTime = microtime(true);
            $location = GeoLocation::getLocation($ip);
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            ?>

            <div class="test-card">
                <div class="ip-header">
                    <span class="ip-address">🔹 <?php echo htmlspecialchars($ip); ?></span>
                    <span style="color: #666; font-size: 14px;">
                        Response: <?php echo $responseTime; ?>ms
                    </span>
                </div>

                <div class="location-data">
                    <div class="data-item">
                        <div class="data-label">City</div>
                        <div class="data-value">
                            <?php echo htmlspecialchars($location['city'] ?: 'N/A'); ?>
                        </div>
                    </div>

                    <div class="data-item">
                        <div class="data-label">Region</div>
                        <div class="data-value">
                            <?php echo htmlspecialchars($location['region'] ?: 'N/A'); ?>
                        </div>
                    </div>

                    <div class="data-item">
                        <div class="data-label">Country</div>
                        <div class="data-value">
                            <?php echo htmlspecialchars($location['country'] ?: 'N/A'); ?>
                        </div>
                    </div>

                    <div class="data-item">
                        <div class="data-label">Country Code</div>
                        <div class="data-value">
                            <?php echo htmlspecialchars($location['country_code'] ?: 'N/A'); ?>
                        </div>
                    </div>

                    <?php if (isset($location['lat']) && $location['lat']): ?>
                    <div class="data-item">
                        <div class="data-label">Coordinates</div>
                        <div class="data-value" style="font-size: 14px;">
                            <?php echo htmlspecialchars($location['lat']); ?>, 
                            <?php echo htmlspecialchars($location['lon']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="formatted-location">
                    <i>📍</i> <?php echo htmlspecialchars($location['formatted']); ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="alert" style="margin-top: 30px;">
            <strong>💡 How It Works:</strong><br>
            1. When someone subscribes to newsletter, their IP address is captured<br>
            2. GeoLocation class calls ip-api.com API to convert IP → Location<br>
            3. Location is stored as "City, Region, Country" format<br>
            4. Admin dashboard displays location instead of raw IP address<br>
            5. IP address is kept as tooltip for reference
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="test-newsletter.php" style="display: inline-block; background: #000; color: white; padding: 15px 40px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                Test Newsletter Subscription →
            </a>
        </div>
    </div>
</body>
</html>
