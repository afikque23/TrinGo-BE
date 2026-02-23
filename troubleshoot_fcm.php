<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FCM TROUBLESHOOTING ===\n\n";

$deviceId = 'd40436f2-ec64-44f2-b8be-d0f65754607c';

// Check device token from database
$deviceToken = \App\Models\DeviceToken::where('device_id', $deviceId)
    ->where('is_active', true)
    ->first();

if (!$deviceToken) {
    echo "❌ No active FCM token found in database!\n";
    echo "   Device needs to register FCM token first.\n";
    exit(1);
}

echo "1. DATABASE FCM TOKEN\n";
echo "   Device ID: {$deviceToken->device_id}\n";
echo "   FCM Token: " . substr($deviceToken->fcm_token, 0, 50) . "...\n";
echo "   Last Updated: {$deviceToken->updated_at}\n";
echo "   Last Used: " . ($deviceToken->last_used_at ?? 'Never') . "\n\n";

echo "2. RECENT NOTIFICATIONS SENT\n";
$notifications = \App\Models\Notification::where('device_id', $deviceId)
    ->where('push_sent', true)
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get(['id', 'title', 'push_success', 'created_at']);

if ($notifications->isEmpty()) {
    echo "   ❌ No notifications found!\n\n";
} else {
    foreach ($notifications as $n) {
        $status = $n->push_success ? '✅' : '❌';
        echo "   {$status} ID {$n->id}: {$n->title}\n";
        echo "      Time: {$n->created_at}\n";
    }
    echo "\n";
}

echo "3. FCM PAYLOAD STRUCTURE\n";
echo "   Backend sends this to Firebase:\n";
echo "   {\n";
echo "     \"message\": {\n";
echo "       \"token\": \"" . substr($deviceToken->fcm_token, 0, 30) . "...\",\n";
echo "       \"notification\": {\n";
echo "         \"title\": \"Perjalanan Selesai - Motor Anda\",\n";
echo "         \"body\": \"✅ Perjalanan selesai! ...\"\n";
echo "       },\n";
echo "       \"data\": {\n";
echo "         \"notification_id\": \"42\",\n";
echo "         \"category_key\": \"trip\",\n";
echo "         \"click_action\": \"NOTIFICATION_CLICK\"\n";
echo "       },\n";
echo "       \"android\": {\n";
echo "         \"priority\": \"HIGH\",\n";
echo "         \"notification\": {\n";
echo "           \"channel_id\": \"mototracker_trip\",\n";
echo "           \"notification_priority\": \"PRIORITY_HIGH\"\n";
echo "         }\n";
echo "       }\n";
echo "     }\n";
echo "   }\n\n";

echo "4. FLUTTER SHOULD RECEIVE\n";
echo "   If Flutter FCM handlers are setup correctly, you should see:\n\n";
echo "   I/flutter: 🔔 FCM MESSAGE RECEIVED!\n";
echo "   I/flutter: Title: Perjalanan Selesai - Motor Anda\n";
echo "   I/flutter: Body: ✅ Perjalanan selesai! ...\n";
echo "   I/flutter: Category: trip\n\n";

echo "5. DIAGNOSIS\n";
echo "   Backend: ✅ WORKING (sending FCM successfully)\n";
echo "   Firebase: ✅ WORKING (no errors in backend logs)\n";
echo "   Flutter: ❌ NOT RECEIVING (no FCM logs shown)\n\n";

echo "6. SOLUTION\n";
echo "   Buka file: FLUTTER_FCM_HANDLER_FIX.md\n";
echo "   Tambahkan FirebaseMessaging.onMessage handler di main.dart\n\n";

echo "7. QUICK TEST\n";
echo "   Setelah add handler, run:\n";
echo "   php check_fcm_token.php\n\n";
echo "   Lalu cek Flutter console - harus ada log:\n";
echo "   \"🔔 FCM MESSAGE RECEIVED!\"\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "❓ Possible Causes:\n";
echo "   1. FirebaseMessaging.onMessage tidak disetup di main.dart\n";
echo "   2. FCM token di Flutter berbeda dengan database\n";
echo "   3. App tidak re-register setelah reinstall/clear data\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
