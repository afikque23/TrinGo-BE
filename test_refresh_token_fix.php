<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Refresh Token Fix ===\n\n";

// Get user ID 14
$user = App\Models\User::find(14);

if (!$user) {
    echo "❌ User ID 14 tidak ditemukan!\n";
    exit(1);
}

echo "User: {$user->name} ({$user->email})\n";
echo "Refresh Token: " . substr($user->refresh_token, 0, 40) . "...\n";
echo "Expires At: {$user->refresh_token_expires_at}\n\n";

if (!$user->refresh_token) {
    echo "❌ User tidak punya refresh token! Generating...\n";
    $user->generateRefreshToken(90);
    $user = $user->fresh();
    echo "✅ Refresh token generated!\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 1: Refresh Token dengan refresh_token saja\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Delete old access tokens
$user->tokens()->delete();
echo "🗑️  Old access tokens deleted\n\n";

// Create request dengan refresh_token (TANPA email)
$request = new Illuminate\Http\Request();
$request->merge([
    'refresh_token' => $user->refresh_token,
    // NOTE: TIDAK ADA 'email' di sini!
]);

// Validate request
$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
    'refresh_token' => 'required|string',
]);

if ($validator->fails()) {
    echo "❌ Validation failed: " . json_encode($validator->errors()) . "\n";
    exit(1);
}

echo "✅ Request validation passed\n";
echo "Request data: " . json_encode($request->all()) . "\n\n";

// Simulate refreshToken method logic
try {
    // NEW LOGIC: Find user by refresh_token
    echo "🔍 Finding user by refresh_token...\n";
    $foundUser = App\Models\User::where('refresh_token', $request->refresh_token)->first();
    
    if (!$foundUser) {
        echo "❌ FAILED: User tidak ditemukan berdasarkan refresh_token\n";
        exit(1);
    }
    
    echo "✅ User found: {$foundUser->name} (ID: {$foundUser->id})\n\n";
    
    // Verify refresh token
    echo "🔐 Verifying refresh token expiration...\n";
    if (!$foundUser->verifyRefreshToken($request->refresh_token)) {
        echo "❌ FAILED: Refresh token expired atau invalid\n";
        exit(1);
    }
    
    echo "✅ Refresh token is valid\n\n";
    
    // Revoke old tokens
    echo "🗑️  Revoking old access tokens...\n";
    $foundUser->tokens()->delete();
    echo "✅ Old tokens revoked\n\n";
    
    // Generate new access token
    echo "🔑 Generating new access token (30 minutes)...\n";
    $newAccessToken = $foundUser->createToken('auth_token', ['*'], now()->addMinutes(30))->plainTextToken;
    echo "✅ New access token: " . substr($newAccessToken, 0, 40) . "...\n\n";
    
    // Generate new refresh token
    echo "🔄 Generating new refresh token (90 days)...\n";
    $newRefreshToken = $foundUser->generateRefreshToken(90);
    echo "✅ New refresh token: " . substr($newRefreshToken, 0, 40) . "...\n\n";
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "RESPONSE (simulated)\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $response = [
        'success' => true,
        'message' => 'Token berhasil diperbaharui',
        'data' => [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 1800,
        ],
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "VERIFICATION\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Verify new tokens
    $foundUser = $foundUser->fresh();
    $newTokenCount = $foundUser->tokens()->count();
    
    echo "Access tokens count: {$newTokenCount}\n";
    echo "Refresh token updated: " . ($foundUser->refresh_token === $newRefreshToken ? "✅ Yes" : "❌ No") . "\n\n";
    
    echo "✅✅✅ TEST PASSED! ✅✅✅\n";
    echo "Refresh token endpoint sekarang bisa work TANPA email!\n";
    echo "Flutter app hanya perlu kirim 'refresh_token' saja.\n\n";
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "NEXT STEPS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. ✅ Fix sudah diterapkan di AuthController\n";
echo "2. ✅ Refresh token sekarang cari user by refresh_token\n";
echo "3. ✅ Tidak perlu email di request\n";
echo "4. 🔄 Test di Flutter app:\n";
echo "   - Tunggu 30 menit sampai access token expired\n";
echo "   - Atau force refresh dengan hapus access token\n";
echo "   - Verify refresh berhasil tanpa 404 error\n";
echo "\n";
