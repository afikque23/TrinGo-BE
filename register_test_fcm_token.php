<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Register FCM Token untuk Testing ===\n\n";

// Tampilkan semua user dengan ID
echo "User yang tersedia:\n";
$users = App\Models\User::all();
foreach ($users as $u) {
    echo "  {$u->id}. {$u->name} ({$u->email})\n";
}

echo "\n";

// Pilih user (ubah angka di bawah sesuai user ID yang ingin di-test)
$userId = 14; // Default ke user ID 4 (afiquee)

$user = App\Models\User::find($userId);

if (!$user) {
    echo "❌ User ID '{$userId}' tidak ditemukan!\n";
    exit(1);
}

echo "Target: ID #{$user->id} - {$user->name} ({$user->email})\n\n";

// Generate dummy FCM token untuk testing
$dummyToken = 'fakeToken_' . time() . '_' . bin2hex(random_bytes(32));

// Hapus token lama jika ada
$oldTokens = App\Models\DeviceToken::where('user_id', $user->id)->count();
if ($oldTokens > 0) {
    App\Models\DeviceToken::where('user_id', $user->id)->delete();
    echo "🗑️  Menghapus {$oldTokens} token lama...\n";
}

// Register token baru
$token = App\Models\DeviceToken::create([
    'user_id' => $user->id,
    'fcm_token' => $dummyToken,
    'platform' => 'android',
    'app_version' => '1.0.0-test',
    'is_active' => true,
    'last_used_at' => now(),
]);

echo "✅ FCM Token berhasil diregister!\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "User ID    : {$user->id}\n";
echo "Nama       : {$user->name}\n";
echo "Email      : {$user->email}\n";
echo "Token      : " . substr($dummyToken, 0, 40) . "...\n";
echo "Platform   : android\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "⚠️  CATATAN PENTING:\n";
echo "Token ini adalah DUMMY TOKEN untuk testing flow sistem.\n";
echo "Push notification TIDAK AKAN sampai ke device fisik.\n\n";

echo "Yang akan terjadi:\n";
echo "  ✅ Notifikasi tersimpan di database\n";
echo "  ✅ Sistem coba kirim ke FCM (akan ditolak karena token fake)\n";
echo "  ⚠️  Log error akan muncul di laravel.log (NORMAL untuk dummy token)\n\n";

echo "Sekarang coba:\n";
echo "  1. Buka web admin: http://127.0.0.1:8000/admin/notifications\n";
echo "  2. Klik 'Test Push' pada template\n";
echo "  3. Pilih user: {$user->name}\n";
echo "  4. Kirim notifikasi\n";
echo "  5. Cek log: tail -f storage/logs/laravel.log\n\n";

// Tampilkan total users dengan token
$totalUsersWithTokens = App\Models\DeviceToken::where('is_active', true)
    ->distinct('user_id')
    ->count();

echo "📊 Status: {$totalUsersWithTokens} user memiliki FCM token aktif\n";

