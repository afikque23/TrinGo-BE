<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceCollection;
use App\Models\Service;
use App\Models\Vehicle;
use App\Services\ServiceSummaryService;
use App\Traits\ApiResponse;
use App\Traits\HasOwnerIdentification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    use ApiResponse, HasOwnerIdentification;

    protected ServiceSummaryService $summaryService;

    public function __construct(ServiceSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    /**
     * Display a listing of services.
     * Supports filtering by vehicle_id.
     * Only shows services for vehicles owned by current user/device.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Get owner's vehicles first
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownedVehicleIds = $vehicleQuery->pluck('id');

        $query = Service::with(['vehicle', 'serviceType'])
            ->whereIn('vehicle_id', $ownedVehicleIds);

        // Filter by vehicle_id if provided
        if ($request->has('vehicle_id')) {
            $vehicleId = $request->vehicle_id;
            
            // Validate that vehicle belongs to owner
            if (!$ownedVehicleIds->contains($vehicleId)) {
                return $this->errorResponse(
                    'Anda tidak memiliki akses ke kendaraan ini.',
                    403
                );
            }
            
            $query->forVehicle($vehicleId);
        }

        // Order by service date descending
        $services = $query->latest('service_date')->get();

        return $this->successResponse(
            new ServiceCollection($services),
            'Data service berhasil diambil.'
        );
    }

    /**
     * Store a newly created service in storage.
     * Validates that vehicle belongs to current user/device.
     *
     * @param StoreServiceRequest $request
     * @return JsonResponse
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        // Validate vehicle ownership
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $vehicle = $vehicleQuery->find($request->vehicle_id);
        
        if (!$vehicle) {
            return $this->errorResponse(
                'Kendaraan tidak ditemukan atau bukan milik Anda.',
                404
            );
        }

        $service = Service::create($request->validated());
        $service->load(['vehicle', 'serviceType']);

        return $this->successResponse(
            new ServiceResource($service),
            'Service berhasil ditambahkan.',
            201
        );
    }

    /**
     * Display the specified service.
     * Validates that service's vehicle belongs to current user/device.
     *
     * @param Request $request
     * @param Service $service
     * @return JsonResponse
     */
    public function show(Request $request, Service $service): JsonResponse
    {
        // Validate vehicle ownership
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownsVehicle = $vehicleQuery->where('id', $service->vehicle_id)->exists();
        
        if (!$ownsVehicle) {
            return $this->errorResponse(
                'Anda tidak memiliki akses ke data service ini.',
                403
            );
        }

        $service->load(['vehicle', 'serviceType']);

        return $this->successResponse(
            new ServiceResource($service),
            'Detail service berhasil diambil.'
        );
    }

    /**
     * Update the specified service in storage.
     * Validates that service's vehicle belongs to current user/device.
     *
     * @param UpdateServiceRequest $request
     * @param Service $service
     * @return JsonResponse
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        // Validate vehicle ownership
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownsVehicle = $vehicleQuery->where('id', $service->vehicle_id)->exists();
        
        if (!$ownsVehicle) {
            return $this->errorResponse(
                'Anda tidak memiliki akses ke data service ini.',
                403
            );
        }

        $service->update($request->validated());
        $service->load(['vehicle', 'serviceType']);

        return $this->successResponse(
            new ServiceResource($service),
            'Service berhasil diperbarui.'
        );
    }

    /**
     * Remove the specified service from storage.
     * Validates that service's vehicle belongs to current user/device.
     *
     * @param Request $request
     * @param Service $service
     * @return JsonResponse
     */
    public function destroy(Request $request, Service $service): JsonResponse
    {
        // Validate vehicle ownership
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownsVehicle = $vehicleQuery->where('id', $service->vehicle_id)->exists();
        
        if (!$ownsVehicle) {
            return $this->errorResponse(
                'Anda tidak memiliki akses ke data service ini.',
                403
            );
        }

        $service->delete();

        return $this->successResponse(
            null,
            'Service berhasil dihapus.'
        );
    }

    /**
     * Get service summary for a specific vehicle.
     * Validates that vehicle belongs to current user/device.
     *
     * @param Request $request
     * @param int $vehicleId
     * @return JsonResponse
     */
    public function summary(Request $request, int $vehicleId): JsonResponse
    {
        try {
            // Validate vehicle ownership
            $vehicleQuery = Vehicle::query();
            $this->applyOwnerFilter($vehicleQuery, $request);
            $vehicle = $vehicleQuery->findOrFail($vehicleId);

            $summary = $this->summaryService->getSummary($vehicleId);

            return $this->successResponse(
                $summary,
                'Ringkasan service berhasil diambil.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Kendaraan tidak ditemukan atau bukan milik Anda.',
                404
            );
        }
    }

    /**
     * Get cost breakdown by service type for a vehicle.
     * Validates that vehicle belongs to current user/device.
     *
     * @param Request $request
     * @param int $vehicleId
     * @return JsonResponse
     */
    public function costBreakdown(Request $request, int $vehicleId): JsonResponse
    {
        // Validate vehicle ownership
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $vehicle = $vehicleQuery->find($vehicleId);
        
        if (!$vehicle) {
            return $this->errorResponse(
                'Kendaraan tidak ditemukan atau bukan milik Anda.',
                404
            );
        }

        $breakdown = $this->summaryService->getCostBreakdown($vehicleId);

        return $this->successResponse(
            ['breakdown' => $breakdown],
            'Breakdown biaya berhasil diambil.'
        );
    }
}
