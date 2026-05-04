<?php

namespace Database\Seeders;

use App\Models\MqttMessage;
use App\Models\Trip;
use App\Models\TripPoint;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MqttTripSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();
        if (!$user) {
            $this->command?->warn('MqttTripSeeder: No users found; skipping.');
            return;
        }

        // Demo route: Kost (A) -> POLINES (B)
        // A coordinate follows your MQTT publish example.
        // B coordinate follows real MQTT telemetry samples near POLINES.
        $deviceId = 'demo-route-kost-polines';
        $vehicleTitle = 'Demo Trip Kost → POLINES';

        $a = ['name' => 'Kost (Titik A)', 'lat' => -7.057748, 'lng' => 110.429642];
        $b = ['name' => 'POLINES (Titik B)', 'lat' => -7.056590, 'lng' => 110.436709];

        // Create a path with turns so the polyline looks like a real route.
        $waypoints = [
            $a,
            ['name' => 'Jl. Prof. Soedarto (belok 1)', 'lat' => -7.057748, 'lng' => 110.433200],
            ['name' => 'Tembalang (belok 2)', 'lat' => -7.056900, 'lng' => 110.433200],
            $b,
        ];

        DB::beginTransaction();
        try {
            $vehicle = Vehicle::query()->firstOrCreate(
                ['device_id' => $deviceId],
                [
                    'user_id' => $user->id,
                    'title' => $vehicleTitle,
                    'tipe_motor' => 'matic',
                    'odometer' => 0,
                    'is_primary' => false,
                ]
            );

            // Cleanup previous demo data so seeding is repeatable.
            $tripIds = Trip::withTrashed()->where('vehicle_id', $vehicle->id)->pluck('id');
            if ($tripIds->isNotEmpty()) {
                TripPoint::query()->whereIn('trip_id', $tripIds)->delete();
                Trip::withTrashed()->whereIn('id', $tripIds)->forceDelete();
            }

            MqttMessage::query()->where('topic', "vehicle/{$deviceId}/telemetry")->delete();

            $points = [];
            // Segment densities: more points => smoother line.
            $segmentSteps = [35, 20, 35];
            for ($s = 0; $s < count($waypoints) - 1; $s++) {
                $from = $waypoints[$s];
                $to = $waypoints[$s + 1];
                $steps = $segmentSteps[$s] ?? 20;
                $segment = $this->interpolate($from['lat'], $from['lng'], $to['lat'], $to['lng'], $steps);
                // Avoid duplicating the connecting point.
                if (!empty($points)) {
                    array_shift($segment);
                }
                $points = array_merge($points, $segment);
            }

            $startAt = now()->subMinutes(15);

            $totalMeters = 0.0;
            $maxSpeed = 0;
            $sumSpeed = 0.0;

            // Create a completed trip so it appears clearly in history.
            $trip = Trip::query()->create([
                'vehicle_id' => $vehicle->id,
                'started_by' => $user->id,
                'start_at' => $startAt,
                'end_at' => null,
                'distance_meters' => 0,
                'start_odometer' => $vehicle->odometer ?? 0,
                'notes' => 'Demo seeded route: Kost (A) → POLINES (B)',
            ]);

            $prev = null;
            foreach ($points as $i => $p) {
                $seq = $i + 1;
                $recordedAt = (clone $startAt)->addSeconds($i * 8);

                $speed = 12 + (int) round(6 * sin($i / 10));
                if ($speed < 0) {
                    $speed = 0;
                }

                $maxSpeed = max($maxSpeed, $speed);
                $sumSpeed += $speed;

                if ($prev !== null) {
                    $totalMeters += $this->haversineMeters($prev['lat'], $prev['lng'], $p['lat'], $p['lng']);
                }
                $prev = $p;

                $address = $seq === 1
                    ? $a['name']
                    : ($seq === count($points) ? $b['name'] : 'Rute Kost → POLINES');

                $payload = [
                    'device_id' => $deviceId,
                    'ts_ms' => (int) $recordedAt->getTimestampMs(),
                    'has_fix' => true,
                    'lat' => $p['lat'],
                    'lng' => $p['lng'],
                    'speed_kmh' => (float) $speed,
                    'course_deg' => null,
                    'sat' => 8,
                    'hdop' => 1.2,
                    'address' => $address,
                    'maps_url' => 'https://maps.google.com/?q=' . $p['lat'] . ',' . $p['lng'],
                    'timestamp' => $recordedAt->toIso8601String(),
                ];

                MqttMessage::query()->create([
                    'topic' => "vehicle/{$deviceId}/telemetry",
                    'qos' => 0,
                    'retained' => false,
                    'payload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
                    'payload_json' => $payload,
                    'address' => $payload['address'],
                    'maps_url' => $payload['maps_url'],
                    'received_at' => $recordedAt,
                    'meta' => [
                        'seed' => true,
                        'seed_source' => 'MqttTripSeeder',
                        'route' => 'kost-polines',
                    ],
                ]);

                TripPoint::query()->create([
                    'trip_id' => $trip->id,
                    'sequence' => $seq,
                    'latitude' => $p['lat'],
                    'longitude' => $p['lng'],
                    'altitude' => null,
                    'speed_kph' => $speed,
                    'accuracy_meters' => null,
                    'recorded_at' => $recordedAt,
                ]);
            }

            $endAt = (clone $startAt)->addSeconds((count($points) - 1) * 8);
            $avgSpeed = count($points) > 0 ? ($sumSpeed / count($points)) : null;

            $trip->forceFill([
                'end_at' => $endAt,
                'distance_meters' => (int) round($totalMeters),
                'avg_speed_kph' => $avgSpeed,
                'max_speed_kph' => $maxSpeed,
            ])->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->command?->info('MqttTripSeeder: Seeded demo trip route Kost (A) → POLINES (B) with trip_points + mqtt_messages.');
    }

    /**
     * @return array<int,array{lat:float,lng:float}>
     */
    private function interpolate(float $lat1, float $lng1, float $lat2, float $lng2, int $steps): array
    {
        $steps = max(2, $steps);
        $out = [];
        for ($i = 0; $i < $steps; $i++) {
            $t = $i / ($steps - 1);
            $out[] = [
                'lat' => $lat1 + (($lat2 - $lat1) * $t),
                'lng' => $lng1 + (($lng2 - $lng1) * $t),
            ];
        }
        return $out;
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $r * $c;
    }
}
