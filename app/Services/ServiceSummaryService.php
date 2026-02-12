<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServiceSummaryService
{
    /**
     * Get service summary for a specific vehicle.
     *
     * @param int $vehicleId
     * @return array
     * @throws ModelNotFoundException
     */
    public function getSummary(int $vehicleId): array
    {
        // Verify vehicle exists
        $vehicle = Vehicle::findOrFail($vehicleId);

        // Get all services for this vehicle
        $services = Service::forVehicle($vehicleId)->get();

        // Calculate summary
        $totalServices = $services->count();
        $totalCost = $services->sum('cost');

        // Get last service
        $lastService = Service::forVehicle($vehicleId)
            ->latest('service_date')
            ->first();

        return [
            'vehicle_id' => $vehicleId,
            'vehicle' => [
                'id' => $vehicle->id,
                'title' => $vehicle->title,
                'license_plate' => $vehicle->license_plate,
            ],
            'total_services' => $totalServices,
            'total_cost' => (float) $totalCost,
            'last_service_date' => $lastService?->service_date?->format('Y-m-d'),
            'last_service_km' => $lastService?->odometer_km,
            'last_service' => $lastService ? [
                'id' => $lastService->id,
                'service_type' => $lastService->serviceType->name,
                'workshop_name' => $lastService->workshop_name,
                'cost' => (float) $lastService->cost,
            ] : null,
        ];
    }

    /**
     * Get cost breakdown by service type for a vehicle.
     *
     * @param int $vehicleId
     * @return array
     */
    public function getCostBreakdown(int $vehicleId): array
    {
        $breakdown = Service::forVehicle($vehicleId)
            ->selectRaw('service_type_id, SUM(cost) as total_cost, COUNT(*) as count')
            ->with('serviceType:id,name')
            ->groupBy('service_type_id')
            ->get()
            ->map(function ($item) {
                return [
                    'service_type' => $item->serviceType->name,
                    'total_cost' => (float) $item->total_cost,
                    'count' => $item->count,
                    'average_cost' => (float) ($item->total_cost / $item->count),
                ];
            })
            ->sortByDesc('total_cost')
            ->values();

        return $breakdown->toArray();
    }

    /**
     * Get service history with pagination.
     *
     * @param int $vehicleId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getHistory(int $vehicleId, int $perPage = 15)
    {
        return Service::forVehicle($vehicleId)
            ->with(['serviceType', 'vehicle'])
            ->latest('service_date')
            ->paginate($perPage);
    }
}
