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
        $durationMinutes = $startAt->diffInMinutes($endAt);

        // Calculate distance and speed from trip points
        $points = TripPoint::where('trip_id', $trip->id)->orderBy('sequence')->get();
        $totalDistanceMeters = 0;
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
            if ($point->speed_kph !== null) {
                $sumSpeed += $point->speed_kph;
                $countSpeed++;
                if ($point->speed_kph > $maxSpeedKph) {
                    $maxSpeedKph = $point->speed_kph;
                }
            }
            $lastPoint = $point;
        }

        $avgSpeedKph = $countSpeed > 0 ? round($sumSpeed / $countSpeed, 2) : null;
        $maxSpeedKph = $maxSpeedKph > 0 ? $maxSpeedKph : null;
        
        $startOdometer = $trip->start_odometer ?? ($vehicle->odometer ?? 0);
        $distanceKm = round($totalDistanceMeters / 1000, 2);
        $endOdometer = (int) ($startOdometer + $distanceKm);

        $trip->update([
            'status' => 'completed',
            'end_at' => $endAt,
            'duration_minutes' => $durationMinutes,
            'distance_meters' => (int) $totalDistanceMeters,
            'start_odometer' => $startOdometer,
            'end_odometer' => $endOdometer,
            'avg_speed_kph' => $avgSpeedKph,
            'max_speed_kph' => $maxSpeedKph,
        ]);

        // Update vehicle odometer
        $vehicle->update([
            'odometer' => $endOdometer,
        ]);

        return response()->json([
            'message' => 'Tracking stopped successfully',
            'trip' => $trip
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

        return response()->json([
            'latitude' => $vehicle->last_latitude ? (float) $vehicle->last_latitude : null,
            'longitude' => $vehicle->last_longitude ? (float) $vehicle->last_longitude : null,
            'speed_kph' => $vehicle->last_speed_kph,
            'heading_deg' => $vehicle->last_heading_deg,
            'altitude' => $vehicle->last_altitude,
            'accuracy_meters' => $vehicle->last_accuracy_meters,
            'telemetry_at' => $vehicle->last_telemetry_at ? $vehicle->last_telemetry_at->toISOString() : null,
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
}


