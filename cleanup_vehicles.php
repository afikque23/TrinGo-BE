<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Update vehicles that have user_id but no device_id
// We'll keep them as user-owned (don't need device_id if they have user_id)
$vehiclesUpdated = DB::table('vehicles')
    ->whereNull('device_id')
    ->whereNotNull('user_id')
    ->count();

echo "ℹ️  Kendaraan dengan user_id (tidak perlu device_id): $vehiclesUpdated\n";

// Count vehicles without both (orphaned - should be cleaned)
$orphaned = DB::table('vehicles')
    ->whereNull('device_id')
    ->whereNull('user_id')
    ->count();

if ($orphaned > 0) {
    echo "⚠️  Kendaraan tanpa user_id dan device_id (akan dihapus): $orphaned\n";
    DB::table('vehicles')
        ->whereNull('device_id')
        ->whereNull('user_id')
        ->delete();
    echo "✅ Kendaraan orphaned berhasil dihapus\n";
}

echo "\n✅ Database sudah siap untuk Guest Mode!\n";
