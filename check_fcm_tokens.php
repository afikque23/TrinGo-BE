<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Users with Active FCM Tokens ===\n\n";

$usersWithTokens = App\Models\DeviceToken::where('is_active', true)
    ->select('user_id')
    ->distinct()
    ->pluck('user_id');

if ($usersWithTokens->count() > 0) {
    foreach ($usersWithTokens as $userId) {
        $user = App\Models\User::find($userId);
        if (!$user) continue;
        
        $tokens = App\Models\DeviceToken::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
        
        echo "- {$user->name} ({$user->email})\n";
        echo "  Total active tokens: {$tokens->count()}\n";
        foreach ($tokens as $token) {
            echo "    • " . substr($token->fcm_token, 0, 30) . "... (Platform: {$token->platform})\n";
        }
        echo "\n";
    }
} else {
    echo "❌ TIDAK ADA user dengan FCM token aktif!\n\n";
    echo "Untuk test push notification, pastikan:\n";
    echo "1. User sudah login ke mobile app (Android/iOS)\n";
    echo "2. Mobile app sudah implement FCM token registration\n";
    echo "3. Token dikirim ke endpoint POST /api/device-token\n\n";
}

echo "\n=== All Users in System ===\n\n";
$allUsers = App\Models\User::all();
foreach ($allUsers as $user) {
    $tokenCount = App\Models\DeviceToken::where('user_id', $user->id)
        ->where('is_active', true)
        ->count();
    echo "- {$user->name} ({$user->email}) - {$tokenCount} token(s)\n";
}
