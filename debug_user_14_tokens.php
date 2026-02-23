<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Refresh Token Issue - User ID 14 ===\n\n";

// Cek user ID 14
$user = App\Models\User::find(14);

if (!$user) {
    echo "❌ User ID 14 tidak ditemukan di database!\n\n";
    
    // List semua user
    echo "User yang tersedia:\n";
    $users = App\Models\User::all();
    foreach ($users as $u) {
        echo "  - ID: {$u->id}, Name: {$u->name}, Email: {$u->email}\n";
    }
    exit(1);
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "USER INFORMATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ID         : {$user->id}\n";
echo "Name       : {$user->name}\n";
echo "Email      : {$user->email}\n";
echo "Is Active  : " . ($user->is_active ? 'Yes' : 'No') . "\n";
echo "Created    : {$user->created_at}\n";
echo "Last Login : " . ($user->last_login_at ?? 'Never') . "\n";
echo "\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "REFRESH TOKEN STATUS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!$user->refresh_token) {
    echo "❌ TIDAK ADA refresh token!\n";
    echo "User harus login ulang untuk mendapat refresh token baru.\n\n";
} else {
    echo "✅ Refresh token ADA\n";
    echo "Token (first 40 chars): " . substr($user->refresh_token, 0, 40) . "...\n";
    echo "Expires At : " . ($user->refresh_token_expires_at ?? 'Not set') . "\n";
    
    // Check if expired
    if ($user->refresh_token_expires_at) {
        $expiresAt = \Carbon\Carbon::parse($user->refresh_token_expires_at);
        $now = \Carbon\Carbon::now();
        
        if ($expiresAt->isPast()) {
            echo "❌ Status: EXPIRED (expired " . $expiresAt->diffForHumans() . ")\n";
        } else {
            echo "✅ Status: VALID (expires " . $expiresAt->diffForHumans() . ")\n";
        }
    } else {
        echo "⚠️  Status: No expiration date set\n";
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ACCESS TOKENS (Sanctum)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$tokens = $user->tokens()->get();
echo "Total access tokens: {$tokens->count()}\n\n";

if ($tokens->count() > 0) {
    foreach ($tokens as $token) {
        echo "  • ID: {$token->id}\n";
        echo "    Name: {$token->name}\n";
        echo "    Token: " . substr($token->token, 0, 20) . "...\n";
        echo "    Created: {$token->created_at}\n";
        
        if ($token->expires_at) {
            $expiresAt = \Carbon\Carbon::parse($token->expires_at);
            $now = \Carbon\Carbon::now();
            
            if ($expiresAt->isPast()) {
                echo "    Status: ❌ EXPIRED (" . $expiresAt->diffForHumans() . ")\n";
            } else {
                echo "    Status: ✅ VALID (expires in " . $expiresAt->diffForHumans() . ")\n";
            }
        } else {
            echo "    Status: ⚠️  No expiration\n";
        }
        echo "\n";
    }
} else {
    echo "❌ Tidak ada access token!\n";
    echo "User perlu login untuk mendapat access token.\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "FCM TOKENS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$fcmTokens = App\Models\DeviceToken::where('user_id', $user->id)
    ->where('is_active', true)
    ->get();

echo "Total FCM tokens: {$fcmTokens->count()}\n\n";

if ($fcmTokens->count() > 0) {
    foreach ($fcmTokens as $token) {
        echo "  • Platform: {$token->platform}\n";
        echo "    Token: " . substr($token->fcm_token, 0, 30) . "...\n";
        echo "    Last used: " . ($token->last_used_at ?? 'Never') . "\n\n";
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "DIAGNOSIS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$issues = [];

if (!$user->refresh_token) {
    $issues[] = "❌ User tidak punya refresh token";
}

if ($user->refresh_token && $user->refresh_token_expires_at) {
    $expiresAt = \Carbon\Carbon::parse($user->refresh_token_expires_at);
    if ($expiresAt->isPast()) {
        $issues[] = "❌ Refresh token sudah expired";
    }
}

if ($tokens->count() === 0) {
    $issues[] = "❌ User tidak punya access token";
}

if (count($issues) > 0) {
    echo "Masalah yang ditemukan:\n";
    foreach ($issues as $issue) {
        echo "  {$issue}\n";
    }
    echo "\n";
    echo "🔧 SOLUSI:\n";
    echo "User harus login ulang untuk mendapat token baru.\n";
    echo "Atau jalankan: php artisan tinker\n";
    echo "Lalu: \$user = User::find(14); \$user->generateRefreshToken(90);\n";
} else {
    echo "✅ Tidak ada masalah dengan token user ini.\n";
    echo "\n";
    echo "🔍 Kemungkinan masalah:\n";
    echo "1. Flutter app tidak mengirim 'email' di request refresh token\n";
    echo "2. Endpoint refresh token butuh 'email' padahal seharusnya cukup 'refresh_token'\n";
    echo "3. Logic refresh token perlu diperbaiki\n";
}

echo "\n";
