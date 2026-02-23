<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Profile Resource Stats ===\n\n";

// Ambil user pertama yang ada
$user = App\Models\User::first();

if (!$user) {
    echo "❌ Tidak ada user di database!\n";
    exit(1);
}

echo "Testing dengan user: {$user->name} ({$user->email})\n\n";

// Simulasi ProfileResource
$resource = new App\Http\Resources\ProfileResource($user);
$profileData = $resource->toArray(request());

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Profile Data:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ID       : {$profileData['id']}\n";
echo "Name     : {$profileData['name']}\n";
echo "Email    : {$profileData['email']}\n";
echo "Phone    : " . ($profileData['phone'] ?? 'null') . "\n";
echo "Location : " . ($profileData['location'] ?? 'null') . "\n";
echo "Avatar   : " . ($profileData['avatar'] ?? 'null') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Stats:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
foreach ($profileData['stats'] as $key => $value) {
    echo str_pad(ucwords(str_replace('_', ' ', $key)), 20) . ": {$value}\n";
}
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Check apakah total_trips dan total_km sudah dihapus
if (isset($profileData['stats']['total_trips'])) {
    echo "❌ GAGAL: total_trips masih ada!\n";
} else {
    echo "✅ BERHASIL: total_trips sudah dihapus\n";
}

if (isset($profileData['stats']['total_km'])) {
    echo "❌ GAGAL: total_km masih ada!\n";
} else {
    echo "✅ BERHASIL: total_km sudah dihapus\n";
}

if (isset($profileData['stats']['total_vehicles'])) {
    echo "✅ BERHASIL: total_vehicles masih ada (jumlah: {$profileData['stats']['total_vehicles']})\n";
} else {
    echo "❌ GAGAL: total_vehicles tidak ada!\n";
}

echo "\n";
echo "JSON Response Preview:\n";
echo json_encode([
    'success' => true,
    'message' => 'Profile retrieved successfully',
    'data' => $profileData
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
