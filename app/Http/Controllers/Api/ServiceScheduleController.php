<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceScheduleRequest;
use App\Http\Requests\UpdateServiceScheduleRequest;
use App\Http\Resources\ServiceScheduleResource;
use App\Models\ServiceSchedule;
use App\Models\Vehicle;
use App\Services\ServiceScheduleService;
use App\Traits\ApiResponse;
use App\Traits\HasOwnerIdentification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceScheduleController extends Controller
{
    use ApiResponse, HasOwnerIdentification;

    protected ServiceScheduleService $scheduleService;

    public function __construct(ServiceScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display a listing of service schedules.
     * Only shows schedules for vehicles owned by current user/device.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Get owner's vehicles first
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownedVehicleIds = $vehicleQuery->pluck('id');

        $query = ServiceSchedule::with(['vehicle', 'serviceType', 'reminderOption'])
            ->whereIn('vehicle_id', $ownedVehicleIds);

        // Filter by vehicle_id if provided
        if ($request->has('vehicle_id')) {
            $vehicleId = $request->vehicle_id;
            
            // Validate that vehicle belongs to owner
            if (!$ownedVehicleIds->contains($vehicleId)) {
                return ServiceScheduleResource::collection(collect([]));
            }
            
            $query->forVehicle($vehicleId);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by schedule type if provided
        if ($request->has('schedule_type')) {
            $query->byType($request->schedule_type);
        }

        $schedules = $query->latest()->get();

        return ServiceScheduleResource::collection($schedules);
    }

    /**
     * Store a newly created service schedule.
     * Validates that vehicle belongs to current user/device.
     *
     * @param StoreServiceScheduleRequest $request
     * @return JsonResponse
     */
    public function store(StoreServiceScheduleRequest $request): JsonResponse
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

        $schedule = ServiceSchedule::create($request->validated());

        $schedule->load(['vehicle', 'serviceType', 'reminderOption']);

        return $this->created(
            new ServiceScheduleResource($schedule),
            'Service schedule created successfully.'
        );
    }

    /**
     * Display the specified service schedule.
     * Validates that schedule's vehicle belongs to current user/device.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        // Get owner's vehicles first
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownedVehicleIds = $vehicleQuery->pluck('id');

        $schedule = ServiceSchedule::with(['vehicle', 'serviceType', 'reminderOption'])
            ->whereIn('vehicle_id', $ownedVehicleIds)
            ->findOrFail($id);

        return $this->success(
            new ServiceScheduleResource($schedule),
            'Service schedule retrieved successfully.'
        );
    }

    /**
     * Update the specified service schedule.
     * Validates that schedule's vehicle belongs to current user/device.
     *
     * @param UpdateServiceScheduleRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateServiceScheduleRequest $request, string $id): JsonResponse
    {
        // Get owner's vehicles first
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownedVehicleIds = $vehicleQuery->pluck('id');

        $schedule = ServiceSchedule::whereIn('vehicle_id', $ownedVehicleIds)
            ->findOrFail($id);

        $schedule->update($request->validated());

        $schedule->load(['vehicle', 'serviceType', 'reminderOption']);

        return $this->success(
            new ServiceScheduleResource($schedule),
            'Service schedule updated successfully.'
        );
    }

    /**
     * Remove the specified service schedule.
     * Validates that schedule's vehicle belongs to current user/device.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        // Get owner's vehicles first
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownedVehicleIds = $vehicleQuery->pluck('id');

        $schedule = ServiceSchedule::whereIn('vehicle_id', $ownedVehicleIds)
            ->findOrFail($id);

        $schedule->delete();

        return $this->success(
            null,
            'Service schedule deleted successfully.'
        );
    }

    /**
     * Evaluate schedule status for a specific vehicle.
     * Validates that vehicle belongs to current user/device.
     *
     * @param Request $request
     * @param string $vehicleId
     * @return JsonResponse
     */
    public function evaluateStatus(Request $request, string $vehicleId): JsonResponse
    {
        // Verify vehicle belongs to owner
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $vehicle = $vehicleQuery->findOrFail($vehicleId);

        $statusEvaluation = $this->scheduleService->evaluateScheduleStatus($vehicleId);

        return $this->success(
            $statusEvaluation,
            'Schedule status evaluated successfully.'
        );
    }

    /**
     * Get service schedules for primary vehicle with status evaluation.
     * This endpoint automatically shows schedules for the user's primary vehicle.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function primaryVehicleSchedules(Request $request): JsonResponse
    {
        // Get owner's primary vehicle
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $primaryVehicle = $vehicleQuery->where('is_primary', true)->first();

        if (!$primaryVehicle) {
            return $this->errorResponse(
                'Belum ada motor utama. Silakan tambah motor terlebih dahulu.',
                404
            );
        }

        // Get all active schedules for primary vehicle with evaluation
        $schedules = ServiceSchedule::with(['serviceType', 'reminderOption'])
            ->active()
            ->forVehicle($primaryVehicle->id)
            ->get();

        $results = [];
        foreach ($schedules as $schedule) {
            $evaluation = $this->evaluateScheduleWithVehicle($schedule, $primaryVehicle);
            $results[] = $evaluation;
        }

        // Sort by status priority: critical > warning > normal
        $sortedResults = collect($results)->sortBy(function ($item) {
            return match ($item['status']) {
                'critical' => 1,
                'warning' => 2,
                'normal' => 3,
                default => 4,
            };
        })->values()->all();

        return $this->success([
            'vehicle' => [
                'id' => $primaryVehicle->id,
                'title' => $primaryVehicle->title,
                'make' => $primaryVehicle->make,
                'model' => $primaryVehicle->model,
                'current_odometer' => $primaryVehicle->odometer ?? 0,
                'license_plate' => $primaryVehicle->license_plate,
            ],
            'schedules' => $sortedResults,
            'summary' => [
                'total' => count($sortedResults),
                'critical' => collect($sortedResults)->where('status', 'critical')->count(),
                'warning' => collect($sortedResults)->where('status', 'warning')->count(),
                'normal' => collect($sortedResults)->where('status', 'normal')->count(),
            ],
        ], 'Primary vehicle schedules retrieved successfully.');
    }

    /**
     * Evaluate a single schedule with vehicle data.
     *
     * @param ServiceSchedule $schedule
     * @param Vehicle $vehicle
     * @return array
     */
    private function evaluateScheduleWithVehicle(ServiceSchedule $schedule, Vehicle $vehicle): array
    {
        $currentKm = $vehicle->odometer ?? 0;

        if ($schedule->schedule_type === 'km') {
            $remaining = $schedule->target_km - $currentKm;
            $reminderThreshold = $schedule->reminderOption ? $schedule->reminderOption->value : 500;

            $status = $this->determineStatus($remaining, $reminderThreshold);

            return [
                'id' => $schedule->id,
                'service_type_id' => $schedule->service_type_id,
                'service_name' => $schedule->serviceType->name,
                'schedule_type' => 'km',
                'target_km' => $schedule->target_km,
                'current_km' => $currentKm,
                'remaining_km' => $remaining,
                'status' => $status,
                'status_label' => $this->getStatusLabel($status),
                'message' => $this->generateKmMessage($remaining, $schedule->serviceType->name),
                'notes' => $schedule->notes,
            ];
        } else {
            $today = \Carbon\Carbon::today();
            $targetDate = \Carbon\Carbon::parse($schedule->target_date);
            $remaining = $today->diffInDays($targetDate, false);
            $reminderThreshold = $schedule->reminderOption 
                ? $this->convertReminderToDays($schedule->reminderOption) 
                : 7;

            $status = $this->determineStatus($remaining, $reminderThreshold);

            return [
                'id' => $schedule->id,
                'service_type_id' => $schedule->service_type_id,
                'service_name' => $schedule->serviceType->name,
                'schedule_type' => 'time',
                'target_date' => $targetDate->format('Y-m-d'),
                'current_date' => $today->format('Y-m-d'),
                'remaining_days' => (int) $remaining,
                'status' => $status,
                'status_label' => $this->getStatusLabel($status),
                'message' => $this->generateTimeMessage($remaining, $schedule->serviceType->name),
                'notes' => $schedule->notes,
            ];
        }
    }

    /**
     * Helper methods from ServiceScheduleService
     */
    private function determineStatus($remaining, $threshold): string
    {
        if ($remaining <= 0) {
            return 'critical';
        }
        if ($remaining <= $threshold) {
            return 'warning';
        }
        return 'normal';
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'critical' => 'Darurat',
            'warning' => 'Segera',
            'normal' => 'Aman',
            default => 'Aman',
        };
    }

    private function generateKmMessage(int $remaining, string $serviceName): string
    {
        if ($remaining <= 0) {
            return "{$serviceName} sudah melewati target! Segera lakukan service.";
        }
        if ($remaining <= 500) {
            return "{$serviceName} akan segera jatuh tempo dalam {$remaining} km.";
        }
        return "{$serviceName} masih {$remaining} km lagi.";
    }

    private function generateTimeMessage(int $remaining, string $serviceName): string
    {
        if ($remaining < 0) {
            $overdue = abs($remaining);
            return "{$serviceName} sudah terlambat {$overdue} hari! Segera lakukan service.";
        }
        if ($remaining == 0) {
            return "{$serviceName} jatuh tempo hari ini!";
        }
        if ($remaining <= 7) {
            return "{$serviceName} akan jatuh tempo dalam {$remaining} hari.";
        }
        return "{$serviceName} masih {$remaining} hari lagi.";
    }

    private function convertReminderToDays($reminderOption): int
    {
        $value = $reminderOption->value;
        $unit = $reminderOption->unit;

        return match ($unit) {
            'days' => $value,
            'weeks' => $value * 7,
            'months' => $value * 30,
            'years' => $value * 365,
            default => 0,
        };
    }
}

