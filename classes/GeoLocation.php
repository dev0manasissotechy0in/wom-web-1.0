<?php
/**
 * IP Geolocation Helper
 * Gets city and country from IP address using free API
 */

class GeoLocation {
    
    /**
     * Get location details from IP address
     * Uses ip-api.com (free, no API key required, 45 requests/minute)
     * 
     * @param string $ip IP address to lookup
     * @return array Location data: ['city' => '', 'country' => '', 'formatted' => '']
     */
    public static function getLocation($ip) {
        // Skip localhost/private IPs
        if (empty($ip) || $ip === '::1' || $ip === '127.0.0.1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
            return [
                'city' => 'Local',
                'country' => 'Development',
                'country_code' => 'DEV',
                'region' => '',
                'lat' => null,
                'lon' => null,
                'formatted' => 'Local (Development)'
            ];
        }
        
        try {
            // Use ip-api.com - Free tier: 45 requests per minute
            $url = "http://ip-api.com/json/{$ip}?fields=status,country,countryCode,region,regionName,city,lat,lon";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return self::getDefaultLocation($ip);
            }
            
            $data = json_decode($response, true);
            
            if ($data && isset($data['status']) && $data['status'] === 'success') {
                $city = $data['city'] ?? '';
                $region = $data['regionName'] ?? '';
                $country = $data['country'] ?? '';
                
                // Format: "City, Region, Country" or "City, Country" or just "Country"
                $formatted = self::formatLocation($city, $region, $country);
                
                return [
                    'city' => $city,
                    'region' => $region,
                    'country' => $country,
                    'country_code' => $data['countryCode'] ?? '',
                    'lat' => $data['lat'] ?? null,
                    'lon' => $data['lon'] ?? null,
                    'formatted' => $formatted
                ];
            }
            
            return self::getDefaultLocation($ip);
            
        } catch (Exception $e) {
            error_log("GeoLocation error for IP {$ip}: " . $e->getMessage());
            return self::getDefaultLocation($ip);
        }
    }
    
    /**
     * Format location string
     */
    private static function formatLocation($city, $region, $country) {
        $parts = array_filter([$city, $region, $country]);
        
        if (empty($parts)) {
            return 'Unknown';
        }
        
        // If all three exist and city != region
        if ($city && $region && $country && $city !== $region) {
            return "{$city}, {$region}, {$country}";
        }
        
        // If city and country (skip region if same as city)
        if ($city && $country) {
            return "{$city}, {$country}";
        }
        
        // Just country
        if ($country) {
            return $country;
        }
        
        return 'Unknown';
    }
    
    /**
     * Get default location when API fails
     */
    private static function getDefaultLocation($ip) {
        return [
            'city' => '',
            'region' => '',
            'country' => 'Unknown',
            'country_code' => '',
            'lat' => null,
            'lon' => null,
            'formatted' => 'Unknown'
        ];
    }
    
    /**
     * Alternative: Use ipinfo.io (requires free API token)
     * Sign up at: https://ipinfo.io/signup
     */
    public static function getLocationIPInfo($ip, $apiToken = '') {
        if (empty($apiToken)) {
            return self::getLocation($ip); // Fallback to ip-api
        }
        
        try {
            $url = "https://ipinfo.io/{$ip}/json?token={$apiToken}";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return self::getDefaultLocation($ip);
            }
            
            $data = json_decode($response, true);
            
            if ($data && isset($data['city'])) {
                return [
                    'city' => $data['city'] ?? '',
                    'region' => $data['region'] ?? '',
                    'country' => $data['country'] ?? '',
                    'formatted' => ($data['city'] ?? '') . ', ' . ($data['country'] ?? '')
                ];
            }
            
            return self::getDefaultLocation($ip);
            
        } catch (Exception $e) {
            error_log("IPInfo error for IP {$ip}: " . $e->getMessage());
            return self::getDefaultLocation($ip);
        }
    }
}
