<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddManualDistanceRequest;
use App\Http\Requests\StoreTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Trip;
use App\Models\TripPoint;
use App\Models\Vehicle;
use App\Traits\HasOwnerIdentification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripController extends Controller
{
    use HasOwnerIdentification;

    /**
     * Get all trips for the current user's vehicles (with optional vehicle filter)
     * Requires authentication.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get all vehicle IDs owned by current user (authenticated or guest)
            $vehicleQuery = Vehicle::query();
            $this->applyOwnerFilter($vehicleQuery, $request);
            $vehicleIds = $vehicleQuery->pluck('id')->toArray();

            // If no vehicles found, return empty
            if (empty($vehicleIds)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => [
                        'total' => 0,
                        'limit' => $request->input('limit', 20),
                        'offset' => $request->input('offset', 0),
                    ],
                ], 200);
            }

            // Query trips for user's vehicles
            $query = Trip::with(['vehicle', 'points'])
                ->whereIn('vehicle_id', $vehicleIds);

            // Filter by specific vehicle_id if provided
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

            $total = Trip::whereIn('vehicle_id', $vehicleIds)->count();

            return response()->json([
                'success' => true,
                'data' => TripResource::collection($trips),
                'meta' => [
                    'total' => $total,
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
            /** @var \Illuminate\Contracts\Auth\Guard $auth */
            $auth = auth();
            $user = $auth->user();
            $trip = Trip::create([
                'vehicle_id' => $validated['vehicle_id'],
                'started_by' => $user?->id ?? null, // null for guest mode
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

                // Kirim notifikasi perjalanan selesai dengan odometer terbaru
                $this->sendTripCompletedNotification($vehicle->fresh(), $trip, $distanceKm);
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

    /**
     * Add manual distance to vehicle and create a trip record
     * This is used when user wants to add distance traveled without tracking
     */
    public function addManualDistance(AddManualDistanceRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();

            // Get the vehicle
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            
            // Verify ownership
            if (!$this->canAccessModel($vehicle, $request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this vehicle',
                ], 403);
            }
            
            // Get current odometer reading
            $startOdometer = $vehicle->odometer ?? 0;
            $distanceKm = $validated['distance_km'];
            $endOdometer = $startOdometer + $distanceKm;
            
            // Create a simple trip record for manual distance
            // Use trip_date for both start and end time
            $tripDate = \Carbon\Carbon::parse($validated['trip_date']);
            
            /** @var \Illuminate\Contracts\Auth\Guard $auth */
            $auth = auth();
            $user = $auth->user();
            $trip = Trip::create([
                'vehicle_id' => $validated['vehicle_id'],
                'started_by' => $user?->id ?? null,
                'start_at' => $tripDate,
                'end_at' => $tripDate,
                'distance_meters' => (int) ($distanceKm * 1000),
                'start_odometer' => $startOdometer,
                'end_odometer' => $endOdometer,
                'avg_speed_kph' => null,
                'max_speed_kph' => null,
                'notes' => $validated['notes'] ?? 'Jarak manual ditambahkan',
            ]);

            // Create a single trip point at (0,0) to satisfy the relationship
            // This indicates it's a manual entry without GPS tracking
            TripPoint::create([
                'trip_id' => $trip->id,
                'sequence' => 1,
                'latitude' => 0,
                'longitude' => 0,
                'altitude' => null,
                'speed_kph' => null,
                'accuracy_meters' => null,
                'recorded_at' => $tripDate,
            ]);

            // Update vehicle odometer
            $vehicle->update([
                'odometer' => $endOdometer,
            ]);

            Log::info("Manual distance added and odometer updated", [
                'vehicle_id' => $vehicle->id,
                'old_odometer' => $startOdometer,
                'distance_added' => $distanceKm,
                'new_odometer' => $endOdometer,
            ]);

            // Kirim notifikasi jarak manual ditambahkan dengan odometer terbaru
            $this->sendTripCompletedNotification($vehicle->fresh(), $trip, $distanceKm);

            DB::commit();

            // Load relationships for response
            $trip->load(['vehicle']);

            return response()->json([
                'success' => true,
                'message' => 'Jarak berhasil ditambahkan',
                'data' => [
                    'trip' => new TripResource($trip),
                    'old_odometer' => $startOdometer,
                    'distance_added' => $distanceKm,
                    'new_odometer' => $endOdometer,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding manual distance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan jarak',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kirim notifikasi setelah trip selesai dengan odometer yang sudah ter-update
     */
    private function sendTripCompletedNotification(Vehicle $vehicle, Trip $trip, float $distanceKm): void
    {
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            
            // Reload vehicle untuk pastikan odometer terbaru
            $vehicle->refresh();
            
            // Ambil user (jika authenticated)
            $user = $vehicle->user_id ? \App\Models\User::find($vehicle->user_id) : null;
            
            // Cari template notifikasi untuk trip completion
            $template = \App\Models\NotificationTemplate::where('category_key', 'trip')
                ->where('is_active', true)
                ->first();
            
            if (!$template) {
                Log::info('NotificationService: Tidak ada template aktif untuk kategori trip');
                return;
            }
            
            // Hitung durasi trip
            $duration = $trip->end_at && $trip->start_at 
                ? $trip->start_at->diffInMinutes($trip->end_at) . ' menit'
                : '-';
            
            // Hitung kecepatan rata-rata
            $avgSpeed = $trip->avg_speed_kph ?? '-';
            
            // Kirim notifikasi dengan data odometer terbaru
            $notificationService->sendFromTemplate(
                $template,
                [
                    'distance' => number_format($distanceKm, 1),  // Jarak perjalanan
                    'duration' => $duration,                       // Durasi perjalanan
                    'avg_speed' => $avgSpeed,                      // Kecepatan rata-rata
                    'current_km' => number_format($vehicle->odometer ?? 0), // Odometer TERBARU setelah trip
                ],
                $user,
                null, // device_id tidak lagi digunakan untuk ownership
                $vehicle
            );
            
            Log::info('Trip completion notification sent', [
                'vehicle_id' => $vehicle->id,
                'trip_id' => $trip->id,
                'distance_km' => $distanceKm,
                'new_odometer' => $vehicle->odometer,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send trip completion notification: ' . $e->getMessage());
        }
    }
}
