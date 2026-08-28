<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\User;
use Carbon\Carbon;

echo "Memeriksa Vehicle & User...\n";
$user = User::first() ?? User::factory()->create();
$vehicle = Vehicle::first() ?? Vehicle::create([
    'user_id' => $user->id,
    'name' => 'Motor Test',
    'motor_type' => 'matic',
    'brand' => 'Honda',
    'plate_number' => 'B 1234 ABC',
    'odometer' => 1000
]);

echo "Mengisi Trip Dummy Realistis...\n";

Trip::create([
    'vehicle_id' => $vehicle->id,
    'status' => 'completed',
    'started_by' => $user->id,
    'source' => 'manual',
    'start_at' => Carbon::today()->addHours(7)->addMinutes(15), // Pagi 07:15
    'end_at' => Carbon::today()->addHours(7)->addMinutes(45), // Selesai 07:45
    'duration_minutes' => 30,
    'distance_meters' => 14500, // 14.5 km
    'avg_speed_kph' => 35.5,
    'start_odometer' => $vehicle->odometer,
    'end_odometer' => $vehicle->odometer + 14.5
]);
$vehicle->odometer += 14.5;
$vehicle->save();

Trip::create([
    'vehicle_id' => $vehicle->id,
    'status' => 'completed',
    'started_by' => $user->id,
    'source' => 'manual',
    'start_at' => Carbon::today()->addHours(12)->addMinutes(10), // Siang 12:10
    'end_at' => Carbon::today()->addHours(12)->addMinutes(50), // Selesai 12:50
    'duration_minutes' => 40,
    'distance_meters' => 12300, // 12.3 km (Macet)
    'avg_speed_kph' => 22.1,
    'start_odometer' => $vehicle->odometer,
    'end_odometer' => $vehicle->odometer + 12.3
]);
$vehicle->odometer += 12.3;
$vehicle->save();

Trip::create([
    'vehicle_id' => $vehicle->id,
    'status' => 'completed',
    'started_by' => $user->id,
    'source' => 'hardware',
    'start_at' => Carbon::today()->addHours(19)->addMinutes(30), // Malam 19:30
    'end_at' => Carbon::today()->addHours(19)->addMinutes(50), // Selesai 19:50
    'duration_minutes' => 20,
    'distance_meters' => 15200, // 15.2 km (Sepi, ngebut dikit)
    'avg_speed_kph' => 50.5,
    'start_odometer' => $vehicle->odometer,
    'end_odometer' => $vehicle->odometer + 15.2
]);
$vehicle->odometer += 15.2;
$vehicle->save();

echo "3 Trip berhasil diinjeksi!\n";
