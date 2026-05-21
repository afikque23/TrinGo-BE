<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Trip;
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

        $trip->update([
            'status' => 'completed',
            'end_at' => $endAt,
            'duration_minutes' => $durationMinutes
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
}

