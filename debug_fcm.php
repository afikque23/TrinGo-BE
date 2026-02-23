<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Debug FCM Configuration ===\n\n";

// Check config
$fcmConfig = config('services.fcm');
echo "FCM Config:\n";
echo "  credentials_path (from config): " . ($fcmConfig['credentials_path'] ?? 'NOT SET') . "\n";
echo "  project_id: " . ($fcmConfig['project_id'] ?? 'NOT SET') . "\n\n";

// Check file existence
$credentialsPath = $fcmConfig['credentials_path'] ?? null;
if ($credentialsPath) {
    echo "Checking file existence:\n";
    echo "  Path: $credentialsPath\n";
    echo "  File exists: " . (file_exists($credentialsPath) ? 'YES ✅' : 'NO ❌') . "\n";
    echo "  Is readable: " . (is_readable($credentialsPath) ? 'YES ✅' : 'NO ❌') . "\n";
    
    if (file_exists($credentialsPath)) {
        echo "  File size: " . filesize($credentialsPath) . " bytes\n";
        
        $contents = json_decode(file_get_contents($credentialsPath), true);
        echo "  Valid JSON: " . ($contents ? 'YES ✅' : 'NO ❌') . "\n";
        if ($contents) {
            echo "  Has private_key: " . (isset($contents['private_key']) ? 'YES ✅' : 'NO ❌') . "\n";
            echo "  Has client_email: " . (isset($contents['client_email']) ? 'YES ✅' : 'NO ❌') . "\n";
            echo "  Project ID in file: " . ($contents['project_id'] ?? 'NOT SET') . "\n";
        }
    }
} else {
    echo "❌ credentials_path is NULL!\n";
}

echo "\n=== Testing FcmNotificationService ===\n\n";

try {
    $fcmService = app(\App\Services\FcmNotificationService::class);
    echo "FcmNotificationService instantiated\n";
    echo "Is configured: " . ($fcmService->isConfigured() ? 'YES ✅' : 'NO ❌') . "\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
