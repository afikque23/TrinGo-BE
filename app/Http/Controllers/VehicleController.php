<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\ServiceInterval;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Traits\ApiResponse;
use App\Traits\HasOwnerIdentification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    use ApiResponse, HasOwnerIdentification;

    /**
     * Display a listing of the user's vehicles.
     * Requires authentication.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            
            $vehicles = $query->with(['serviceIntervals' => function ($query) {
                    $query->where('is_active', true)->orderBy('next_due_km');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse(
                $vehicles,
                'Daftar kendaraan berhasil diambil',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error fetching vehicles: ' . $e->getMessage());
            return $this->errorResponse(
                'Gagal mengambil daftar kendaraan',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Store a newly created vehicle in storage.
     * Requires authentication. First vehicle is automatically set as primary.
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = time() . '_' . $photo->getClientOriginalName();
                $path = $photo->storeAs('vehicles', $filename, 'public');
                $validated['photo_url'] = $path;
            }
            
            // Set owner (user_id) and optional device tracking
            $ownerData = $this->getOwnerData($request);
            $validated = array_merge($validated, $ownerData);
            
            // Auto-set as primary if this is owner's first vehicle
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            $existingVehicleCount = $query->count();
            if ($existingVehicleCount === 0) {
                $validated['is_primary'] = true;
            }
            
            // Create vehicle
            $vehicle = Vehicle::create($validated);
            
            // Generate default service intervals based on tipe_motor
            $this->generateServiceIntervals($vehicle);
            
            // Load relationships
            $vehicle->load('serviceIntervals');
            
            // Trigger fuzzy check so user gets immediate notifications for critical components
            \App\Events\VehicleStatusChecked::dispatch($vehicle);
            
            DB::commit();
            
            $message = $vehicle->is_primary 
                ? 'Kendaraan berhasil ditambahkan dan ditetapkan sebagai motor utama'
                : 'Kendaraan berhasil ditambahkan';
            
            return $this->successResponse(
                $vehicle,
                $message,
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded photo if exists
            if (isset($validated['photo_url'])) {
                Storage::disk('public')->delete($validated['photo_url']);
            }
            
            Log::error('Error creating vehicle: ' . $e->getMessage());
            return $this->errorResponse(
                'Gagal menambahkan kendaraan',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Display the specified vehicle.
     * Requires authentication.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            
            $vehicle = $query->with([
                    'serviceIntervals' => function ($query) {
                        $query->where('is_active', true)->orderBy('next_due_km');
                    },
                    'serviceHistories' => function ($query) {
                        $query->latest()->limit(5);
                    },
                    'fuelLogs' => function ($query) {
                        $query->latest()->limit(5);
                    },
                    'reminders' => function ($query) {
                        $query->where('is_completed', false)->latest();
                    }
                ])
                ->findOrFail($id);

            return $this->successResponse(
                $vehicle,
                'Detail kendaraan berhasil diambil',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Kendaraan tidak ditemukan',
                404
            );
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle: ' . $e->getMessage());
            return $this->errorResponse(
                'Gagal mengambil detail kendaraan',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update the specified vehicle in storage.
     * Requires authentication.
     */
    public function update(UpdateVehicleRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            $vehicle = $query->findOrFail($id);
            
            $validated = $request->validated();
            $oldPhotoUrl = $vehicle->photo_url;
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = time() . '_' . $photo->getClientOriginalName();
                $path = $photo->storeAs('vehicles', $filename, 'public');
                $validated['photo_url'] = $path;
                
                // Delete old photo
                if ($oldPhotoUrl && Storage::disk('public')->exists($oldPhotoUrl)) {
                    Storage::disk('public')->delete($oldPhotoUrl);
                }
            }
            
            // If tipe_motor changed, regenerate service intervals
            $tipeMotorChanged = isset($validated['tipe_motor']) && 
                                $validated['tipe_motor'] !== $vehicle->tipe_motor;
            
            // Update vehicle
            $vehicle->update($validated);
            
            // Regenerate service intervals if tipe_motor changed
            if ($tipeMotorChanged) {
                // Deactivate old intervals
                $vehicle->serviceIntervals()->update(['is_active' => false]);
                
                // Generate new intervals
                $this->generateServiceIntervals($vehicle);
            }
            
            // Regenerate cache
            $this->forgetVehicleCache($vehicle->id);
            
            // Trigger fuzzy check on update as well
            \App\Events\VehicleStatusChecked::dispatch($vehicle);
            
            // Load relationships
            $vehicle->load('serviceIntervals');
            
            DB::commit();
            
            return $this->successResponse(
                $vehicle,
                'Kendaraan berhasil diperbarui',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Kendaraan tidak ditemukan',
                404
            );
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded photo if exists
            if (isset($validated['photo_url'])) {
                Storage::disk('public')->delete($validated['photo_url']);
            }
            
            Log::error('Error updating vehicle: ' . $e->getMessage());
            return $this->errorResponse(
                'Gagal memperbarui kendaraan',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified vehicle from storage.
     * Requires authentication.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            $vehicle = $query->findOrFail($id);
            
            $photoUrl = $vehicle->photo_url;
            
            // Soft delete vehicle (cascade akan handle relations)
            $vehicle->delete();
            
            // Delete photo
            if ($photoUrl && Storage::disk('public')->exists($photoUrl)) {
                Storage::disk('public')->delete($photoUrl);
            }
            
            DB::commit();
            
            return $this->successResponse(
                null,
                'Kendaraan berhasil dihapus',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Kendaraan tidak ditemukan',
                404
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting vehicle: ' . $e->getMessage());
            return $this->errorResponse(
                'Gagal menghapus kendaraan',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Generate default service intervals for a vehicle based on tipe_motor.
     */
    private function generateServiceIntervals(Vehicle $vehicle): void
    {
        $defaultIntervals = ServiceInterval::getDefaultIntervals($vehicle->tipe_motor);
        $currentOdometer = $vehicle->odometer ?? 0;
        
        foreach ($defaultIntervals as $interval) {
            ServiceInterval::create([
                'vehicle_id' => $vehicle->id,
                'service_name' => $interval['service_name'],
                'service_type' => $interval['service_type'],
                'interval_km' => $interval['interval_km'],
                'next_due_km' => $currentOdometer + $interval['interval_km'],
                'description' => $interval['description'],
                'is_active' => true,
            ]);
        }
    }

    /**
     * Set primary vehicle for current user.
     * Requires authentication.
     */
    public function setPrimary(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Verify vehicle exists and belongs to owner
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            $vehicle = $query->findOrFail($id);

            $ownerFilter = $this->getOwnerFilter($request);

            // If the new primary has no device_id yet, transfer the device_id from the previous
            // primary (or any other owned vehicle). This ensures MQTT telemetry for the device
            // is routed to the current primary vehicle without changing firmware/topic.
            if ($vehicle->device_id === null) {
                $previousPrimary = Vehicle::query()
                    ->where($ownerFilter['column'], $ownerFilter['id'])
                    ->where('is_primary', true)
                    ->where('id', '!=', $vehicle->id)
                    ->first();

                $deviceIdToMove = $previousPrimary?->device_id;
                if ($deviceIdToMove === null) {
                    $deviceIdToMove = Vehicle::query()
                        ->where($ownerFilter['column'], $ownerFilter['id'])
                        ->where('id', '!=', $vehicle->id)
                        ->whereNotNull('device_id')
                        ->value('device_id');
                }

                if ($deviceIdToMove !== null) {
                    // Detach device_id from any other vehicle under this owner.
                    Vehicle::query()
                        ->where($ownerFilter['column'], $ownerFilter['id'])
                        ->where('id', '!=', $vehicle->id)
                        ->where('device_id', $deviceIdToMove)
                        ->update(['device_id' => null]);

                    $vehicle->device_id = $deviceIdToMove;
                }
            }
            
            // Unset all other vehicles as primary for this owner
            Vehicle::where($ownerFilter['column'], $ownerFilter['id'])
                ->where('id', '!=', $vehicle->id)
                ->update(['is_primary' => false]);
            
            // Set this vehicle as primary
            $vehicle->is_primary = true;
            $vehicle->save();
            
            // Load vehicle with relationships
            $vehicle->load(['serviceIntervals' => function ($query) {
                $query->where('is_active', true)->orderBy('next_due_km');
            }]);
            
            DB::commit();
            
            return $this->successResponse(
                $vehicle,
                'Motor utama berhasil diubah',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Kendaraan tidak ditemukan atau bukan milik Anda',
                404
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting primary vehicle: ' . $e->getMessage());
            return $this->errorResponse(
                'Gagal mengubah motor utama',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get primary vehicle for current user.
     * Requires authentication.
     */
    public function getPrimary(Request $request): JsonResponse
    {
        try {
            // Get primary vehicle with relationships
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            $vehicle = $query->where('is_primary', true)
                ->with([
                    'serviceIntervals' => function ($query) {
                        $query->where('is_active', true)->orderBy('next_due_km');
                    },
                    'serviceHistories' => function ($query) {
                        $query->latest()->limit(5);
                    },
                    'fuelLogs' => function ($query) {
                        $query->latest()->limit(5);
                    },
                    'reminders' => function ($query) {
                        $query->where('is_completed', false)->latest();
                    }
                ])
                ->first();
            
            // If no primary vehicle found
            if (!$vehicle) {
                return $this->errorResponse(
                    'Belum ada motor utama. Silakan pilih motor utama terlebih dahulu.',
                    404
                );
            }
            
            return $this->successResponse(
                $vehicle,
                'Data motor utama berhasil diambil',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error fetching primary vehicle: ' . $e->getMessage());
            return $this->errorResponse(
                'Gagal mengambil data motor utama',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get service metrics for primary vehicle.
     * Returns distance since last service and distance until next service.
     */
    public function getServiceMetrics(Request $request): JsonResponse
    {
        try {
            // Get primary vehicle
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            $vehicle = $query->where('is_primary', true)->first();

            if (!$vehicle) {
                return $this->errorResponse('Motor utama belum ditetapkan', 404);
            }

            $currentOdometer = $vehicle->odometer ?? 0;

            // Get last service from service histories
            $lastService = $vehicle->serviceHistories()
                ->latest('performed_at')
                ->first();

            $distanceSinceService = 0;
            if ($lastService && $lastService->odometer) {
                $distanceSinceService = max(0, $currentOdometer - $lastService->odometer);
            }

            // Get next service from service schedules
            $nextSchedule = $vehicle->serviceSchedules()
                ->where('schedule_type', 'km')
                ->where('target_km', '>', $currentOdometer)
                ->orderBy('target_km', 'asc')
                ->first();

            $distanceUntilNextService = 0;
            $nextServiceAt = null;
            if ($nextSchedule && $nextSchedule->target_km) {
                $distanceUntilNextService = max(0, $nextSchedule->target_km - $currentOdometer);
                $nextServiceAt = $nextSchedule->target_km;
            }

            return $this->successResponse([
                'current_odometer' => $currentOdometer,
                'distance_since_service' => $distanceSinceService,
                'last_service_odometer' => $lastService?->odometer,
                'distance_until_next_service' => $distanceUntilNextService,
                'next_service_at' => $nextServiceAt,
            ], 'Service metrics berhasil diambil');

        } catch (\Exception $e) {
            Log::error('Error fetching service metrics: ' . $e->getMessage());
            return $this->errorResponse(
                'Gagal mengambil service metrics',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get usage pattern statistics from trip data for primary vehicle.
     * Returns average km/day, weekly total, and usage intensity.
     */
    public function getUsagePattern(Request $request): JsonResponse
    {
        try {
            // Get primary vehicle
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            $vehicle = $query->where('is_primary', true)->first();

            if (!$vehicle) {
                return $this->errorResponse('Motor utama belum ditetapkan', 404);
            }

            // Get trips from last 30 days
            $thirtyDaysAgo = now()->subDays(30);
            $trips = $vehicle->trips()
                ->where('start_at', '>=', $thirtyDaysAgo)
                ->get();

            // Calculate statistics
            $totalDistance = $trips->sum('distance_meters') / 1000; // Convert to km
            $tripCount = $trips->count();

            // Average km per day (last 30 days)
            $averageKmPerDay = $tripCount > 0 ? round($totalDistance / 30, 1) : 0;

            // This week's distance (last 7 days)
            $sevenDaysAgo = now()->subDays(7);
            $weeklyDistance = $vehicle->trips()
                ->where('start_at', '>=', $sevenDaysAgo)
                ->get()
                ->sum('distance_meters') / 1000;

            // Determine usage intensity
            $usageIntensity = 'light'; // default
            if ($averageKmPerDay > 50) {
                $usageIntensity = 'heavy';
            } elseif ($averageKmPerDay > 20) {
                $usageIntensity = 'moderate';
            }

            return $this->successResponse([
                'average_km_per_day' => $averageKmPerDay,
                'weekly_km' => round($weeklyDistance, 1),
                'monthly_km' => round($totalDistance, 1),
                'trip_count_30_days' => $tripCount,
                'usage_intensity' => $usageIntensity,
                'odometer' => $vehicle->odometer ?? 0,
            ], 'Usage pattern berhasil diambil');

        } catch (\Exception $e) {
            Log::error('Error fetching usage pattern: ' . $e->getMessage());
            return $this->errorResponse(
                'Gagal mengambil usage pattern',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

}
