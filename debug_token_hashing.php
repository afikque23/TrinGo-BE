<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Token Hashing Issue ===\n\n";

$user = App\Models\User::find(14);

echo "User: {$user->name}\n";
echo "Refresh Token (DB): {$user->refresh_token}\n";
echo "Token length: " . strlen($user->refresh_token) . " characters\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TOKEN ANALYSIS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$tokenLength = strlen($user->refresh_token);

if ($tokenLength === 64) {
    echo "✅ Token length is 64 chars (SHA256 hash)\n";
    echo "This is the HASHED token stored in database\n\n";
    echo "⚠️  PROBLEM: Client menyimpan HASHED token!\n";
    echo "Client seharusnya menyimpan PLAIN TEXT token.\n\n";
} elseif ($tokenLength === 128) {
    echo "✅ Token length is 128 chars (plain text from bin2hex(random_bytes(64)))\n";
    echo "This is the PLAIN TEXT token (SHOULD BE HASHED!)\n\n";
    echo "⚠️  PROBLEM: Token di database tidak di-hash!\n";
    echo "Database seharusnya menyimpan SHA256 hash (64 chars).\n\n";
} else {
    echo "❓ Token length is {$tokenLength} chars (unexpected!)\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "SOLUTION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Regenerate refresh token untuk user ID 14...\n";

// Generate new token dengan method yang benar
$newPlainToken = $user->generateRefreshToken(90);
$user = $user->fresh();

echo "✅ New token generated!\n\n";
echo "Plain text token (for client): {$newPlainToken}\n";
echo "Plain token length: " . strlen($newPlainToken) . " chars\n\n";
echo "Hashed token (in DB): {$user->refresh_token}\n";
echo "Hashed token length: " . strlen($user->refresh_token) . " chars\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "VERIFICATION TEST\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test 1: Verify dengan plain token (CORRECT)
echo "Test 1: verifyRefreshToken() dengan PLAIN text token\n";
$valid1 = $user->verifyRefreshToken($newPlainToken);
echo "Result: " . ($valid1 ? "✅ VALID" : "❌ INVALID") . "\n\n";

// Test 2: Verify dengan hashed token (WRONG)
echo "Test 2: verifyRefreshToken() dengan HASHED token (dari DB)\n";
$valid2 = $user->verifyRefreshToken($user->refresh_token);
echo "Result: " . ($valid2 ? "❌ VALID (seharusnya INVALID!)" : "✅ INVALID (correct!)") . "\n\n";

if ($valid1 && !$valid2) {
    echo "✅✅✅ VERIFICATION LOGIC BENAR! ✅✅✅\n\n";
    echo "Kesimpulan:\n";
    echo "1. Client harus simpan PLAIN TEXT token (128 chars)\n";
    echo "2. Database simpan HASHED token (64 chars SHA256)\n";
    echo "3. Saat refresh, client kirim PLAIN TEXT token\n";
    echo "4. Backend hash token dari client, lalu compare dengan DB\n\n";
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "NEXT STEPS UNTUK FLUTTER APP\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "User ID 14 perlu LOGIN ULANG untuk mendapat token baru!\n\n";
    echo "Atau update manual refresh_token di Flutter app:\n";
    echo "  Plain token: {$newPlainToken}\n\n";
    echo "Setelah itu, refresh token akan work!\n";
} else {
    echo "❌ Verification logic error!\n";
}

echo "\n";
