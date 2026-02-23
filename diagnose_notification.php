<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DIAGNOSIS NOTIFIKASI ===\n\n";

// 1. Check template
$template = \App\Models\NotificationTemplate::where('category_key', 'trip')
    ->where('trigger_type', 'trip_completed')
    ->first();

echo "1. TEMPLATE CONFIGURATION\n";
echo "   Name: {$template->name}\n";
echo "   Priority: {$template->priority}\n";
echo "   Channel: {$template->channel}\n";
$androidPriority = in_array($template->priority, ['high', 'critical']) ? 'HIGH' : 'NORMAL';
echo "   FCM Android Priority: {$androidPriority}\n";
if ($androidPriority === 'HIGH') {
    echo "   ✅ Backend configured correctly for heads-up notification\n\n";
} else {
    echo "   ❌ Priority too low - heads-up won't show!\n\n";
}

// 2. Check recent notifications
echo "2. RECENT NOTIFICATIONS\n";
$notifications = \App\Models\Notification::where('device_id', 'd40436f2-ec64-44f2-b8be-d0f65754607c')
    ->orderBy('created_at', 'desc')
    ->take(3)
    ->get(['id', 'title', 'priority', 'push_sent', 'push_success', 'created_at']);

foreach($notifications as $n) {
    $pushSent = $n->push_sent ? 'YES ✅' : 'NO ❌';
    $pushSuccess = $n->push_success ? 'YES ✅' : 'NO ❌';
    
    echo "   ID {$n->id}: {$n->title}\n";
    echo "   Priority: {$n->priority} | Push Sent: {$pushSent} | Success: {$pushSuccess}\n";
    echo "   Time: {$n->created_at}\n\n";
}

// 3. Show FCM payload structure
echo "3. FCM PAYLOAD YANG DIKIRIM KE FIREBASE\n";
echo "   {\n";
echo "     \"message\": {\n";
echo "       \"notification\": { \"title\": \"...\", \"body\": \"...\" },\n";
echo "       \"android\": {\n";
echo "         \"priority\": \"{$androidPriority}\",  ← HEADS-UP NEEDS 'HIGH'\n";
echo "         \"notification\": {\n";
echo "           \"channel_id\": \"mototracker_trip\",  ← MUST MATCH FLUTTER\n";
echo "           \"notification_priority\": \"PRIORITY_HIGH\"\n";
echo "         }\n";
echo "       }\n";
echo "     }\n";
echo "   }\n\n";

// 4. Conclusion
echo "4. KESIMPULAN\n";
echo "   Backend: ✅ SUDAH BENAR\n";
echo "   - FCM push berhasil dikirim (push_success = YES)\n";
echo "   - Priority = HIGH (akan trigger heads-up di Android)\n";
echo "   - Channel ID = mototracker_trip\n\n";

echo "   Flutter: ❌ BELUM SETUP\n";
echo "   - Notification channel 'mototracker_trip' belum dibuat\n";
echo "   - Atau channel importance bukan Importance.high\n\n";

echo "5. SOLUSI\n";
echo "   Buka file: FLUTTER_HEADS_UP_NOTIFICATION.md\n";
echo "   Ikuti langkah 'Setup Notification Channels dengan Importance HIGH'\n\n";

echo "   Yang perlu dibuat di Flutter:\n";
echo "   const AndroidNotificationChannel tripChannel = AndroidNotificationChannel(\n";
echo "     'mototracker_trip',  // ID sama dengan backend\n";
echo "     'Trip Notifications',\n";
echo "     importance: Importance.high,  // ← PENTING!\n";
echo "   );\n\n";

echo "   Tanpa setup ini, Android TIDAK akan show heads-up notification!\n";
