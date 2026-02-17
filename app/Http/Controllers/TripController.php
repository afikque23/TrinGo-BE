<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Trip;
use App\Models\TripPoint;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripController extends Controller
{
    /**
     * Get all trips (with optional vehicle filter)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Trip::with(['vehicle', 'points']);

            // Filter by vehicle_id if provided
            if ($request->has('vehicle_id')) {
                $query->where('vehicle_id', $request->vehicle_id);
            }

            // Pagination
            $limit = $request->input('limit', 20);
            $offset = $request->input('offset', 0);

            $trips = $query->latest()
                ->skip($offset)
                ->take($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => TripResource::collection($trips),
                'meta' => [
                    'total' => Trip::count(),
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching trips: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trips',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new trip and auto-update vehicle odometer
     */
    public function store(StoreTripRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();

            // Get the vehicle
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            
            // Get current odometer reading
            $startOdometer = $vehicle->odometer ?? 0;
            
            // Convert total_distance from km to meters (if needed)
            // Assuming total_distance is in kilometers from mobile
            $distanceMeters = (int) ($validated['total_distance'] * 1000);
            $distanceKm = $validated['total_distance'];
            
            // Calculate end odometer
            $endOdometer = $startOdometer + $distanceKm;

            // Create trip
            $trip = Trip::create([
                'vehicle_id' => $validated['vehicle_id'],
                'started_by' => auth()->id() ?? null, // null for guest mode
                'start_at' => $validated['start_time'],
                'end_at' => $validated['end_time'] ?? null,
                'distance_meters' => $distanceMeters,
                'start_odometer' => $startOdometer,
                'end_odometer' => $endOdometer,
                'avg_speed_kph' => $validated['average_speed'] ?? null,
                'max_speed_kph' => $validated['max_speed'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create trip points
            foreach ($validated['points'] as $index => $point) {
                TripPoint::create([
                    'trip_id' => $trip->id,
                    'sequence' => $index + 1,
                    'latitude' => $point['latitude'],
                    'longitude' => $point['longitude'],
                    'altitude' => $point['altitude'] ?? null,
                    'speed_kph' => $point['speed'] ?? null,
                    'accuracy_meters' => $point['accuracy'] ?? null,
                    'recorded_at' => $point['timestamp'],
                ]);
            }

            // *** AUTO-UPDATE VEHICLE ODOMETER ***
            // Only update if trip status is "completed"
            if ($validated['status'] === 'completed') {
                $vehicle->update([
                    'odometer' => $endOdometer,
                ]);

                Log::info("Vehicle odometer updated", [
                    'vehicle_id' => $vehicle->id,
                    'old_odometer' => $startOdometer,
                    'distance_added' => $distanceKm,
                    'new_odometer' => $endOdometer,
                ]);
            }

            DB::commit();

            // Load relationships for response
            $trip->load(['vehicle', 'points']);

            return response()->json([
                'success' => true,
                'message' => 'Trip saved successfully',
                'data' => new TripResource($trip),
                'odometer_updated' => $validated['status'] === 'completed',
                'new_odometer' => $validated['status'] === 'completed' ? $endOdometer : null,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving trip: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save trip',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific trip by ID
     */
    public function show($id): JsonResponse
    {
        try {
            $trip = Trip::with(['vehicle', 'points'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new TripResource($trip),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Trip not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update an existing trip
     */
    public function update(Request $request, $id): JsonResponse
    {
        // TODO: Implement trip update logic if needed
        return response()->json([
            'success' => false,
            'message' => 'Trip update not implemented yet',
        ], 501);
    }

    /**
     * Delete a trip
     */
    public function destroy($id): JsonResponse
    {
        try {
            $trip = Trip::findOrFail($id);
            $trip->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Trip deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete trip',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
