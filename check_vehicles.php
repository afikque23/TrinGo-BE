<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK KENDARAAN DI DATABASE ===\n\n";

$vehicles = DB::table('vehicles')->get();

if ($vehicles->isEmpty()) {
    echo "❌ Tidak ada kendaraan di database\n";
} else {
    foreach ($vehicles as $vehicle) {
        echo "Kendaraan ID: {$vehicle->id}\n";
        echo "  - Title: {$vehicle->title}\n";
        echo "  - User ID: " . ($vehicle->user_id ?? 'null') . "\n";
        echo "  - Device ID: " . ($vehicle->device_id ?? 'null') . "\n";
        echo "  - Is Primary: " . ($vehicle->is_primary ? 'Yes' : 'No') . "\n";
        echo "  - Odometer: {$vehicle->odometer}\n";
        echo "---\n";
    }
}

echo "\nDevice ID dari app: 89ae2461-6993-4490-b408-36c2d4454c92\n";
