<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceTypeRequest;
use App\Http\Requests\UpdateServiceTypeRequest;
use App\Http\Resources\ServiceTypeResource;
use App\Http\Resources\ServiceTypeSimpleResource;
use App\Models\ServiceType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceTypeController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of service types.
     * Use ?active=1 to filter only active types (for mobile dropdown).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = ServiceType::query();

        // Filter by active status if provided
        if ($request->has('active')) {
            $query->where('is_active', (bool) $request->active);
        }

        $serviceTypes = $query->orderBy('name')->get();

        // Use simple resource for mobile dropdown (when active filter is used)
        if ($request->has('active') && $request->active == 1) {
            return $this->successResponse(
                ServiceTypeSimpleResource::collection($serviceTypes),
                'Daftar jenis service aktif berhasil diambil.'
            );
        }

        // Use full resource for admin panel
        return $this->successResponse(
            ServiceTypeResource::collection($serviceTypes),
            'Daftar jenis service berhasil diambil.'
        );
    }

    /**
     * Store a newly created service type.
     *
     * @param StoreServiceTypeRequest $request
     * @return JsonResponse
     */
    public function store(StoreServiceTypeRequest $request): JsonResponse
    {
        $serviceType = ServiceType::create($request->validated());

        return $this->successResponse(
            new ServiceTypeResource($serviceType),
            'Jenis service berhasil ditambahkan.',
            201
        );
    }

    /**
     * Display the specified service type.
     *
     * @param ServiceType $serviceType
     * @return JsonResponse
     */
    public function show(ServiceType $serviceType): JsonResponse
    {
        return $this->successResponse(
            new ServiceTypeResource($serviceType),
            'Detail jenis service berhasil diambil.'
        );
    }

    /**
     * Update the specified service type.
     *
     * @param UpdateServiceTypeRequest $request
     * @param ServiceType $serviceType
     * @return JsonResponse
     */
    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType): JsonResponse
    {
        $serviceType->update($request->validated());

        return $this->successResponse(
            new ServiceTypeResource($serviceType),
            'Jenis service berhasil diperbarui.'
        );
    }

    /**
     * Remove the specified service type.
     *
     * @param ServiceType $serviceType
     * @return JsonResponse
     */
    public function destroy(ServiceType $serviceType): JsonResponse
    {
        // Check if service type is being used
        if ($serviceType->services()->exists()) {
            return $this->errorResponse(
                'Jenis service tidak dapat dihapus karena masih digunakan oleh data service.',
                422
            );
        }

        $serviceType->delete();

        return $this->successResponse(
            null,
            'Jenis service berhasil dihapus.'
        );
    }

    /**
     * Toggle active status of service type.
     *
     * @param ServiceType $serviceType
     * @return JsonResponse
     */
    public function toggleActive(ServiceType $serviceType): JsonResponse
    {
        $serviceType->update([
            'is_active' => !$serviceType->is_active
        ]);

        return $this->successResponse(
            new ServiceTypeResource($serviceType),
            'Status jenis service berhasil diubah.'
        );
    }
}
