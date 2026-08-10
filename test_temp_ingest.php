<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deviceId = '20:9B:A9:61:04:F8';

// Cek kondisi awal
$vehicle = \App\Models\Vehicle::where('device_id', $deviceId)->first();
if (!$vehicle) {
    echo "❌ Vehicle dengan device_id=$deviceId tidak ditemukan!\n";
    echo "Daftar device_id yang ada:\n";
    \App\Models\Vehicle::whereNotNull('device_id')->get(['id','title','device_id'])->each(function($v) {
        echo "  ID={$v->id} | {$v->title} | device_id={$v->device_id}\n";
    });
    exit(1);
}

echo "✅ Vehicle ditemukan: {$vehicle->title} (ID={$vehicle->id})\n";
echo "   last_engine_temp_c SEBELUM: " . var_export($vehicle->last_engine_temp_c, true) . "\n";
echo "   last_engine_temp_at SEBELUM: " . var_export($vehicle->last_engine_temp_at, true) . "\n\n";

// Jalankan ingest
$svc = app(\App\Services\TelemetryIngestService::class);
$payload = json_encode([
    'device_id' => $deviceId,
    'timestamp' => 245575,
    'has_fix' => true,
    'lat' => -7.057695,
    'lng' => 110.4296615,
    'speed_kmh' => 0.4,
    'sat' => 4,
    'hdop' => 1.98,
    'engine_temp_c' => 34.1,
    'engine_overheat' => false,
]);

echo "📤 Ingest payload: $payload\n\n";
$svc->ingest("vehicle/{$deviceId}/telemetry", $payload);

// Cek hasil
$vehicle->refresh();
echo "   last_engine_temp_c SESUDAH: " . var_export($vehicle->last_engine_temp_c, true) . "\n";
echo "   last_engine_temp_at SESUDAH: " . var_export($vehicle->last_engine_temp_at, true) . "\n";

if ($vehicle->last_engine_temp_c !== null) {
    echo "\n✅ SUKSES! Suhu berhasil disimpan.\n";
} else {
    echo "\n❌ GAGAL! Suhu masih null setelah ingest.\n";
}
