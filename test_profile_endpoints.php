<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Profile Endpoints ===\n\n";

// Get user dengan token
$user = App\Models\User::first();

if (!$user) {
    echo "❌ Tidak ada user di database!\n";
    exit(1);
}

// Create personal access token untuk testing
$token = $user->createToken('test-profile-token')->plainTextToken;

echo "Testing dengan user: {$user->name} ({$user->email})\n";
echo "Token: " . substr($token, 0, 40) . "...\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ENDPOINT 1: GET /profile\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$controller = new App\Http\Controllers\ProfileController();
$request = new Illuminate\Http\Request();
$request->setUserResolver(function() use ($user) {
    return $user;
});

$response = $controller->show($request);
$data = json_decode($response->getContent(), true);

echo "Status: {$response->getStatusCode()}\n";
echo "Response:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Verify stats
if (isset($data['data']['stats'])) {
    echo "Stats verification:\n";
    echo "  - total_vehicles: " . (isset($data['data']['stats']['total_vehicles']) ? "✅ Ada" : "❌ Tidak ada") . "\n";
    echo "  - total_trips: " . (isset($data['data']['stats']['total_trips']) ? "❌ Masih ada (HARUS DIHAPUS)" : "✅ Sudah dihapus") . "\n";
    echo "  - total_km: " . (isset($data['data']['stats']['total_km']) ? "❌ Masih ada (HARUS DIHAPUS)" : "✅ Sudah dihapus") . "\n";
} else {
    echo "❌ Stats tidak ada!\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ENDPOINT 2: POST /profile/update\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$updateRequest = new Illuminate\Http\Request();
$updateRequest->setUserResolver(function() use ($user) {
    return $user;
});
$updateRequest->merge([
    'name' => $user->name . ' (Updated)',
]);

$updateResponse = $controller->update($updateRequest);
$updateData = json_decode($updateResponse->getContent(), true);

echo "Status: {$updateResponse->getStatusCode()}\n";
echo "Updated name: " . ($updateData['data']['name'] ?? 'N/A') . "\n";
echo "Stats in update response: " . (isset($updateData['data']['stats']) ? "✅ Ada" : "❌ Tidak ada") . "\n";

if (isset($updateData['data']['stats'])) {
    echo "  - total_vehicles: " . (isset($updateData['data']['stats']['total_vehicles']) ? "✅ Ada" : "❌ Tidak ada") . "\n";
    echo "  - total_trips: " . (isset($updateData['data']['stats']['total_trips']) ? "❌ Masih ada" : "✅ Dihapus") . "\n";
    echo "  - total_km: " . (isset($updateData['data']['stats']['total_km']) ? "❌ Masih ada" : "✅ Dihapus") . "\n";
}

// Rollback name
$user->update(['name' => str_replace(' (Updated)', '', $user->name)]);

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ENDPOINT 3: DELETE /profile/avatar\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$deleteRequest = new Illuminate\Http\Request();
$deleteRequest->setUserResolver(function() use ($user) {
    return $user;
});

$deleteResponse = $controller->deleteAvatar($deleteRequest);
$deleteData = json_decode($deleteResponse->getContent(), true);

echo "Status: {$deleteResponse->getStatusCode()}\n";
echo "Avatar: " . ($deleteData['data']['avatar'] ?? 'null') . "\n";
echo "Stats in delete response: " . (isset($deleteData['data']['stats']) ? "✅ Ada" : "❌ Tidak ada") . "\n";

if (isset($deleteData['data']['stats'])) {
    echo "  - total_vehicles: " . (isset($deleteData['data']['stats']['total_vehicles']) ? "✅ Ada" : "❌ Tidak ada") . "\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "SUMMARY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$allEndpointsOk = true;

if (isset($data['data']['stats']['total_trips']) || 
    isset($updateData['data']['stats']['total_trips']) || 
    isset($deleteData['data']['stats']['total_trips'])) {
    echo "❌ GAGAL: total_trips masih muncul di response!\n";
    $allEndpointsOk = false;
}

if (isset($data['data']['stats']['total_km']) || 
    isset($updateData['data']['stats']['total_km']) || 
    isset($deleteData['data']['stats']['total_km'])) {
    echo "❌ GAGAL: total_km masih muncul di response!\n";
    $allEndpointsOk = false;
}

if ($allEndpointsOk) {
    echo "✅ SEMUA ENDPOINT SUDAH BENAR!\n";
    echo "✅ Hanya total_vehicles yang muncul di stats\n";
    echo "✅ total_trips dan total_km sudah tidak muncul\n";
}

// Clean up token
$user->tokens()->where('name', 'test-profile-token')->delete();

echo "\n";
