<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Trip;
use App\Models\Vehicle;
use Carbon\Carbon;

echo "Membersihkan data trip sebelumnya...\n";
\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Trip::truncate(); // Hapus trip lama agar bersih
\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

$vehicle = Vehicle::first();
if (!$vehicle) {
    echo "Vehicle tidak ditemukan!\n";
    exit;
}

// Reset odometer motor sebagai base awal
$vehicle->update(['odometer' => 1000]);

echo "Menginjeksi data baru sesuai Tabel 4.8...\n";

// 1. Pagi (07:15) - 1.45 km
Trip::create([
    'vehicle_id' => $vehicle->id,
    'status' => 'completed',
    'started_by' => 1,
    'source' => 'manual',
    'start_at' => Carbon::today()->addHours(7)->addMinutes(15),
    'end_at' => Carbon::today()->addHours(7)->addMinutes(25), // 10 menit
    'duration_minutes' => 10,
    'distance_meters' => 1450, // 1.45 km
    'avg_speed_kph' => 18.5,
    'start_odometer' => $vehicle->odometer,
    'end_odometer' => $vehicle->odometer + 1.45
]);
$vehicle->update(['odometer' => $vehicle->odometer + 1.45]);

// 2. Siang (12:10) - 4.96 km
Trip::create([
    'vehicle_id' => $vehicle->id,
    'status' => 'completed',
    'started_by' => 1,
    'source' => 'manual',
    'start_at' => Carbon::today()->addHours(12)->addMinutes(10),
    'end_at' => Carbon::today()->addHours(12)->addMinutes(30), // 20 menit
    'duration_minutes' => 20,
    'distance_meters' => 4960, // 4.96 km
    'avg_speed_kph' => 25.0,
    'start_odometer' => $vehicle->odometer,
    'end_odometer' => $vehicle->odometer + 4.96
]);
$vehicle->update(['odometer' => $vehicle->odometer + 4.96]);

// 3. Sore (16:30) - 3.12 km
Trip::create([
    'vehicle_id' => $vehicle->id,
    'status' => 'completed',
    'started_by' => 1,
    'source' => 'manual',
    'start_at' => Carbon::today()->addHours(16)->addMinutes(30),
    'end_at' => Carbon::today()->addHours(16)->addMinutes(45), // 15 menit
    'duration_minutes' => 15,
    'distance_meters' => 3120, // 3.12 km
    'avg_speed_kph' => 30.2,
    'start_odometer' => $vehicle->odometer,
    'end_odometer' => $vehicle->odometer + 3.12
]);
$vehicle->update(['odometer' => $vehicle->odometer + 3.12]);

echo "Data berhasil disinkronisasi ke Database!\n";
