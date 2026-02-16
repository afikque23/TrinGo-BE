<?php

namespace App\Services;

use App\Models\ServiceSchedule;
use App\Models\Vehicle;
use Carbon\Carbon;

class ServiceScheduleService
{
    /**
     * Evaluate all active schedules for a specific vehicle.
     *
     * @param int $vehicleId
     * @return array
     */
    public function evaluateScheduleStatus(int $vehicleId): array
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        
        $schedules = ServiceSchedule::with(['serviceType', 'reminderOption'])
            ->active()
            ->forVehicle($vehicleId)
            ->get();

        $results = [];

        foreach ($schedules as $schedule) {
            $evaluation = $this->evaluateSingleSchedule($schedule, $vehicle);
            $results[] = $evaluation;
        }

        return $results;
    }

    /**
     * Evaluate a single schedule and determine its status.
     *
     * @param ServiceSchedule $schedule
     * @param Vehicle $vehicle
     * @return array
     */
    private function evaluateSingleSchedule(ServiceSchedule $schedule, Vehicle $vehicle): array
    {
        if ($schedule->schedule_type === 'km') {
            return $this->evaluateKmBasedSchedule($schedule, $vehicle);
        }

        return $this->evaluateTimeBasedSchedule($schedule);
    }

    /**
     * Evaluate kilometer-based schedule.
     *
     * @param ServiceSchedule $schedule
     * @param Vehicle $vehicle
     * @return array
     */
    private function evaluateKmBasedSchedule(ServiceSchedule $schedule, Vehicle $vehicle): array
    {
        $currentKm = $vehicle->odometer ?? $vehicle->current_km ?? 0;
        $remaining = $schedule->target_km - $currentKm;
        
        // Get reminder threshold if reminder option exists
        $reminderThreshold = $schedule->reminderOption ? $schedule->reminderOption->value : 500; // default 500km

        $status = $this->determineStatus($remaining, $reminderThreshold);

        return [
            'schedule_id' => $schedule->id,
            'service_name' => $schedule->serviceType->name,
            'schedule_type' => 'km',
            'target_km' => $schedule->target_km,
            'current_km' => $currentKm,
            'remaining_km' => $remaining,
            'unit' => 'km',
            'reminder_threshold' => $reminderThreshold,
            'status' => $status,
            'status_label' => $this->getStatusLabel($status),
            'message' => $this->generateKmMessage($remaining, $schedule->serviceType->name),
            'notes' => $schedule->notes,
        ];
    }

    /**
     * Evaluate time-based schedule.
     *
     * @param ServiceSchedule $schedule
     * @return array
     */
    private function evaluateTimeBasedSchedule(ServiceSchedule $schedule): array
    {
        $today = Carbon::today();
        $targetDate = Carbon::parse($schedule->target_date);
        $remaining = $today->diffInDays($targetDate, false); // negative if overdue

        // Convert reminder option to days based on its unit, or use default 7 days
        $reminderThreshold = $schedule->reminderOption 
            ? $this->convertReminderToDays($schedule->reminderOption) 
            : 7;

        $status = $this->determineStatus($remaining, $reminderThreshold);

        return [
            'schedule_id' => $schedule->id,
            'service_name' => $schedule->serviceType->name,
            'schedule_type' => 'time',
            'target_date' => $targetDate->format('Y-m-d'),
            'current_date' => $today->format('Y-m-d'),
            'remaining_days' => (int) $remaining,
            'unit' => 'days',
            'reminder_threshold' => $reminderThreshold,
            'status' => $status,
            'status_label' => $this->getStatusLabel($status),
            'message' => $this->generateTimeMessage($remaining, $schedule->serviceType->name),
            'notes' => $schedule->notes,
        ];
    }

    /**
     * Convert reminder option value to days.
     *
     * @param \App\Models\ReminderOption $reminderOption
     * @return int
     */
    private function convertReminderToDays($reminderOption): int
    {
        $value = $reminderOption->value;
        $unit = $reminderOption->unit;

        return match ($unit) {
            'days' => $value,
            'weeks' => $value * 7,
            'months' => $value * 30, // Approximate
            'years' => $value * 365, // Approximate
            default => 0,
        };
    }

    /**
     * Determine status based on remaining value and threshold.
     *
     * @param int|float $remaining
     * @param int|float $threshold
     * @return string
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

    /**
     * Generate message for km-based schedule.
     *
     * @param int $remaining
     * @param string $serviceName
     * @return string
     */
    private function generateKmMessage(int $remaining, string $serviceName): string
    {
        if ($remaining <= 0) {
            return "{$serviceName} sudah melewati target! Segera lakukan service.";
        }

        if ($remaining <= 100) {
            return "{$serviceName} akan segera jatuh tempo dalam {$remaining} km.";
        }

        return "{$serviceName} masih {$remaining} km lagi.";
    }

    /**
     * Generate message for time-based schedule.
     *
     * @param int $remaining
     * @param string $serviceName
     * @return string
     */
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

    /**
     * Get status label in Indonesian.
     *
     * @param string $status
     * @return string
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'critical' => 'Darurat',
            'warning' => 'Segera',
            'normal' => 'Aman',
            default => 'Aman',
        };
    }
}
