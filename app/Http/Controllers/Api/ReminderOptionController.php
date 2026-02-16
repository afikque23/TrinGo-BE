<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReminderOptionRequest;
use App\Http\Requests\UpdateReminderOptionRequest;
use App\Http\Resources\ReminderOptionResource;
use App\Models\ReminderOption;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReminderOptionController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of reminder options.
     * 
     * Query params:
     * - active: 1 (only active) | 0 (only inactive) | null (all - admin only)
     * - unit: km | days | weeks | months | years | null (all)
     * - per_page: jumlah data per halaman (default: 15)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ReminderOption::query();

        // Filter berdasarkan status aktif
        // Default to active only for non-admin users
        if ($request->has('active')) {
            $isActive = filter_var($request->active, FILTER_VALIDATE_BOOLEAN);
            $query->byStatus($isActive);
        } else {
            // Default to active only
            $query->active();
        }

        // Filter berdasarkan unit
        if ($request->has('unit')) {
            $query->byUnit($request->unit);
        }

        // Ordering
        $query->orderBy('unit', 'asc')
              ->orderBy('value', 'asc');

        // Pagination
        $perPage = $request->input('per_page', 15);
        $reminderOptions = $query->paginate($perPage);

        return ReminderOptionResource::collection($reminderOptions);
    }

    /**
     * Store a newly created reminder option.
     */
    public function store(StoreReminderOptionRequest $request): JsonResponse
    {
        // Cek duplikasi value + unit
        $exists = ReminderOption::where('value', $request->value)
            ->where('unit', $request->unit)
            ->exists();

        if ($exists) {
            return $this->error(
                'Kombinasi nilai dan unit pengingat sudah ada.',
                422
            );
        }

        $reminderOption = ReminderOption::create($request->validated());

        return $this->created(
            new ReminderOptionResource($reminderOption),
            'Opsi pengingat berhasil ditambahkan.'
        );
    }

    /**
     * Display the specified reminder option.
     */
    public function show(ReminderOption $reminderOption): JsonResponse
    {
        return $this->success(
            new ReminderOptionResource($reminderOption),
            'Detail opsi pengingat berhasil diambil.'
        );
    }

    /**
     * Update the specified reminder option.
     */
    public function update(UpdateReminderOptionRequest $request, ReminderOption $reminderOption): JsonResponse
    {
        // Cek duplikasi value + unit (kecuali untuk record yang sedang diupdate)
        $exists = ReminderOption::where('value', $request->value)
            ->where('unit', $request->unit)
            ->where('id', '!=', $reminderOption->id)
            ->exists();

        if ($exists) {
            return $this->error(
                'Kombinasi nilai dan unit pengingat sudah ada.',
                422
            );
        }

        $reminderOption->update($request->validated());

        return $this->success(
            new ReminderOptionResource($reminderOption),
            'Opsi pengingat berhasil diperbarui.'
        );
    }

    /**
     * Remove the specified reminder option.
     */
    public function destroy(ReminderOption $reminderOption): JsonResponse
    {
        // TODO: Cek apakah reminder option sedang digunakan di reminder
        // if ($reminderOption->reminders()->exists()) {
        //     return $this->error(
        //         'Opsi pengingat tidak dapat dihapus karena masih digunakan.',
        //         422
        //     );
        // }

        $reminderOption->delete();

        return $this->success(
            null,
            'Opsi pengingat berhasil dihapus.'
        );
    }

    /**
     * Toggle reminder option status (active/inactive).
     */
    public function toggleStatus(ReminderOption $reminderOption): JsonResponse
    {
        $reminderOption->update([
            'is_active' => !$reminderOption->is_active
        ]);

        $status = $reminderOption->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return $this->success(
            new ReminderOptionResource($reminderOption),
            "Opsi pengingat berhasil {$status}."
        );
    }
}
