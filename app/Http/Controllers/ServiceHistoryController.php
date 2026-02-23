<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceHistoryRequest;
use App\Http\Requests\UpdateServiceHistoryRequest;
use App\Models\ServiceHistory;
use App\Models\Vehicle;
use App\Traits\ApiResponse;
use App\Traits\HasOwnerIdentification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceHistoryController extends Controller
{
    use ApiResponse, HasOwnerIdentification;

    /**
     * Generate file URL using file serving route
     */
    private function getFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Extract filename from path (e.g., 'receipts/file.png' -> 'file.png')
        $filename = basename($path);
        
        // Use the file serving route
        return url('/api/v1/motorcycle/files/receipts/' . $filename);
    }

    /**
     * Display a listing of service histories for the user's primary vehicle.
     * Requires authentication.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get owner's primary vehicle
            $vehicleQuery = Vehicle::query();
            $this->applyOwnerFilter($vehicleQuery, $request);
            $primaryVehicle = $vehicleQuery->where('is_primary', true)->first();

            if (!$primaryVehicle) {
                return $this->errorResponse('Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.', 404);
            }

            // Get service histories for primary vehicle, ordered by date (newest first)
            $serviceHistories = ServiceHistory::where('vehicle_id', $primaryVehicle->id)
                ->orderBy('performed_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'service_type' => $service->service_type,
                        'performed_at' => $service->performed_at->format('Y-m-d'),
                        'odometer' => $service->odometer,
                        'cost' => $service->cost_cents ? $service->cost_cents / 100 : null,
                        'currency' => $service->currency,
                        'service_provider' => $service->service_provider,
                        'receipt_url' => $this->getFileUrl($service->receipt_url),
                        'notes' => $service->notes,
                        'created_at' => $service->created_at->toIso8601String(),
                    ];
                });

            return $this->successResponse([
                'service_histories' => $serviceHistories,
                'total' => $serviceHistories->count(),
                'vehicle' => [
                    'id' => $primaryVehicle->id,
                    'name' => $primaryVehicle->name,
                    'plate_number' => $primaryVehicle->plate_number,
                ],
            ], 'Riwayat servis berhasil diambil.');

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil riwayat servis: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created service history.
     * Requires authentication.
     */
    public function store(StoreServiceHistoryRequest $request): JsonResponse
    {
        try {
            // Get owner's primary vehicle
            $vehicleQuery = Vehicle::query();
            $this->applyOwnerFilter($vehicleQuery, $request);
            $primaryVehicle = $vehicleQuery->where('is_primary', true)->first();

            if (!$primaryVehicle) {
                return $this->errorResponse('Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.', 404);
            }

            $validated = $request->validated();

            // Handle receipt photo upload
            $receiptUrl = null;
            if ($request->hasFile('receipt_photo')) {
                $file = $request->file('receipt_photo');
                $ownerFilter = $this->getOwnerFilter($request);
                $ownerId = $ownerFilter['id'];
                $filename = time() . '_' . $ownerId . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('receipts', $filename, 'public');
                $receiptUrl = $path;
            }

            // Convert cost to cents for storage
            $costCents = null;
            if (isset($validated['cost'])) {
                $costCents = (int) ($validated['cost'] * 100);
            }

            // Create service history
            $serviceHistory = ServiceHistory::create([
                'vehicle_id' => $primaryVehicle->id,
                'service_type' => $validated['service_type'],
                'performed_at' => $validated['performed_at'],
                'odometer' => $validated['odometer'] ?? null,
                'cost_cents' => $costCents,
                'currency' => $validated['currency'] ?? 'IDR',
                'service_provider' => $validated['service_provider'] ?? null,
                'receipt_url' => $receiptUrl,
                'notes' => $validated['notes'] ?? null,
            ]);

            return $this->successResponse([
                'service_history' => [
                    'id' => $serviceHistory->id,
                    'service_type' => $serviceHistory->service_type,
                    'performed_at' => $serviceHistory->performed_at->format('Y-m-d'),
                    'odometer' => $serviceHistory->odometer,
                    'cost' => $serviceHistory->cost_cents ? $serviceHistory->cost_cents / 100 : null,
                    'currency' => $serviceHistory->currency,
                    'service_provider' => $serviceHistory->service_provider,
                    'receipt_url' => $this->getFileUrl($serviceHistory->receipt_url),
                    'notes' => $serviceHistory->notes,
                    'created_at' => $serviceHistory->created_at->toIso8601String(),
                ],
            ], 'Riwayat servis berhasil ditambahkan.', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menambahkan riwayat servis: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified service history.
     * Requires authentication.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            // Get owner's primary vehicle
            $vehicleQuery = Vehicle::query();
            $this->applyOwnerFilter($vehicleQuery, $request);
            $primaryVehicle = $vehicleQuery->where('is_primary', true)->first();

            if (!$primaryVehicle) {
                return $this->errorResponse('Motor utama belum ditetapkan.', 404);
            }

            // Get service history for primary vehicle only
            $serviceHistory = ServiceHistory::where('id', $id)
                ->where('vehicle_id', $primaryVehicle->id)
                ->first();

            if (!$serviceHistory) {
                return $this->errorResponse('Riwayat servis tidak ditemukan.', 404);
            }

            return $this->successResponse([
                'service_history' => [
                    'id' => $serviceHistory->id,
                    'service_type' => $serviceHistory->service_type,
                    'performed_at' => $serviceHistory->performed_at->format('Y-m-d'),
                    'odometer' => $serviceHistory->odometer,
                    'cost' => $serviceHistory->cost_cents ? $serviceHistory->cost_cents / 100 : null,
                    'currency' => $serviceHistory->currency,
                    'service_provider' => $serviceHistory->service_provider,
                    'receipt_url' => $this->getFileUrl($serviceHistory->receipt_url),
                    'notes' => $serviceHistory->notes,
                    'created_at' => $serviceHistory->created_at->toIso8601String(),
                    'updated_at' => $serviceHistory->updated_at->toIso8601String(),
                ],
            ], 'Detail riwayat servis berhasil diambil.');

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil detail riwayat servis: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified service history.
     * Requires authentication.
     */
    public function update(UpdateServiceHistoryRequest $request, string $id): JsonResponse
    {
        try {
            // Get owner's primary vehicle
            $vehicleQuery = Vehicle::query();
            $this->applyOwnerFilter($vehicleQuery, $request);
            $primaryVehicle = $vehicleQuery->where('is_primary', true)->first();

            if (!$primaryVehicle) {
                return $this->errorResponse('Motor utama belum ditetapkan.', 404);
            }

            // Get service history for primary vehicle only
            $serviceHistory = ServiceHistory::where('id', $id)
                ->where('vehicle_id', $primaryVehicle->id)
                ->first();

            if (!$serviceHistory) {
                return $this->errorResponse('Riwayat servis tidak ditemukan.', 404);
            }

            $validated = $request->validated();

            // Handle receipt photo upload
            if ($request->hasFile('receipt_photo')) {
                // Delete old receipt if exists
                if ($serviceHistory->receipt_url) {
                    Storage::disk('public')->delete($serviceHistory->receipt_url);
                }

                $file = $request->file('receipt_photo');
                $ownerFilter = $this->getOwnerFilter($request);
                $ownerId = $ownerFilter['id'];
                $filename = time() . '_' . $ownerId . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('receipts', $filename, 'public');
                $validated['receipt_url'] = $path;
            }

            // Convert cost to cents if provided
            if (isset($validated['cost'])) {
                $validated['cost_cents'] = (int) ($validated['cost'] * 100);
                unset($validated['cost']);
            }

            // Update service history
            $serviceHistory->update($validated);

            return $this->successResponse([
                'service_history' => [
                    'id' => $serviceHistory->id,
                    'service_type' => $serviceHistory->service_type,
                    'performed_at' => $serviceHistory->performed_at->format('Y-m-d'),
                    'odometer' => $serviceHistory->odometer,
                    'cost' => $serviceHistory->cost_cents ? $serviceHistory->cost_cents / 100 : null,
                    'currency' => $serviceHistory->currency,
                    'service_provider' => $serviceHistory->service_provider,
                    'receipt_url' => $this->getFileUrl($serviceHistory->receipt_url),
                    'notes' => $serviceHistory->notes,
                    'updated_at' => $serviceHistory->updated_at->toIso8601String(),
                ],
            ], 'Riwayat servis berhasil diperbarui.');

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memperbarui riwayat servis: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified service history.
     * Requires authentication.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            // Get owner's primary vehicle
            $vehicleQuery = Vehicle::query();
            $this->applyOwnerFilter($vehicleQuery, $request);
            $primaryVehicle = $vehicleQuery->where('is_primary', true)->first();

            if (!$primaryVehicle) {
                return $this->errorResponse('Motor utama belum ditetapkan.', 404);
            }

            // Get service history for primary vehicle only
            $serviceHistory = ServiceHistory::where('id', $id)
                ->where('vehicle_id', $primaryVehicle->id)
                ->first();

            if (!$serviceHistory) {
                return $this->errorResponse('Riwayat servis tidak ditemukan.', 404);
            }

            // Delete receipt file if exists
            if ($serviceHistory->receipt_url) {
                Storage::disk('public')->delete($serviceHistory->receipt_url);
            }

            // Delete service history (soft delete)
            $serviceHistory->delete();

            return $this->successResponse(null, 'Riwayat servis berhasil dihapus.');

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menghapus riwayat servis: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get cost summary and analysis for service histories.
     * Requires authentication.
     */
    public function costSummary(Request $request): JsonResponse
    {
        try {
            // Get owner's primary vehicle
            $vehicleQuery = Vehicle::query();
            $this->applyOwnerFilter($vehicleQuery, $request);
            $primaryVehicle = $vehicleQuery->where('is_primary', true)->first();

            if (!$primaryVehicle) {
                return $this->errorResponse('Motor utama belum ditetapkan. Silakan atur motor utama terlebih dahulu.', 404);
            }

            // Get period filter (default: all time)
            $period = $request->query('period', 'all'); // all, year, month
            $year = $request->query('year', now()->year);
            $month = $request->query('month', now()->month);

            $query = ServiceHistory::where('vehicle_id', $primaryVehicle->id);

            // Apply period filter
            if ($period === 'year') {
                $query->whereYear('performed_at', $year);
            } elseif ($period === 'month') {
                $query->whereYear('performed_at', $year)
                    ->whereMonth('performed_at', $month);
            }

            // Get service histories
            $serviceHistories = $query->get();

            // Calculate totals
            $totalCost = $serviceHistories->sum('cost_cents') / 100;
            $totalServices = $serviceHistories->count();
            $averageCost = $totalServices > 0 ? $totalCost / $totalServices : 0;

            // Group by service type
            $costByServiceType = $serviceHistories->groupBy('service_type')->map(function ($services, $type) {
                return [
                    'service_type' => $type,
                    'total_cost' => $services->sum('cost_cents') / 100,
                    'count' => $services->count(),
                    'average_cost' => $services->avg('cost_cents') / 100,
                ];
            })->values();

            // Group by month (for yearly view)
            $costByMonth = [];
            if ($period === 'year') {
                $costByMonth = $serviceHistories->groupBy(function ($service) {
                    return $service->performed_at->format('Y-m');
                })->map(function ($services, $month) {
                    return [
                        'month' => $month,
                        'total_cost' => $services->sum('cost_cents') / 100,
                        'count' => $services->count(),
                    ];
                })->values();
            }

            // Get most expensive service
            $mostExpensiveService = $serviceHistories->sortByDesc('cost_cents')->first();
            $mostExpensive = null;
            if ($mostExpensiveService) {
                $mostExpensive = [
                    'id' => $mostExpensiveService->id,
                    'service_type' => $mostExpensiveService->service_type,
                    'cost' => $mostExpensiveService->cost_cents / 100,
                    'performed_at' => $mostExpensiveService->performed_at->format('Y-m-d'),
                ];
            }

            // Get last service date
            $lastService = $serviceHistories->sortByDesc('performed_at')->first();
            $lastServiceDate = $lastService ? $lastService->performed_at->format('Y-m-d') : null;

            return $this->successResponse([
                'summary' => [
                    'period' => $period,
                    'year' => $period !== 'all' ? $year : null,
                    'month' => $period === 'month' ? $month : null,
                    'total_cost' => round($totalCost, 2),
                    'total_services' => $totalServices,
                    'average_cost' => round($averageCost, 2),
                    'currency' => 'IDR',
                    'last_service_date' => $lastServiceDate,
                ],
                'cost_by_service_type' => $costByServiceType,
                'cost_by_month' => $costByMonth,
                'most_expensive_service' => $mostExpensive,
                'vehicle' => [
                    'id' => $primaryVehicle->id,
                    'name' => $primaryVehicle->name,
                    'plate_number' => $primaryVehicle->plate_number,
                ],
            ], 'Ringkasan biaya servis berhasil diambil.');

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil ringkasan biaya: ' . $e->getMessage(), 500);
        }
    }
}
