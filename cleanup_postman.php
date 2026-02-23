<?php
/**
 * Clean Postman Collection from Guest Mode references
 */

$file = 'Motorcycle_Management_API.postman_collection.json';
$content = file_get_contents($file);
$json = json_decode($content, true);

if (!$json) {
    die("❌ Error: Failed to parse JSON file. Error: " . json_last_error_msg() . "\n");
}

function cleanCollection(&$data) {
    // Remove X-Device-ID headers from all requests
    if (isset($data['request']['header']) && is_array($data['request']['header'])) {
        $data['request']['header'] = array_values(array_filter($data['request']['header'], function($header) {
            return !isset($header['key']) || $header['key'] !== 'X-Device-ID';
        }));
    }
    
    // Clean Login request body
    if (isset($data['request']['body']['raw'])) {
        $body = $data['request']['body']['raw'];
        // Remove device_id and device_name from Login
        if (strpos($body, '"password"') !== false && strpos($body, '"device_id"') !== false) {
            $bodyArray = json_decode($body, true);
            if ($bodyArray) {
                unset($bodyArray['device_id']);
                unset($bodyArray['device_name']);
                $data['request']['body']['raw'] = json_encode($bodyArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        }
        // Remove device_id, email, device_name from Refresh Token
        if (strpos($body, '"refresh_token"') !== false && (strpos($body, '"device_id"') !== false || strpos($body, '"email"') !== false)) {
            $bodyArray = json_decode($body, true);
            if ($bodyArray && isset($bodyArray['refresh_token'])) {
                $bodyArray = ['refresh_token' => $bodyArray['refresh_token']];
                $data['request']['body']['raw'] = json_encode($bodyArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        }
    }
    
    // Update descriptions
    if (isset($data['request']['description'])) {
        $data['request']['description'] = str_replace(
            'Support guest mode dengan X-Device-ID', 
            'Requires authentication with Bearer token', 
            $data['request']['description']
        );
        $data['request']['description'] = str_replace(
            'supports both authenticated & guest mode', 
            'requires authentication with Bearer token', 
            $data['request']['description']
        );
        $data['request']['description'] = str_replace(
            'Panggil saat app pertama dibuka, token di-refresh, atau user login. Support guest mode dengan X-Device-ID.', 
            'Register FCM token saat user login atau token di-refresh. Requires authentication.', 
            $data['request']['description']
        );
    }
    
    // Recurse through items
    if (isset($data['item']) && is_array($data['item'])) {
        foreach ($data['item'] as &$item) {
            cleanCollection($item);
        }
    }
}

// Clean the collection
cleanCollection($json);

// Update metadata description if needed
if (isset($json['info']['description'])) {
    $json['info']['description'] = str_replace(
        'All endpoints require authentication. Guest mode has been removed.',
        'All data endpoints require authentication. Guest mode has been completely removed.',
        $json['info']['description']
    );
}

// Save cleaned collection
$output = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if (!$output) {
    die("❌ Error: Failed to encode JSON. Error: " . json_last_error_msg() . "\n");
}

$result = file_put_contents($file, $output);

if ($result === false) {
    die("❌ Error: Failed to write file.\n");
}

echo "✅ Postman collection cleaned successfully!\n";
echo "- Removed all X-Device-ID headers\n";
echo "- Removed device_id from Login & Refresh Token requests\n";
echo "- Updated descriptions to remove guest mode references\n";
