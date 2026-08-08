<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\TripPoint;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TelemetryIngestService
{
    /**
     * Ingest a telemetry message and update the matched vehicle's last known location.
     *
     * Expected topic format: vehicle/{deviceId}/telemetry
     */
    public function ingest(string $topic, string $payload, ?array $payloadJson = null, ?\DateTimeInterface $receivedAt = null): void
    {
        $deviceId = $this->extractDeviceId($topic);
        if ($deviceId === null) {
            return;
        }

        $receivedAt = $receivedAt ? Carbon::instance($receivedAt) : now();

        $data = $payloadJson;
        if (!is_array($data)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data = $decoded;
            }
        }

        if (!is_array($data)) {
            return;
        }

        $hasFixBool = null;
        if (array_key_exists('has_fix', $data)) {
            $hasFix = $data['has_fix'];
            $hasFixBool = is_bool($hasFix)
                ? $hasFix
                : (is_int($hasFix) || is_float($hasFix) ? ((float) $hasFix) !== 0.0 : filter_var($hasFix, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }

        $latitude = $this->getNumeric($data, ['lat', 'latitude']);
        $longitude = $this->getNumeric($data, ['lng', 'lon', 'longitude']);
        $speedKph = $this->getSpeedKph($data);
        $estDistanceM = $this->getNumeric($data, ['est_distance_m']);
        $mpuIsMoving = $this->getBool($data, ['mpu_is_moving']);
        $mpuGForce = $this->getNumeric($data, ['mpu_g_force']);

        // Suhu mesin dari DS18B20 (monitoring only — tidak dipakai sebagai input fuzzy)
        $engineTempC = $this->getNumeric($data, ['engine_temp_c']);
        // Validasi: DS18B20 mengembalikan -127 jika tidak terdeteksi
        if ($engineTempC !== null && $engineTempC < -50.0) {
            $engineTempC = null;
        }
        $engineOverheat = $this->getBool($data, ['engine_overheat']);

        if ($hasFixBool === false) {
            $latitude = null;
            $longitude = null;
        } else {
            if ($latitude === null || $longitude === null) {
                return;
            }

            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                Log::warning('Telemetry ignored due to invalid coordinates.', [
                    'topic' => $topic,
                    'device_id' => $deviceId,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                return;
            }

            // Double validation logic to fix GPS drift
            if ($speedKph > 2.0 && $mpuIsMoving === false) {
                $speedKph = 0.0;
            }
        }

        $headingDeg = $this->getNumeric($data, ['heading', 'course', 'course_deg', 'heading_deg']);
        $altitude = $this->getNumeric($data, ['alt', 'altitude']);
        $accuracyMeters = $this->getNumeric($data, ['accuracy', 'accuracy_meters', 'acc']);
        $satellites = $this->getNumeric($data, ['sat', 'satellites']);
        $hdop = $this->getNumeric($data, ['hdop']);

        $baroOk = $this->getBool($data, ['baro_ok']);
        $baroRelAltM = $this->getNumeric($data, ['baro_rel_alt_m']);
        $gradeRatio = $this->getNumeric($data, ['grade']);
        $gradePct = $this->getNumeric($data, ['grade_pct']);

        $telemetryAt = $this->parseTelemetryAt($data);

        // Cari semua kendaraan yang terikat dengan device_id ini
        $matches = Vehicle::query()
            ->where('device_id', $deviceId)
            ->get();

        if ($matches->isEmpty()) {
            Log::warning('Telemetry received for unknown device_id.', [
                'topic'     => $topic,
                'device_id' => $deviceId,
            ]);
            return;
        }

        // Prioritas 1: motor yang ditetapkan sebagai is_primary = 1
        // Prioritas 2: motor terbaru (id terbesar) yang terikat device_id ini
        $vehicle = $matches->firstWhere('is_primary', true)
            ?? $matches->sortByDesc('id')->first();

        if ($matches->count() > 1) {
            Log::info('Multiple vehicles share device_id. Routing telemetry to primary/latest.', [
                'topic'          => $topic,
                'device_id'      => $deviceId,
                'routed_to'      => $vehicle->id,
                'is_primary'     => (bool) $vehicle->is_primary,
                'all_vehicle_ids'=> $matches->pluck('id')->all(),
            ]);
        }

        // Hitung fallback kecepatan matematis jika kecepatan dari sensor kurang akurat di kecepatan rendah (< 5 km/h)
        if ($speedKph < 5.0 && $latitude !== null && $longitude !== null && $vehicle->last_latitude !== null && $vehicle->last_longitude !== null) {
            $distanceMeters = $this->calculateHaversineDistance((float)$vehicle->last_latitude, (float)$vehicle->last_longitude, $latitude, $longitude);
            
            // Jarak minimal 2 meter agar tidak terpengaruh GPS drift kecil
            // Maksimal 100 meter per ping agar terhindar dari spike GPS jauh
            if ($distanceMeters >= 2.0 && $distanceMeters <= 100.0) {
                $lastTime = $vehicle->last_telemetry_at ?? $vehicle->last_telemetry_received_at;
                $currentTime = $telemetryAt ?? $receivedAt;
                
                if ($lastTime && $currentTime) {
                    $dtSeconds = $currentTime->getTimestamp() - $lastTime->getTimestamp();
                    if ($dtSeconds > 0) {
                        $mathSpeedKph = ($distanceMeters / $dtSeconds) * 3.6;
                        // Ambil kecepatan terbesar jika sensor hardware under-reporting
                        if ($mathSpeedKph > $speedKph) {
                            $speedKph = $mathSpeedKph;
                        }
                    }
                }
            } elseif ($distanceMeters < 2.0 && $speedKph <= 0) {
                $speedKph = 0.0;
            }
        }

        $vehicle->forceFill([
            'last_latitude' => $latitude,
            'last_longitude' => $longitude,
            'last_speed_kph' => $speedKph !== null ? (int) round($speedKph) : null,
            'last_heading_deg' => $headingDeg !== null ? (int) round($headingDeg) : null,
            'last_altitude' => $altitude,
            'last_accuracy_meters' => $accuracyMeters,
            'last_satellites' => $satellites !== null ? (int) round($satellites) : null,
            'last_hdop' => $hdop,
            'last_baro_ok' => $baroOk,
            'last_baro_rel_alt_m' => $baroRelAltM,
            'last_grade_ratio' => $gradeRatio,
            'last_grade_pct' => $gradePct,
            'last_telemetry_at' => $telemetryAt,
            'last_telemetry_received_at' => $receivedAt,
            // Suhu mesin DS18B20 — hanya diperbarui jika sensor mengirim data valid
            ...(($engineTempC !== null) ? [
                'last_engine_temp_c' => round($engineTempC, 2),
                'last_engine_overheat' => $engineOverheat ?? false,
                'last_engine_temp_at' => $receivedAt,
            ] : []),
        ])->save();

        if ((bool) config('mqtt.trip_points.enabled', false)) {
            $this->appendTripPoint(
                vehicle: $vehicle,
                latitude: $latitude,
                longitude: $longitude,
                altitude: $altitude,
                baroRelAltM: $baroRelAltM,
                gradePct: $gradePct,
                speedKph: $speedKph,
                accuracyMeters: $accuracyMeters,
                recordedAt: $telemetryAt ?? $receivedAt,
                hasFix: $hasFixBool,
                estDistanceM: $estDistanceM,
                mpuIsMoving: $mpuIsMoving,
                mpuGForce: $mpuGForce,
            );
        }
    }

    private function appendTripPoint(
        Vehicle $vehicle,
        ?float $latitude,
        ?float $longitude,
        ?float $altitude,
        ?float $baroRelAltM,
        ?float $gradePct,
        ?float $speedKph,
        ?float $accuracyMeters,
        Carbon $recordedAt,
        ?bool $hasFix = null,
        ?float $estDistanceM = null,
        ?bool $mpuIsMoving = null,
        ?float $mpuGForce = null,
    ): void {
        // Jangan simpan no-fix telemetry ke trip_points agar rute/jarak tidak tercemar.
        if ($hasFix === false || $latitude === null || $longitude === null) {
            Log::debug('TelemetryIngest: skipping trip_point due to GPS no-fix.', [
                'vehicle_id' => $vehicle->id,
                'has_fix' => $hasFix,
            ]);
            return;
        }

        // Pattern B: hanya simpan trip_points jika ada trip AKTIF yang dimulai oleh user.
        // Tidak auto-start trip — trip harus dimulai via tombol START di mobile.
        $trip = Trip::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('status', 'active')
            ->whereNull('end_at')
            ->orderByDesc('id')
            ->first();

        if (!$trip) {
            // Tidak ada trip aktif — skip, jangan simpan trip_points.
            Log::debug('TelemetryIngest: no active trip for vehicle, skipping trip_point.', [
                'vehicle_id' => $vehicle->id,
            ]);
            return;
        }

        $lastPoint = TripPoint::query()
            ->where('trip_id', $trip->id)
            ->orderByDesc('sequence')
            ->first();

        $nextSequence = ($lastPoint->sequence ?? 0) + 1;

        $distanceToAdd = 0.0;
        if ($hasFix === true && $latitude !== null && $longitude !== null && $lastPoint && $lastPoint->latitude !== null && $lastPoint->longitude !== null) {
            $distanceToAdd = $this->calculateHaversineDistance((float)$lastPoint->latitude, (float)$lastPoint->longitude, $latitude, $longitude);
        } elseif ($hasFix === false && $estDistanceM !== null && $estDistanceM > 0) {
            $distanceToAdd = $estDistanceM;
        }

        if ($distanceToAdd > 0) {
            $trip->distance_meters = ($trip->distance_meters ?? 0) + (int) round($distanceToAdd);
            $trip->save();
        }

        TripPoint::create([
            'trip_id' => $trip->id,
            'sequence' => $nextSequence,
            'has_fix' => $hasFix,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'altitude' => $altitude,
            'baro_rel_alt_m' => $baroRelAltM,
            'grade_pct' => $gradePct,
            'speed_kph' => $speedKph !== null ? (int) round($speedKph) : null,
            'est_distance_m' => $estDistanceM,
            'mpu_is_moving' => $mpuIsMoving,
            'mpu_g_force' => $mpuGForce,
            'accuracy_meters' => $accuracyMeters,
            'recorded_at' => $recordedAt,
        ]);
    }

    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000.0; // in meters
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * (sin($lonDelta / 2) ** 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * @param array<string,mixed> $data
     * @param string[] $keys
     */
    private function getBool(array $data, array $keys): ?bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            if (is_bool($value)) {
                return $value;
            }

            if (is_int($value) || is_float($value)) {
                return ((float) $value) !== 0.0;
            }

            if (is_string($value)) {
                $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        }

        return null;
    }

    private function extractDeviceId(string $topic): ?string
    {
        if (preg_match('~^vehicle/([^/]+)/telemetry$~', $topic, $m) !== 1) {
            return null;
        }

        $deviceId = trim($m[1]);
        return $deviceId !== '' ? $deviceId : null;
    }

    /**
     * @param array<string,mixed> $data
     * @param string[] $keys
     */
    private function getNumeric(array $data, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            if (is_int($value) || is_float($value)) {
                return (float) $value;
            }

            if (is_string($value) && is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    /**
     * @param array<string,mixed> $data
     */
    private function getSpeedKph(array $data): float
    {
        $speedKph = $this->getNumeric($data, ['speed_kph', 'speed_kmh', 'speed']);
        if ($speedKph !== null) {
            return $speedKph;
        }

        $speedKphFallback = $this->getNumeric($data, ['speed_km_h', 'speedKph']);
        if ($speedKphFallback !== null) {
            return $speedKphFallback;
        }

        $speedMps = $this->getNumeric($data, ['speed_mps', 'speedMps']);
        if ($speedMps !== null) {
            return $speedMps * 3.6;
        }

        return 0.0;
    }

    /**
     * @param array<string,mixed> $data
     */
    private function parseTelemetryAt(array $data): ?Carbon
    {
        $raw = $data['timestamp'] ?? $data['recorded_at'] ?? $data['time'] ?? null;
        if ($raw === null) {
            return null;
        }

        if (is_int($raw) || is_float($raw)) {
            $numeric = (float) $raw;
            // Heuristic: treat > 1e12 as milliseconds.
            if ($numeric > 1_000_000_000_000) {
                return Carbon::createFromTimestampMs((int) round($numeric));
            }

            if ($numeric > 0) {
                return Carbon::createFromTimestamp((int) round($numeric));
            }

            return null;
        }

        if (is_string($raw)) {
            try {
                return Carbon::parse($raw);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
