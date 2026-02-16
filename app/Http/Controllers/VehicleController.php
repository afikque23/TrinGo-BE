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
     * Supports both authenticated users and guest mode (device_id)
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
     * Supports both authenticated users and guest mode (device_id)
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
            
            // Set owner (user_id atau device_id)
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
     * Supports both authenticated users and guest mode (device_id)
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
     * Supports both authenticated users and guest mode (device_id)
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
     * Supports both authenticated users and guest mode (device_id)
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
     * Set primary vehicle for current owner.
     * Supports both authenticated users and guest mode (device_id)
     */
    public function setPrimary(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Verify vehicle exists and belongs to owner
            $query = Vehicle::query();
            $this->applyOwnerFilter($query, $request);
            $vehicle = $query->findOrFail($id);
            
            // Unset all other vehicles as primary for this owner
            $ownerFilter = $this->getOwnerFilter($request);
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
     * Get primary vehicle for current owner.
     * Supports both authenticated users and guest mode (device_id)
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

}
