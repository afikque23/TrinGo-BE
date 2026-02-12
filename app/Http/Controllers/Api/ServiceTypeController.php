<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceTypeRequest;
use App\Http\Requests\UpdateServiceTypeRequest;
use App\Http\Resources\ServiceTypeResource;
use App\Models\ServiceType;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceTypeController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of service types.
     * 
     * Query params:
     * - active: 1 (only active) | 0 (only inactive) | null (all)
     * - per_page: jumlah data per halaman (default: 15)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ServiceType::query()->withCount('services');

        // Filter berdasarkan status aktif
        if ($request->has('active')) {
            $isActive = filter_var($request->active, FILTER_VALIDATE_BOOLEAN);
            $query->byStatus($isActive);
        }

        // Ordering
        $query->orderBy('name', 'asc');

        // Pagination
        $perPage = $request->input('per_page', 15);
        $serviceTypes = $query->paginate($perPage);

        return ServiceTypeResource::collection($serviceTypes);
    }

    /**
     * Store a newly created service type.
     */
    public function store(StoreServiceTypeRequest $request): JsonResponse
    {
        $serviceType = ServiceType::create($request->validated());

        return $this->created(
            new ServiceTypeResource($serviceType),
            'Jenis service berhasil ditambahkan.'
        );
    }

    /**
     * Display the specified service type.
     */
    public function show(ServiceType $serviceType): JsonResponse
    {
        $serviceType->loadCount('services');

        return $this->success(
            new ServiceTypeResource($serviceType),
            'Detail jenis service berhasil diambil.'
        );
    }

    /**
     * Update the specified service type.
     */
    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType): JsonResponse
    {
        $serviceType->update($request->validated());

        return $this->success(
            new ServiceTypeResource($serviceType),
            'Jenis service berhasil diperbarui.'
        );
    }

    /**
     * Remove the specified service type.
     */
    public function destroy(ServiceType $serviceType): JsonResponse
    {
        // Cek apakah service type sedang digunakan
        if ($serviceType->services()->exists()) {
            return $this->error(
                'Jenis service tidak dapat dihapus karena masih digunakan pada data servis.',
                422
            );
        }

        $serviceType->delete();

        return $this->success(
            null,
            'Jenis service berhasil dihapus.'
        );
    }

    /**
     * Toggle service type status (active/inactive).
     */
    public function toggleStatus(ServiceType $serviceType): JsonResponse
    {
        $serviceType->update([
            'is_active' => !$serviceType->is_active
        ]);

        $status = $serviceType->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return $this->success(
            new ServiceTypeResource($serviceType),
            "Jenis service berhasil {$status}."
        );
    }
}
