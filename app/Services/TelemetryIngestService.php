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

        // If device reports it has no GPS fix, skip updating last location.
        if (array_key_exists('has_fix', $data)) {
            $hasFix = $data['has_fix'];
            $hasFixBool = is_bool($hasFix)
                ? $hasFix
                : (is_int($hasFix) || is_float($hasFix) ? ((float) $hasFix) !== 0.0 : filter_var($hasFix, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));

            if ($hasFixBool === false) {
                return;
            }
        }

        $latitude = $this->getNumeric($data, ['lat', 'latitude']);
        $longitude = $this->getNumeric($data, ['lng', 'lon', 'longitude']);

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

        $speedKph = $this->getSpeedKph($data);
        $headingDeg = $this->getNumeric($data, ['heading', 'course', 'course_deg', 'heading_deg']);
        $altitude = $this->getNumeric($data, ['alt', 'altitude']);
        $accuracyMeters = $this->getNumeric($data, ['accuracy', 'accuracy_meters', 'acc']);
        $satellites = $this->getNumeric($data, ['sat', 'satellites']);
        $hdop = $this->getNumeric($data, ['hdop']);

        $telemetryAt = $this->parseTelemetryAt($data);

        $matches = Vehicle::query()
            ->where('device_id', $deviceId)
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($matches->count() > 1) {
            Log::warning('Multiple vehicles share the same device_id. Using the lowest id match.', [
                'topic' => $topic,
                'device_id' => $deviceId,
                'vehicle_ids' => $matches->pluck('id')->all(),
            ]);
        }

        $vehicle = $matches->first();
        if (!$vehicle) {
            Log::warning('Telemetry received for unknown device_id.', [
                'topic' => $topic,
                'device_id' => $deviceId,
            ]);
            return;
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
            'last_telemetry_at' => $telemetryAt,
            'last_telemetry_received_at' => $receivedAt,
        ])->save();

        if ((bool) config('mqtt.trip_points.enabled', false)) {
            $this->appendTripPoint(
                vehicle: $vehicle,
                latitude: $latitude,
                longitude: $longitude,
                altitude: $altitude,
                speedKph: $speedKph,
                accuracyMeters: $accuracyMeters,
                recordedAt: $telemetryAt ?? $receivedAt,
            );
        }
    }

    private function appendTripPoint(
        Vehicle $vehicle,
        float $latitude,
        float $longitude,
        ?float $altitude,
        ?float $speedKph,
        ?float $accuracyMeters,
        Carbon $recordedAt,
    ): void {
        $trip = Trip::query()
            ->where('vehicle_id', $vehicle->id)
            ->whereNull('end_at')
            ->orderByDesc('id')
            ->first();

        if (!$trip) {
            // Auto-start a trip so incoming telemetry can be stored as trip history.
            $trip = Trip::query()->create([
                'vehicle_id' => $vehicle->id,
                'started_by' => $vehicle->user_id,
                'start_at' => $recordedAt,
                'end_at' => null,
                'distance_meters' => 0,
                'start_odometer' => $vehicle->odometer ?? 0,
                'notes' => 'Auto-started from MQTT telemetry',
            ]);
        }

        $nextSequence = (int) (TripPoint::query()->where('trip_id', $trip->id)->max('sequence') ?? 0) + 1;

        TripPoint::create([
            'trip_id' => $trip->id,
            'sequence' => $nextSequence,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'altitude' => $altitude,
            'speed_kph' => $speedKph !== null ? (int) round($speedKph) : null,
            'accuracy_meters' => $accuracyMeters,
            'recorded_at' => $recordedAt,
        ]);
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
    private function getSpeedKph(array $data): ?float
    {
        $speedKph = $this->getNumeric($data, ['speed_kph', 'speed_kmh', 'speed_km_h', 'speedKph']);
        if ($speedKph !== null) {
            return $speedKph;
        }

        $speedMps = $this->getNumeric($data, ['speed_mps', 'speedMps']);
        if ($speedMps !== null) {
            return $speedMps * 3.6;
        }

        return $this->getNumeric($data, ['speed']);
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
