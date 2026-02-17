<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Update vehicle ID 1 dengan device_id dari app
$deviceId = '89ae2461-6993-4490-b408-36c2d4454c92';
$vehicleId = 1;

$updated = DB::table('vehicles')
    ->where('id', $vehicleId)
    ->whereNull('device_id')
    ->update(['device_id' => $deviceId]);

if ($updated > 0) {
    echo "✅ Kendaraan ID $vehicleId berhasil diupdate dengan device_id\n";
    
    // Show vehicle info
    $vehicle = DB::table('vehicles')->where('id', $vehicleId)->first();
    echo "   - Title: {$vehicle->title}\n";
    echo "   - Device ID: {$vehicle->device_id}\n";
    echo "   - User ID: " . ($vehicle->user_id ?? 'null (guest mode)') . "\n";
} else {
    echo "ℹ️  Kendaraan ID $vehicleId sudah memiliki device_id atau tidak ditemukan\n";
}
