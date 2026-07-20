<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceScheduleRequest;
use App\Http\Requests\UpdateServiceScheduleRequest;
use App\Http\Resources\ServiceScheduleResource;
use App\Models\ServiceHistory;
use App\Models\ServiceSchedule;
use App\Models\Vehicle;
use App\Services\ServiceScheduleService;
use App\Services\ServiceScheduleReminderService;
use App\Traits\ApiResponse;
use App\Traits\HasOwnerIdentification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ServiceScheduleController extends Controller
{
    use ApiResponse, HasOwnerIdentification;

    protected ServiceScheduleService $scheduleService;
    protected ServiceScheduleReminderService $reminderService;

    public function __construct(
        ServiceScheduleService $scheduleService,
        ServiceScheduleReminderService $reminderService
    ) {
        $this->scheduleService = $scheduleService;
        $this->reminderService = $reminderService;
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

        // Note: interval_value is auto-calculated via Model accessor if value is 0
        // No need to manually save here, accessor handles it automatically

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

        $validated = $request->validated();

        // Calculate interval_value if not provided or is 0
        if (!isset($validated['interval_value']) || $validated['interval_value'] == 0) {
            if ($validated['schedule_type'] == 'km') {
                // For mileage-based: interval = target_km - last_service_mileage
                $validated['interval_value'] = $validated['target_km'] - ($validated['last_service_mileage'] ?? 0);
            } elseif ($validated['schedule_type'] == 'time') {
                // For time-based: calculate days between dates if both are provided
                if (isset($validated['target_date']) && isset($validated['last_service_date'])) {
                    $targetDate = \Carbon\Carbon::parse($validated['target_date']);
                    $lastDate = \Carbon\Carbon::parse($validated['last_service_date']);
                    $validated['interval_value'] = $targetDate->diffInDays($lastDate);
                }
            }
        }

        $schedule = ServiceSchedule::create($validated);

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

        $validated = $request->validated();

        // Recalculate interval_value if target or last service values changed
        if (isset($validated['target_km']) || isset($validated['last_service_mileage'])) {
            if ($schedule->schedule_type == 'km' || (isset($validated['schedule_type']) && $validated['schedule_type'] == 'km')) {
                $targetKm = $validated['target_km'] ?? $schedule->target_km;
                $lastMileage = $validated['last_service_mileage'] ?? $schedule->last_service_mileage ?? 0;
                $validated['interval_value'] = $targetKm - $lastMileage;
            }
        }

        if (isset($validated['target_date']) || isset($validated['last_service_date'])) {
            if ($schedule->schedule_type == 'time' || (isset($validated['schedule_type']) && $validated['schedule_type'] == 'time')) {
                $targetDate = isset($validated['target_date']) ? \Carbon\Carbon::parse($validated['target_date']) : \Carbon\Carbon::parse($schedule->target_date);
                $lastDate = isset($validated['last_service_date']) ? \Carbon\Carbon::parse($validated['last_service_date']) : ($schedule->last_service_date ? \Carbon\Carbon::parse($schedule->last_service_date) : null);

                if ($lastDate) {
                    $validated['interval_value'] = $targetDate->diffInDays($lastDate);
                }
            }
        }

        $schedule->update($validated);

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

    /**
     * Check reminders for a specific vehicle based on current odometer.
     * This endpoint should be called when odometer is updated or manually by the app.
     *
     * @param Request $request
     * @param string $vehicleId
     * @return JsonResponse
     */
    public function checkReminders(Request $request, string $vehicleId): JsonResponse
    {
        // Validate request
        $request->validate([
            'current_odometer' => 'required|integer|min:0',
        ]);

        // Verify vehicle belongs to owner
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $vehicle = $vehicleQuery->findOrFail($vehicleId);

        $currentOdometer = $request->input('current_odometer');

        // Check KM-based reminders
        $triggeredReminders = $this->reminderService->checkKmBasedReminders(
            $vehicleId,
            $currentOdometer
        );

        return $this->success([
            'vehicle_id' => $vehicleId,
            'current_odometer' => $currentOdometer,
            'reminders_triggered' => count($triggeredReminders),
            'reminders' => $triggeredReminders,
        ], count($triggeredReminders) > 0
            ? 'Reminder notifications have been sent.'
            : 'No reminders to trigger at this time.');
    }

    /**
     * Reset reminder flag for a schedule (e.g., when user updates target)
     * Internal use or can be called when schedule is marked as completed
     *
     * @param Request $request
     * @param string $scheduleId
     * @return JsonResponse
     */
    public function resetReminder(Request $request, string $scheduleId): JsonResponse
    {
        // Get owner's vehicles first
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownedVehicleIds = $vehicleQuery->pluck('id');

        $schedule = ServiceSchedule::whereIn('vehicle_id', $ownedVehicleIds)
            ->findOrFail($scheduleId);

        $reset = $this->reminderService->resetReminderFlag($schedule->id);

        if ($reset) {
            return $this->success(
                ['schedule_id' => $schedule->id],
                'Reminder flag reset successfully.'
            );
        }

        return $this->errorResponse('Failed to reset reminder flag.', 500);
    }

    /**
     * Mark schedule as completed, create service history entry, and roll baseline forward.
     *
     * @param Request $request
     * @param string $scheduleId
     * @return JsonResponse
     */
    public function complete(Request $request, string $scheduleId): JsonResponse
    {
        $validated = $request->validate([
            'performed_at' => ['required', 'date', 'before_or_equal:today'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'service_provider' => ['nullable', 'string', 'max:150'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Get owner's vehicles first
        $vehicleQuery = Vehicle::query();
        $this->applyOwnerFilter($vehicleQuery, $request);
        $ownedVehicleIds = $vehicleQuery->pluck('id');

        $schedule = ServiceSchedule::with(['serviceType', 'reminderOption'])
            ->whereIn('vehicle_id', $ownedVehicleIds)
            ->findOrFail($scheduleId);

        $vehicle = Vehicle::whereIn('id', $ownedVehicleIds)->findOrFail($schedule->vehicle_id);

        $performedAt = Carbon::parse($validated['performed_at'])->startOfDay();
        $serviceOdometer = array_key_exists('odometer', $validated)
            ? (int) $validated['odometer']
            : (int) ($vehicle->odometer ?? 0);

        $intervalValue = (int) ($schedule->interval_value ?? 0);
        if ($intervalValue <= 0) {
            if (
                $schedule->schedule_type === 'km'
                && $schedule->target_km !== null
                && $schedule->last_service_mileage !== null
            ) {
                $intervalValue = (int) $schedule->target_km - (int) $schedule->last_service_mileage;
            } elseif (
                $schedule->schedule_type === 'time'
                && $schedule->target_date !== null
                && $schedule->last_service_date !== null
            ) {
                $intervalValue = Carbon::parse($schedule->last_service_date)
                    ->diffInDays(Carbon::parse($schedule->target_date));
            }
        }

        if ($intervalValue <= 0) {
            $intervalValue = $schedule->schedule_type === 'km' ? 1000 : 30;
        }

        DB::transaction(function () use (
            $validated,
            $schedule,
            $vehicle,
            $performedAt,
            $serviceOdometer,
            $intervalValue
        ) {
            $serviceName = $schedule->service_name
                ?? $schedule->serviceType?->name
                ?? 'Service';

            $costCents = null;
            if (array_key_exists('cost', $validated) && $validated['cost'] !== null) {
                $costCents = (int) round(((float) $validated['cost']) * 100);
            }

            ServiceHistory::create([
                'vehicle_id' => $schedule->vehicle_id,
                'service_type_id' => $schedule->service_type_id,
                'service_type' => $serviceName,
                'performed_at' => $performedAt->toDateString(),
                'odometer' => $serviceOdometer,
                'cost_cents' => $costCents,
                'currency' => strtoupper((string) ($validated['currency'] ?? 'IDR')),
                'service_provider' => $validated['service_provider'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $updates = [
                'last_service_date' => $performedAt->toDateString(),
                'interval_value' => $intervalValue,
                'reminder_sent' => false,
                'reminder_sent_at' => null,
                'is_active' => true,
            ];

            if ($schedule->schedule_type === 'km') {
                $updates['last_service_mileage'] = $serviceOdometer;
                $updates['target_km'] = $serviceOdometer + $intervalValue;
            } else {
                $updates['target_date'] = $performedAt->copy()->addDays($intervalValue)->toDateString();
            }

            $schedule->update($updates);

            if ($serviceOdometer > (int) ($vehicle->odometer ?? 0)) {
                $vehicle->update([
                    'odometer' => $serviceOdometer,
                ]);
            }
        });

        $schedule->refresh();
        $schedule->load(['vehicle', 'serviceType', 'reminderOption']);

        return $this->success([
            'schedule' => new ServiceScheduleResource($schedule),
            'completed_at' => $performedAt->toDateString(),
            'next_target' => [
                'schedule_type' => $schedule->schedule_type,
                'target_km' => $schedule->target_km,
                'target_date' => $schedule->target_date?->format('Y-m-d'),
            ],
        ], 'Servis berhasil dicatat dan jadwal berikutnya telah diperbarui.');
    }
}

