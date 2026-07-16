<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Diagnostic controller untuk debugging koneksi ESP32 ↔ Backend.
 *
 * Endpoints ini membantu menelusuri apakah:
 *  1. device_id di motor sudah benar sesuai MAC/ID ESP32
 *  2. Telemetry sudah masuk ke backend dari ESP32
 *  3. MQTT subscriber sudah berjalan dan menerima pesan
 */
class IotDiagnosticController extends Controller
{
    /**
     * GET /motors/{motorId}/iot-diagnostic
     *
     * Tampilkan status lengkap koneksi IoT untuk motor tertentu.
     * Berguna untuk debugging: apakah ESP32 sudah terhubung dan datanya sudah masuk.
     */
    public function check(Request $request, $motorId)
    {
        $vehicle = Vehicle::where('id', $motorId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // ── Hitung status dari waktu telemetry terakhir ─────────────────────
        $lastReceived = $vehicle->last_telemetry_received_at;
        $secondsAgo   = $lastReceived ? now()->diffInSeconds($lastReceived) : null;

        $iotStatus = match (true) {
            $secondsAgo === null  => 'never_received',
            $secondsAgo <= 15    => 'online',
            $secondsAgo <= 60    => 'unstable',
            $secondsAgo <= 3600  => 'offline_recent',   // mati < 1 jam
            default              => 'offline',
        };

        // ── Diagnosis masalah ────────────────────────────────────────────────
        $issues = [];
        $hints  = [];

        if (!$vehicle->device_id) {
            $issues[] = 'device_id belum diisi di data motor';
            $hints[]  = 'Isi field "device_id" motor dengan MAC Address ESP32 (contoh: AA:BB:CC:DD:EE:FF)';
        }

        if ($iotStatus === 'never_received') {
            $issues[] = 'Tidak ada data telemetry yang pernah diterima dari perangkat ini';
            $hints[]  = 'Pastikan ESP32 mempublish ke topik: vehicle/' . ($vehicle->device_id ?? '{device_id}') . '/telemetry';
            $hints[]  = 'Pastikan MQTT Subscriber Laravel sedang berjalan: php artisan mqtt:subscribe';
            $hints[]  = 'Periksa broker MQTT berjalan di port ' . config('mqtt.port', 1883);
        }

        if ($vehicle->device_id) {
            $expectedTelemetryTopic = 'vehicle/' . $vehicle->device_id . '/telemetry';
            $expectedCommandTopic   = 'tringgo/device/' . $vehicle->device_id . '/command';
        } else {
            $expectedTelemetryTopic = 'vehicle/{device_id}/telemetry';
            $expectedCommandTopic   = 'tringgo/device/{device_id}/command';
        }

        // ── Log diagnostik ini ke Laravel log ───────────────────────────────
        Log::channel('stack')->info('[IoT Diagnostic] Vehicle check', [
            'vehicle_id'   => $vehicle->id,
            'vehicle_name' => $vehicle->title,
            'device_id'    => $vehicle->device_id,
            'iot_status'   => $iotStatus,
            'seconds_ago'  => $secondsAgo,
            'issues_found' => count($issues),
            'user_id'      => $request->user()->id,
        ]);

        return response()->json([
            'vehicle' => [
                'id'        => $vehicle->id,
                'name'      => $vehicle->title,
                'device_id' => $vehicle->device_id,
            ],
            'iot_status'  => $iotStatus,
            'seconds_ago' => $secondsAgo,
            'last_telemetry_received_at' => $lastReceived?->toIso8601String(),
            'last_telemetry_at'          => $vehicle->last_telemetry_at?->toIso8601String(),
            'last_known_location' => [
                'latitude'  => $vehicle->last_latitude  ? (float) $vehicle->last_latitude  : null,
                'longitude' => $vehicle->last_longitude ? (float) $vehicle->last_longitude : null,
                'speed_kph' => $vehicle->last_speed_kph,
                'altitude'  => $vehicle->last_altitude,
                'satellites' => $vehicle->last_satellites,
                'accuracy_meters' => $vehicle->last_accuracy_meters,
            ],
            'mqtt_topics' => [
                'telemetry_subscribe' => $expectedTelemetryTopic,
                'command_publish'     => $expectedCommandTopic,
                'broker_host'         => config('mqtt.host'),
                'broker_port'         => config('mqtt.port'),
            ],
            'diagnosis' => [
                'issues' => $issues,
                'hints'  => $hints,
                'ok'     => count($issues) === 0,
            ],
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /iot-diagnostic/ping-mqtt
     *
     * Coba koneksi ke MQTT broker dan laporkan hasilnya.
     * Berguna untuk mengecek apakah broker MQTT aktif.
     */
    public function pingMqtt(Request $request)
    {
        $host = config('mqtt.host', '127.0.0.1');
        $port = (int) config('mqtt.port', 1883);

        $connected = false;
        $errorMsg  = null;
        $latencyMs = null;

        try {
            $start = microtime(true);

            // Coba koneksi TCP sederhana ke broker
            $socket = @fsockopen($host, $port, $errno, $errstr, 5);
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            if ($socket) {
                fclose($socket);
                $connected = true;
            } else {
                $errorMsg = "errno={$errno} errstr={$errstr}";
            }
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
        }

        $result = [
            'broker_host' => $host,
            'broker_port' => $port,
            'reachable'   => $connected,
            'latency_ms'  => $latencyMs,
            'error'       => $errorMsg,
            'hint'        => $connected
                ? 'Broker MQTT dapat dijangkau. Pastikan ESP32 terhubung ke broker yang sama.'
                : 'Broker MQTT tidak dapat dijangkau. Pastikan Mosquitto/broker berjalan.',
            'checked_at'  => now()->toIso8601String(),
        ];

        Log::info('[IoT Diagnostic] MQTT ping', $result);

        return response()->json($result, $connected ? 200 : 503);
    }

    /**
     * GET /motors/{motorId}/iot-diagnostic/simulate-telemetry
     *
     * Simulasi kiriman data telemetry ke vehicle ini, tanpa MQTT.
     * Berguna untuk memverifikasi alur backend bekerja ketika data masuk.
     * HANYA untuk keperluan debug/development.
     */
    public function simulateTelemetry(Request $request, $motorId)
    {
        $vehicle = Vehicle::where('id', $motorId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!$vehicle->device_id) {
            return response()->json([
                'success' => false,
                'message' => 'Motor belum memiliki device_id. Isi terlebih dahulu di pengaturan motor.',
            ], 422);
        }

        // Lokasi simulasi: koordinat sekitar Semarang
        $lat = -6.9535 + (mt_rand(-100, 100) / 10000);
        $lng = 110.4388 + (mt_rand(-100, 100) / 10000);

        $vehicle->forceFill([
            'last_latitude'              => $lat,
            'last_longitude'             => $lng,
            'last_speed_kph'             => mt_rand(0, 60),
            'last_altitude'              => mt_rand(5, 50),
            'last_accuracy_meters'       => mt_rand(3, 15),
            'last_satellites'            => mt_rand(6, 12),
            'last_telemetry_at'          => now(),
            'last_telemetry_received_at' => now(),
        ])->save();

        Log::info('[IoT Diagnostic] Simulated telemetry injected', [
            'vehicle_id' => $vehicle->id,
            'device_id'  => $vehicle->device_id,
            'lat'        => $lat,
            'lng'        => $lng,
            'by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telemetry simulasi berhasil diinjeksi. Status IoT motor seharusnya berubah menjadi "online" dalam 15 detik.',
            'simulated_data' => [
                'latitude'  => $lat,
                'longitude' => $lng,
                'speed_kph' => $vehicle->last_speed_kph,
            ],
            'note' => 'Ini hanya untuk testing. Data nyata harus datang dari ESP32 melalui MQTT.',
        ]);
    }
}
