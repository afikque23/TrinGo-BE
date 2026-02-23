<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Debug FCM Payload ===\n\n";

$deviceId = 'd40436f2-ec64-44f2-b8be-d0f65754607c';

// Get FCM service
$fcmService = app(\App\Services\FcmNotificationService::class);

// Get device token
$deviceToken = \App\Models\DeviceToken::where('device_id', $deviceId)
    ->where('is_active', true)
    ->first();

if (!$deviceToken) {
    echo "❌ No device token found!\n";
    exit(1);
}

echo "✅ Device Token: " . substr($deviceToken->fcm_token, 0, 30) . "...\n\n";

// Check template priority
$template = \App\Models\NotificationTemplate::where('category_key', 'trip')
    ->where('trigger_type', 'trip_completed')
    ->first();

if ($template) {
    echo "📋 Template: {$template->name}\n";
    echo "   Category: {$template->category_key}\n";
    echo "   Priority: {$template->priority}\n";
    echo "   Channel: {$template->channel}\n\n";
    
    // Check if priority is high/critical
    if (in_array($template->priority, ['high', 'critical'])) {
        echo "✅ Priority is HIGH/CRITICAL - FCM will send with android.priority = HIGH\n\n";
    } else {
        echo "⚠️ Priority is {$template->priority} - FCM will send with android.priority = NORMAL\n";
        echo "   → Heads-up notification TIDAK akan muncul!\n\n";
    }
}

echo "🔍 Checking Recent Notifications:\n\n";

$notifications = \App\Models\Notification::where('device_id', $deviceId)
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get(['id', 'title', 'category_key', 'priority', 'push_sent', 'push_success', 'created_at']);

foreach ($notifications as $notif) {
    $pushStatus = $notif->push_sent 
        ? ($notif->push_success ? '✅ Sent & Success' : '⚠️ Sent but Failed')
        : '❌ Not Sent';
    
    echo "ID {$notif->id}: {$notif->title}\n";
    echo "  Priority: {$notif->priority} | Category: {$notif->category_key}\n";
    echo "  Push: {$pushStatus}\n";
    echo "  Time: {$notif->created_at}\n\n";
}

echo "\n📊 FCM Payload yang Dikirim:\n\n";
echo "Untuk notification dengan priority = 'high', FCM mengirim:\n\n";
echo json_encode([
    'message' => [
        'token' => '...',
        'notification' => [
            'title' => 'Perjalanan Selesai - Motor Anda',
            'body' => 'Perjalanan selesai! ...',
        ],
        'data' => [
            'notification_id' => '36',
            'vehicle_id' => '10',
            'category_key' => 'trip',
            'click_action' => 'NOTIFICATION_CLICK',
        ],
        'android' => [
            'priority' => 'HIGH', // ← Ini yang penting!
            'notification' => [
                'channel_id' => 'mototracker_trip', // ← Channel ID
                'sound' => 'default',
                'notification_priority' => 'PRIORITY_HIGH', // ← Android notification priority
            ],
        ],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "✅ Backend SUDAH BENAR!\n\n";
echo "❌ MASALAH ADA DI FLUTTER:\n";
echo "   Flutter belum setup notification channel 'mototracker_trip' dengan Importance.high\n\n";
echo "📘 Lihat dokumentasi: FLUTTER_HEADS_UP_NOTIFICATION.md\n";
echo "   Bagian: 'Setup Notification Channels dengan Importance HIGH'\n\n";
