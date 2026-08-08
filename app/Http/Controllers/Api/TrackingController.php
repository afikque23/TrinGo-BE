<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\TripPoint;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TrackingController extends Controller
{
    protected $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    public function start(Request $request, $motorId)
    {
        $vehicle = Vehicle::where('id', $motorId)->where('user_id', $request->user()->id)->firstOrFail();

        // Check if there is already an active trip
        $activeTrip = Trip::where('vehicle_id', $vehicle->id)->where('status', 'active')->first();
        if ($activeTrip) {
            return response()->json([
                'message' => 'Terdapat trip yang masih aktif',
                'trip_id' => $activeTrip->id,
                'status' => 'active'
            ], 400);
        }

        // Create new trip
        $trip = Trip::create([
            'vehicle_id' => $vehicle->id,
            'started_by' => $request->user()->id,
            'start_at' => Carbon::now(),
            'status' => 'active',
            'device_id' => $vehicle->device_id, // if any
            'start_odometer' => $vehicle->odometer ?? 0,
        ]);

        // Publish MQTT
        $deviceId = $vehicle->device_id;
        if ($deviceId) {
            $topic = "tringgo/device/{$deviceId}/command";
            $payload = json_encode([
                'command' => 'start',
                'trip_id' => $trip->id
            ]);
            $this->mqttService->publish($topic, $payload);
        }

        return response()->json([
            'message' => 'Tracking started successfully',
            'trip_id' => $trip->id,
            'status' => 'active'
        ], 200);
    }

    public function stop(Request $request, $motorId)
    {
        $vehicle = Vehicle::where('id', $motorId)->where('user_id', $request->user()->id)->firstOrFail();

        // Find active trip
        $trip = Trip::where('vehicle_id', $vehicle->id)->where('status', 'active')->first();
        if (!$trip) {
            return response()->json([
                'message' => 'Tidak ada trip yang sedang aktif'
            ], 404);
        }

        // Publish MQTT
        $deviceId = $vehicle->device_id;
        if ($deviceId) {
            $topic = "tringgo/device/{$deviceId}/command";
            $payload = json_encode([
                'command' => 'stop',
                'trip_id' => $trip->id
            ]);
            $this->mqttService->publish($topic, $payload);
        }

        // Update trip
        $endAt = Carbon::now();
        $startAt = Carbon::parse($trip->start_at);
        // Simpan durasi sebagai menit bulat agar konsisten di seluruh UI dan riwayat trip.
        $durationSeconds = $startAt->diffInSeconds($endAt);
        $durationMinutes = (int) round($durationSeconds / 60);

        // Calculate distance and speed from trip points
        $points = TripPoint::where('trip_id', $trip->id)->orderBy('sequence')->get();

        // Fallback: simpan route points dari mobile jika subscriber MQTT tidak menyimpan TripPoints.
        if ($points->isEmpty()) {
            $clientRoutePoints = $request->input('client_route_points', []);
            if (is_array($clientRoutePoints) && !empty($clientRoutePoints)) {
                $rows = [];
                $sequence = 1;
                foreach ($clientRoutePoints as $rawPoint) {
                    if (!is_array($rawPoint)) {
                        continue;
                    }

                    $lat = $this->parseFloat($rawPoint['lat'] ?? $rawPoint['latitude'] ?? null);
                    $lng = $this->parseFloat($rawPoint['lng'] ?? $rawPoint['longitude'] ?? null);
                    if ($lat === null || $lng === null) {
                        continue;
                    }
                    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                        continue;
                    }

                    $speed = $this->parseFloat($rawPoint['speed_kph'] ?? null);
                    $recordedAt = $this->parseClientTimestamp($rawPoint['recorded_at'] ?? $rawPoint['timestamp'] ?? null);

                    $rows[] = [
                        'trip_id' => $trip->id,
                        'sequence' => $sequence++,
                        'has_fix' => true,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'speed_kph' => $speed !== null ? (int) round($speed) : null,
                        'recorded_at' => $recordedAt,
                        'created_at' => now(),
                    ];
                }

                if (!empty($rows)) {
                    TripPoint::insert($rows);
                    $points = TripPoint::where('trip_id', $trip->id)->orderBy('sequence')->get();
                    \Illuminate\Support\Facades\Log::info('TrackingStop: persisted client route points fallback', [
                        'trip_id' => $trip->id,
                        'points_count' => count($rows),
                    ]);
                }
            }
        }

        $totalDistanceMeters = 0;
        $totalEstDistanceMeters = 0; // fallback dari est_distance_m ESP32
        $maxSpeedKph = 0;
        $sumSpeed = 0;
        $countSpeed = 0;

        $lastPoint = null;
        foreach ($points as $point) {
            if ($lastPoint) {
                $totalDistanceMeters += $this->calculateHaversineDistance(
                    $lastPoint->latitude, $lastPoint->longitude,
                    $point->latitude, $point->longitude
                );
            }
            // Akumulasi est_distance_m (dikirim ESP32 per interval GPS)
            if ($point->est_distance_m !== null) {
                $totalEstDistanceMeters += $point->est_distance_m;
            }
            if ($point->speed_kph !== null) {
                $sumSpeed += $point->speed_kph;
                $countSpeed++;
                if ($point->speed_kph > $maxSpeedKph) {
                    $maxSpeedKph = $point->speed_kph;
                }
            }
            $lastPoint = $point;
        }

        // Jika Haversine 0 (GPS fix tidak konsisten), pakai est_distance_m ESP32
        if ($totalDistanceMeters == 0 && $totalEstDistanceMeters > 0) {
            $totalDistanceMeters = $totalEstDistanceMeters;
        }

        // Ultimate fallback: gunakan client_distance_meters dari mobile jika backend tidak punya TripPoints
        $clientDistanceMeters = (float) $request->input('client_distance_meters', 0);
        if ($totalDistanceMeters == 0 && $clientDistanceMeters > 0) {
            $totalDistanceMeters = $clientDistanceMeters;
            \Illuminate\Support\Facades\Log::info('TrackingStop: using client-side distance as fallback', [
                'trip_id' => $trip->id,
                'client_distance_meters' => $clientDistanceMeters,
            ]);
        }

        // Fallback statistik kecepatan dari mobile jika backend tidak punya TripPoints valid.
        $clientAvgSpeedKph = (float) $request->input('client_avg_speed_kph', 0);
        $clientMaxSpeedKph = (float) $request->input('client_max_speed_kph', 0);

        $distanceKm = round($totalDistanceMeters / 1000, 2);

        $avgSpeedKph = $countSpeed > 0 ? round($sumSpeed / $countSpeed, 1) : null;
        $maxSpeedKph = $maxSpeedKph > 0 ? $maxSpeedKph : null;

        if ($avgSpeedKph === null || $avgSpeedKph <= 0) {
            if ($durationSeconds > 0 && $distanceKm > 0) {
                $avgSpeedKph = round($distanceKm / ($durationSeconds / 3600), 1);
            } elseif ($clientAvgSpeedKph > 0) {
                $avgSpeedKph = round($clientAvgSpeedKph, 1);
            }
        }

        if (($maxSpeedKph === null || $maxSpeedKph <= 0) && $clientMaxSpeedKph > 0) {
            $maxSpeedKph = round($clientMaxSpeedKph, 2);
        }

        // Sanity check: Max speed logikanya tidak mungkin lebih rendah dari Average speed
        if ($maxSpeedKph !== null && $avgSpeedKph !== null && $maxSpeedKph < $avgSpeedKph) {
            $maxSpeedKph = $avgSpeedKph;
        }

        // Hitung elevation_gain dari baro_rel_alt_m (BMP280) — total kenaikan elevasi
        $elevationGainM = 0;
        $lastAlt = null;
        foreach ($points as $point) {
            $alt = $point->baro_rel_alt_m;
            if ($alt !== null && $lastAlt !== null) {
                $delta = $alt - $lastAlt;
                if ($delta > 0) {
                    $elevationGainM += $delta; // hanya akumulasi kenaikan
                }
            }
            if ($alt !== null) {
                $lastAlt = $alt;
            }
        }
        $elevationGainM = (int) round($elevationGainM);

        $startOdometer = $trip->start_odometer ?? ($vehicle->odometer ?? 0);
        $endOdometer = $startOdometer + $distanceKm;

        $trip->update([
            'status' => 'completed',
            'end_at' => $endAt,
            'duration_minutes' => $durationMinutes,
            'distance_meters' => (int) $totalDistanceMeters,
            'start_odometer' => $startOdometer,
            'end_odometer' => $endOdometer,
            'avg_speed_kph' => $avgSpeedKph,
            'max_speed_kph' => $maxSpeedKph,
            'elevation_gain' => $elevationGainM,
        ]);

        // Update vehicle odometer
        $vehicle->update([
            'odometer' => $endOdometer,
        ]);

        return response()->json([
            'message' => 'Tracking stopped successfully',
            'trip' => $trip->fresh(),
            'summary' => [
                'distance_km'      => $distanceKm,
                'duration_minutes' => $durationMinutes,
                'avg_speed_kph'    => $avgSpeedKph,
                'max_speed_kph'    => $maxSpeedKph,
                'elevation_gain_m' => $elevationGainM,
                'new_odometer'     => $endOdometer,
                'trip_points_count' => $points->count(),   // info debug
                'used_client_distance' => ($points->count() === 0 && $clientDistanceMeters > 0),
            ],
        ], 200);
    }

    public function status(Request $request, $motorId)
    {
        $vehicle = Vehicle::where('id', $motorId)->where('user_id', $request->user()->id)->firstOrFail();

        $activeTrip = Trip::where('vehicle_id', $vehicle->id)->where('status', 'active')->first();

        return response()->json([
            'is_tracking' => $activeTrip ? true : false,
            'active_trip' => $activeTrip
        ], 200);
    }

    public function lastLocation(Request $request, $motorId)
    {
        $vehicle = Vehicle::where('id', $motorId)->where('user_id', $request->user()->id)->firstOrFail();

        $lat = $vehicle->last_latitude;
        $lng = $vehicle->last_longitude;
        $hasCoordinates = $lat !== null && $lng !== null;
        $satellites = $vehicle->last_satellites;
        $hdop = $vehicle->last_hdop;
        $gpsReady = $hasCoordinates
            && $satellites !== null
            && $hdop !== null
            && (int) $satellites >= 4
            && (float) $hdop <= 5.0;

        // Hitung status IoT online/offline berdasarkan last_telemetry_received_at
        $lastReceived = $vehicle->last_telemetry_received_at;
        $secondsAgo = $lastReceived ? now()->diffInSeconds($lastReceived) : null;
        $iotStatus = match(true) {
            $secondsAgo === null      => 'unknown',
            $secondsAgo <= 15        => 'online',
            $secondsAgo <= 60        => 'unstable',
            default                  => 'offline',
        };

        return response()->json([
            'latitude'        => $hasCoordinates ? (float) $lat : null,
            'longitude'       => $hasCoordinates ? (float) $lng : null,
            'speed_kph'       => $vehicle->last_speed_kph,
            'heading_deg'     => $vehicle->last_heading_deg,
            'altitude'        => $vehicle->last_altitude,
            'accuracy_meters' => $vehicle->last_accuracy_meters,
            'satellites'      => $satellites,
            'hdop'            => $hdop,
            'baro_rel_alt_m'  => $vehicle->last_baro_rel_alt_m,
            'grade_pct'       => $vehicle->last_grade_pct,
            'gps_ready'       => $gpsReady,
            'telemetry_at'    => $vehicle->last_telemetry_at
                                    ? $vehicle->last_telemetry_at->toISOString()
                                    : null,
            'received_at'     => $lastReceived ? $lastReceived->toISOString() : null,
            'iot_status'      => $iotStatus,
            'seconds_ago'     => $secondsAgo,
        ], 200);
    }

    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function parseFloat($value): ?float
    {
        if ($value === null) {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function parseClientTimestamp($value): Carbon
    {
        try {
            if (is_int($value) || is_float($value)) {
                $numeric = (float) $value;
                if ($numeric > 1_000_000_000_000) {
                    return Carbon::createFromTimestampMs((int) round($numeric));
                }
                if ($numeric > 0) {
                    return Carbon::createFromTimestamp((int) round($numeric));
                }
            }

            if (is_string($value) && $value !== '') {
                return Carbon::parse($value);
            }
        } catch (\Throwable) {
            // fallback below
        }

        return now();
    }
}


