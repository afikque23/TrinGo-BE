<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceScheduleRequest;
use App\Http\Requests\UpdateServiceScheduleRequest;
use App\Http\Resources\ServiceScheduleResource;
use App\Models\ServiceSchedule;
use App\Services\ServiceScheduleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceScheduleController extends Controller
{
    use ApiResponse;

    protected ServiceScheduleService $scheduleService;

    public function __construct(ServiceScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display a listing of service schedules.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ServiceSchedule::with(['vehicle', 'serviceType', 'reminderOption'])
            ->whereHas('vehicle', function ($query) {
                $query->where('user_id', auth()->id());
            });

        // Filter by vehicle_id if provided
        if ($request->has('vehicle_id')) {
            $query->forVehicle($request->vehicle_id);
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
     *
     * @param StoreServiceScheduleRequest $request
     * @return JsonResponse
     */
    public function store(StoreServiceScheduleRequest $request): JsonResponse
    {
        $schedule = ServiceSchedule::create($request->validated());

        $schedule->load(['vehicle', 'serviceType', 'reminderOption']);

        return $this->created(
            new ServiceScheduleResource($schedule),
            'Service schedule created successfully.'
        );
    }

    /**
     * Display the specified service schedule.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $schedule = ServiceSchedule::with(['vehicle', 'serviceType', 'reminderOption'])
            ->whereHas('vehicle', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        return $this->success(
            new ServiceScheduleResource($schedule),
            'Service schedule retrieved successfully.'
        );
    }

    /**
     * Update the specified service schedule.
     *
     * @param UpdateServiceScheduleRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateServiceScheduleRequest $request, string $id): JsonResponse
    {
        $schedule = ServiceSchedule::whereHas('vehicle', function ($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $schedule->update($request->validated());

        $schedule->load(['vehicle', 'serviceType', 'reminderOption']);

        return $this->success(
            new ServiceScheduleResource($schedule),
            'Service schedule updated successfully.'
        );
    }

    /**
     * Remove the specified service schedule.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $schedule = ServiceSchedule::whereHas('vehicle', function ($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $schedule->delete();

        return $this->success(
            null,
            'Service schedule deleted successfully.'
        );
    }

    /**
     * Evaluate schedule status for a specific vehicle.
     *
     * @param string $vehicleId
     * @return JsonResponse
     */
    public function evaluateStatus(string $vehicleId): JsonResponse
    {
        // Verify vehicle belongs to authenticated user
        $vehicle = auth()->user()->vehicles()->findOrFail($vehicleId);

        $statusEvaluation = $this->scheduleService->evaluateScheduleStatus($vehicleId);

        return $this->success(
            $statusEvaluation,
            'Schedule status evaluated successfully.'
        );
    }
}
