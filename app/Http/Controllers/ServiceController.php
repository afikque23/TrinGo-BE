<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceCollection;
use App\Models\Service;
use App\Services\ServiceSummaryService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    use ApiResponse;

    protected ServiceSummaryService $summaryService;

    public function __construct(ServiceSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    /**
     * Display a listing of services.
     * Supports filtering by vehicle_id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Service::with(['vehicle', 'serviceType']);

        // Filter by vehicle_id if provided
        if ($request->has('vehicle_id')) {
            $query->forVehicle($request->vehicle_id);
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
     *
     * @param StoreServiceRequest $request
     * @return JsonResponse
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
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
     *
     * @param Service $service
     * @return JsonResponse
     */
    public function show(Service $service): JsonResponse
    {
        $service->load(['vehicle', 'serviceType']);

        return $this->successResponse(
            new ServiceResource($service),
            'Detail service berhasil diambil.'
        );
    }

    /**
     * Update the specified service in storage.
     *
     * @param UpdateServiceRequest $request
     * @param Service $service
     * @return JsonResponse
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $service->update($request->validated());
        $service->load(['vehicle', 'serviceType']);

        return $this->successResponse(
            new ServiceResource($service),
            'Service berhasil diperbarui.'
        );
    }

    /**
     * Remove the specified service from storage.
     *
     * @param Service $service
     * @return JsonResponse
     */
    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return $this->successResponse(
            null,
            'Service berhasil dihapus.'
        );
    }

    /**
     * Get service summary for a specific vehicle.
     *
     * @param int $vehicleId
     * @return JsonResponse
     */
    public function summary(int $vehicleId): JsonResponse
    {
        try {
            $summary = $this->summaryService->getSummary($vehicleId);

            return $this->successResponse(
                $summary,
                'Ringkasan service berhasil diambil.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Kendaraan tidak ditemukan.',
                404
            );
        }
    }

    /**
     * Get cost breakdown by service type for a vehicle.
     *
     * @param int $vehicleId
     * @return JsonResponse
     */
    public function costBreakdown(int $vehicleId): JsonResponse
    {
        $breakdown = $this->summaryService->getCostBreakdown($vehicleId);

        return $this->successResponse(
            ['breakdown' => $breakdown],
            'Breakdown biaya berhasil diambil.'
        );
    }
}
