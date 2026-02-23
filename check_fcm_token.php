<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Check FCM Token for Device ===\n\n";

$deviceId = 'd40436f2-ec64-44f2-b8be-d0f65754607c';

// Cek device token
$deviceToken = \App\Models\DeviceToken::where('device_id', $deviceId)
    ->where('is_active', true)
    ->first();

if ($deviceToken) {
    echo "✅ Device Token Found:\n";
    echo "   ID: {$deviceToken->id}\n";
    echo "   Device ID: {$deviceToken->device_id}\n";
    echo "   FCM Token: " . substr($deviceToken->fcm_token, 0, 50) . "...\n";
    echo "   Is Active: " . ($deviceToken->is_active ? 'YES' : 'NO') . "\n";
    echo "   User ID: " . ($deviceToken->user_id ?? 'NULL (Guest)') . "\n";
    echo "   Created: {$deviceToken->created_at}\n";
    echo "   Updated: {$deviceToken->updated_at}\n\n";
    
    // Test send push directly
    echo "📤 Testing Direct FCM Push...\n\n";
    
    $fcmService = app(\App\Services\FcmNotificationService::class);
    
    if (!$fcmService->isConfigured()) {
        echo "❌ FCM not configured!\n";
        exit(1);
    }
    
    echo "✅ FCM is configured\n";
    
    try {
        $result = $fcmService->sendToDeviceId(
            $deviceId,
            'Test Push Notification 🔔',
            'Ini test push notification langsung dari FCM service. Tap untuk buka app.',
            [
                'category' => 'test',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        );
        
        echo "\n✅ FCM Push Result:\n";
        echo "   Success: " . ($result ? 'YES ✅' : 'NO ❌') . "\n";
        
        if ($result) {
            echo "\n🎉 Push notification berhasil dikirim!\n";
            echo "   Cek device Android sekarang - seharusnya muncul pop-up notification!\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} else {
    echo "❌ No FCM Token found for device_id: $deviceId\n";
    echo "\n📋 Available device tokens:\n";
    
    $allTokens = \App\Models\DeviceToken::where('is_active', true)->get();
    
    if ($allTokens->isEmpty()) {
        echo "   (No active device tokens)\n";
    } else {
        foreach ($allTokens as $token) {
            echo "   - Device: {$token->device_id}\n";
            echo "     Token: " . substr($token->fcm_token, 0, 30) . "...\n\n";
        }
    }
}
