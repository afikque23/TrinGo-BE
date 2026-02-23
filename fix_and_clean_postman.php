<?php
/**
 * Fix JSON encoding issues and clean Postman collection from guest mode
 */

$file = 'Motorcycle_Management_API.postman_collection.json';

// Step 1: Fix file encoding and remove control characters
echo "Step 1: Fixing file encoding...\n";
$content = file_get_contents($file);
$content = trim($content);
// Remove invalid control characters but keep newlines, tabs, carriage returns
$content = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F-\xFF]/', '', $content);

$json = json_decode($content, true);
if (!$json) {
    die("❌ Error: Failed to parse JSON after cleaning. Error: " . json_last_error_msg() . "\n");
}
echo "✅ JSON parsed successfully! Collection has " . count($json['item']) . " sections.\n\n";

// Step 2: Clean guest mode references
echo "Step 2: Cleaning guest mode references...\n";

function cleanCollection(&$data) {
    // Remove X-Device-ID headers from all requests
    if (isset($data['request']['header']) && is_array($data['request']['header'])) {
        $originalCount = count($data['request']['header']);
        $data['request']['header'] = array_values(array_filter($data['request']['header'], function($header) {
            return !isset($header['key']) || $header['key'] !== 'X-Device-ID';
        }));
        if (count($data['request']['header']) < $originalCount) {
            echo "  - Removed X-Device-ID header from " . ($data['name'] ?? 'unnamed request') . "\n";
        }
    }
    
    // Clean Login request body
    if (isset($data['request']['body']['raw'])) {
        $body = $data['request']['body']['raw'];
        $modified = false;
        
        // Remove device_id and device_name from Login
        if (strpos($body, '"password"') !== false && strpos($body, '"device_id"') !== false) {
            $bodyArray = json_decode($body, true);
            if ($bodyArray) {
                unset($bodyArray['device_id']);
                unset($bodyArray['device_name']);
                $data['request']['body']['raw'] = json_encode($bodyArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $modified = true;
                echo "  - Cleaned Login request body\n";
            }
        }
        
        // Remove device_id, email, device_name from Refresh Token
        if (strpos($body, '"refresh_token"') !== false && (strpos($body, '"device_id"') !== false || strpos($body, '"email"') !== false)) {
            $bodyArray = json_decode($body, true);
            if ($bodyArray && isset($bodyArray['refresh_token'])) {
                $bodyArray = ['refresh_token' => $bodyArray['refresh_token']];
                $data['request']['body']['raw'] = json_encode($bodyArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $modified = true;
                echo "  - Cleaned Refresh Token request body\n";
            }
        }
    }
    
    // Update descriptions
    if (isset($data['request']['description'])) {
        $oldDesc = $data['request']['description'];
        
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
        
        if ($oldDesc !== $data['request']['description']) {
            echo "  - Updated description for " . ($data['name'] ?? 'unnamed request') . "\n";
        }
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

// Step 3: Remove device_id from collection variables
if (isset($json['variable']) && is_array($json['variable'])) {
    $originalCount = count($json['variable']);
    $json['variable'] = array_values(array_filter($json['variable'], function($var) {
        return !isset($var['key']) || $var['key'] !== 'device_id';
    }));
    if (count($json['variable']) < $originalCount) {
        echo "  - Removed device_id from collection variables\n";
    }
}

// Update collection metadata
if (isset($json['info']['description'])) {
    $json['info']['description'] = str_replace(
        'All endpoints require authentication. Guest mode has been removed.',
        'All data endpoints require authentication. Guest mode has been completely removed.',
        $json['info']['description']
    );
}

// Step 4: Save cleaned collection
echo "\nStep 3: Saving cleaned collection...\n";
$output = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if (!$output) {
    die("❌ Error: Failed to encode JSON. Error: " . json_last_error_msg() . "\n");
}

$result = file_put_contents($file, $output);

if ($result === false) {
    die("❌ Error: Failed to write file.\n");
}

echo "✅ File saved successfully! (" . number_format($result) . " bytes)\n\n";

// Step 5: Verify the result
echo "Step 4: Verifying result...\n";
$verify = json_decode(file_get_contents($file), true);
if ($verify) {
    echo "✅ Collection is valid JSON with " . count($verify['item']) . " sections\n";
    echo "✅ Collection variables: " . count($verify['variable']) . "\n";
} else {
    echo "❌ Verification failed!\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ CLEANUP COMPLETE!\n";
echo str_repeat("=", 60) . "\n";
echo "Summary of changes:\n";
echo "- Removed invalid control characters from file\n";
echo "- Removed all X-Device-ID headers\n";
echo "- Cleaned Login & Refresh Token request bodies\n";
echo "- Updated descriptions to remove guest mode references\n";
echo "- Removed device_id from collection variables\n";
echo "\nThe collection is now ready to use with persistent login!\n";
