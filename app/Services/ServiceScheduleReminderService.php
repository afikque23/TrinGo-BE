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

                // Kirim notification
                $sent = $this->sendReminderNotification(
                    $schedule,
                    "Kendaraan Anda akan mencapai jadwal servis dalam {$kmUntilService} km lagi",
                    [
                        'type' => 'schedule_reminder',
                        'schedule_id' => $schedule->id,
                        'current_odometer' => $currentOdometer,
                        'target_km' => $schedule->target_km,
                        'remaining_km' => $kmUntilService,
                        'service_type' => $schedule->service_name ?? $schedule->serviceType->name,
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
                    // Kirim notification
                    $sent = $this->sendReminderNotification(
                        $schedule,
                        "Jadwal servis Anda dalam {$daysUntilService} hari lagi",
                        [
                            'type' => 'schedule_reminder',
                            'schedule_id' => $schedule->id,
                            'target_date' => $targetDate->format('Y-m-d'),
                            'remaining_days' => $daysUntilService,
                            'service_type' => $schedule->service_name ?? $schedule->serviceType->name,
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
     * Send reminder notification via FCM dan simpan ke database
     *
     * @param ServiceSchedule $schedule
     * @param string $message
     * @param array $data
     * @return bool
     */
    protected function sendReminderNotification(ServiceSchedule $schedule, string $message, array $data = []): bool
    {
        $vehicle = $schedule->vehicle;
        $serviceName = $schedule->service_name ?? $schedule->serviceType->name;

        // Get user_id dari vehicle
        $userId = $vehicle->user_id;
        $deviceId = $vehicle->device_id;

        // Simpan notification ke database
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
                dataPayload: $data
            );

            Log::info("Reminder notification created for schedule #{$schedule->id}", [
                'notification_id' => $notification->id,
                'push_sent' => $notification->push_sent ?? false,
            ]);

            // Check if push was successful
            return $notification->push_sent ?? false;

        } catch (\Exception $e) {
            Log::error("Failed to send reminder notification for schedule #{$schedule->id}: " . $e->getMessage());
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
