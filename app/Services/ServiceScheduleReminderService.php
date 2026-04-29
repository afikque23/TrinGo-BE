<?php

namespace App\Services;

use App\Models\ServiceSchedule;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk handle reminder checks untuk service schedules
 */
class ServiceScheduleReminderService
{
    protected FcmNotificationService $fcmService;
    protected NotificationService $notificationService;

    public function __construct(
        FcmNotificationService $fcmService,
        NotificationService $notificationService
    ) {
        $this->fcmService = $fcmService;
        $this->notificationService = $notificationService;
    }

    /**
     * Check reminders untuk specific vehicle berdasarkan odometer update
     *
     * @param int $vehicleId
     * @param int $currentOdometer
     * @return array
     */
    public function checkKmBasedReminders(int $vehicleId, int $currentOdometer): array
    {
        $triggeredReminders = [];

        // Get active schedules dengan reminder threshold untuk vehicle ini
        /** @var \Illuminate\Database\Eloquent\Collection<int, ServiceSchedule> $schedules */
        $schedules = ServiceSchedule::with(['vehicle', 'serviceType'])
            ->where('vehicle_id', $vehicleId)
            ->where('is_active', true)
            ->where('schedule_type', 'km')
            ->whereNotNull('reminder_threshold')
            ->whereNotNull('target_km')
            ->where('reminder_sent', false) // Hanya yang belum dikirim
            ->get();

        foreach ($schedules as $schedule) {
            /** @var ServiceSchedule $schedule */
            // Calculate reminder trigger point
            $reminderTriggerOdometer = $schedule->target_km - $schedule->reminder_threshold;

            // Check apakah current odometer sudah mencapai atau melewati threshold
            if ($currentOdometer >= $reminderTriggerOdometer) {
                $kmUntilService = $schedule->target_km - $currentOdometer;

                // Kirim notification menggunakan template
                $sent = $this->sendReminderNotificationFromTemplate(
                    schedule: $schedule,
                    triggerType: 'schedule_reminder_km',
                    variables: [
                        'service_name' => $schedule->service_name ?? $schedule->serviceType->name,
                        'service_type' => $schedule->service_name ?? $schedule->serviceType->name,
                        'km_remaining' => $kmUntilService,
                        'target_km' => $schedule->target_km,
                        'current_km' => $currentOdometer,
                        'vehicle_name' => $schedule->vehicle->name ?? $schedule->vehicle->brand . ' ' . $schedule->vehicle->model,
                    ]
                );

                if ($sent) {
                    // Update flag reminder_sent
                    $schedule->update([
                        'reminder_sent' => true,
                        'reminder_sent_at' => now(),
                    ]);

                    $triggeredReminders[] = [
                        'schedule_id' => $schedule->id,
                        'service_type' => $schedule->service_name ?? $schedule->serviceType->name,
                        'current_odometer' => $currentOdometer,
                        'target_km' => $schedule->target_km,
                        'remaining_km' => $kmUntilService,
                        'message' => "Servis {$schedule->service_name} dalam {$kmUntilService} km lagi",
                    ];
                }
            }
        }

        return $triggeredReminders;
    }

    /**
     * Check reminders berbasis tanggal (dipanggil via scheduled job)
     *
     * @return array
     */
    public function checkDateBasedReminders(): array
    {
        $triggeredReminders = [];
        $today = Carbon::today();

        // Get active schedules dengan reminder threshold berbasis waktu
        /** @var \Illuminate\Database\Eloquent\Collection<int, ServiceSchedule> $schedules */
        $schedules = ServiceSchedule::with(['vehicle', 'serviceType'])
            ->where('is_active', true)
            ->where('schedule_type', 'time')
            ->whereNotNull('reminder_threshold')
            ->whereNotNull('target_date')
            ->where('reminder_sent', false) // Hanya yang belum dikirim
            ->get();

        foreach ($schedules as $schedule) {
            /** @var ServiceSchedule $schedule */
            // Calculate reminder date
            $targetDate = Carbon::parse($schedule->target_date);
            $reminderDate = $targetDate->copy()->subDays($schedule->reminder_threshold);

            // Check apakah hari ini sudah mencapai atau melewati reminder date
            if ($today->greaterThanOrEqualTo($reminderDate)) {
                $daysUntilService = $today->diffInDays($targetDate, false); // false = bisa negatif jika sudah lewat

                if ($daysUntilService >= 0) { // Hanya kirim jika belum melewati target date
                    // Kirim notification menggunakan template
                    $sent = $this->sendReminderNotificationFromTemplate(
                        schedule: $schedule,
                        triggerType: 'schedule_reminder_time',
                        variables: [
                            'service_name' => $schedule->service_name ?? $schedule->serviceType->name,
                            'service_type' => $schedule->service_name ?? $schedule->serviceType->name,
                            'days_remaining' => $daysUntilService,
                            'target_date' => $targetDate->format('d/m/Y'),
                            'vehicle_name' => $schedule->vehicle->name ?? $schedule->vehicle->brand . ' ' . $schedule->vehicle->model,
                        ]
                    );

                    if ($sent) {
                        // Update flag reminder_sent
                        $schedule->update([
                            'reminder_sent' => true,
                            'reminder_sent_at' => now(),
                        ]);

                        $triggeredReminders[] = [
                            'schedule_id' => $schedule->id,
                            'service_type' => $schedule->service_name ?? $schedule->serviceType->name,
                            'target_date' => $targetDate->format('Y-m-d'),
                            'remaining_days' => $daysUntilService,
                            'message' => "Servis {$schedule->service_name} dalam {$daysUntilService} hari lagi",
                        ];
                    }
                }
            }
        }

        return $triggeredReminders;
    }

    /**
     * Send reminder notification menggunakan template dari database
     *
     * @param ServiceSchedule $schedule
     * @param string $triggerType
     * @param array $variables
     * @return bool
     */
    protected function sendReminderNotificationFromTemplate(
        ServiceSchedule $schedule,
        string $triggerType,
        array $variables = []
    ): bool {
        $vehicle = $schedule->vehicle;

        // Get user_id dari vehicle
        $userId = $vehicle->user_id;
        $deviceId = $vehicle->device_id;

        // Cari template notifikasi yang sesuai
        $template = \App\Models\NotificationTemplate::where('category_key', 'service')
            ->where('trigger_type', $triggerType)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::warning("No active notification template found for trigger: {$triggerType}");
            
            // Fallback ke hardcoded message jika template tidak ada
            return $this->sendFallbackNotification($schedule, $triggerType, $variables);
        }

        try {
            // Kirim notification dari template
            $notification = $this->notificationService->sendFromTemplate(
                template: $template,
                variables: $variables,
                user: $userId ? \App\Models\User::find($userId) : null,
                deviceId: $deviceId,
                vehicle: $vehicle
            );

            if ($notification) {
                Log::info("Reminder notification sent from template for schedule #{$schedule->id}", [
                    'notification_id' => $notification->id,
                    'template_id' => $template->id,
                    'trigger_type' => $triggerType,
                ]);

                return $notification->push_sent ?? false;
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Failed to send reminder notification for schedule #{$schedule->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fallback notification jika template tidak ditemukan
     *
     * @param ServiceSchedule $schedule
     * @param string $triggerType
     * @param array $variables
     * @return bool
     */
    protected function sendFallbackNotification(
        ServiceSchedule $schedule,
        string $triggerType,
        array $variables
    ): bool {
        $vehicle = $schedule->vehicle;
        $userId = $vehicle->user_id;
        $deviceId = $vehicle->device_id;

        // Generate default message
        $message = match($triggerType) {
            'schedule_reminder_km' => "🔧 Pengingat Servis: {$variables['service_name']} dalam {$variables['km_remaining']} km lagi!",
            'schedule_reminder_time' => "🔧 Pengingat Servis: {$variables['service_name']} dalam {$variables['days_remaining']} hari lagi!",
            default => "🔧 Pengingat Servis: {$variables['service_name']}",
        };

        try {
            $notification = $this->notificationService->sendDirect(
                title: '🔔 Pengingat Servis',
                message: $message,
                categoryKey: 'service',
                priority: 'high',
                channel: 'push',
                user: $userId ? \App\Models\User::find($userId) : null,
                deviceId: $deviceId,
                vehicle: $vehicle,
                dataPayload: array_merge([
                    'type' => 'schedule_reminder',
                    'schedule_id' => $schedule->id,
                    'trigger_type' => $triggerType,
                ], $variables)
            );

            Log::warning("Fallback notification sent (no template) for schedule #{$schedule->id}");
            return $notification->push_sent ?? false;

        } catch (\Exception $e) {
            Log::error("Failed to send fallback notification for schedule #{$schedule->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset reminder_sent flag saat schedule diupdate dengan target baru
     *
     * @param int $scheduleId
     * @return bool
     */
    public function resetReminderFlag(int $scheduleId): bool
    {
        try {
            $schedule = ServiceSchedule::find($scheduleId);
            if ($schedule) {
                $schedule->update([
                    'reminder_sent' => false,
                    'reminder_sent_at' => null,
                ]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to reset reminder flag for schedule #{$scheduleId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check all pending reminders (untuk scheduled job)
     * Menggabungkan check km-based dan date-based
     *
     * @return array
     */
    public function checkAllPendingReminders(): array
    {
        $results = [
            'km_based' => [],
            'date_based' => [],
        ];

        // Check date-based reminders
        $results['date_based'] = $this->checkDateBasedReminders();

        // Check km-based reminders untuk semua vehicle
        $vehicles = Vehicle::whereNotNull('current_km')->get();
        foreach ($vehicles as $vehicle) {
            $reminders = $this->checkKmBasedReminders($vehicle->id, $vehicle->current_km);
            if (!empty($reminders)) {
                $results['km_based'] = array_merge($results['km_based'], $reminders);
            }
        }

        return $results;
    }
}
