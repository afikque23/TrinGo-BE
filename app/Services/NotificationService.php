<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected FcmNotificationService $fcmService;

    public function __construct(FcmNotificationService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Daftar semua variabel yang tersedia beserta deskripsinya.
     * Digunakan di admin panel untuk referensi.
     */
    public const AVAILABLE_VARIABLES = [
        // Kendaraan
        'vehicle_name'   => 'Nama kendaraan (contoh: Honda Beat 2023)',
        'vehicle_plate'  => 'Plat nomor kendaraan',
        'vehicle_type'   => 'Tipe motor (matic/manual/sport)',
        'current_km'     => 'Kilometer odometer saat ini',
        'vehicle_color'  => 'Warna kendaraan',
        'vehicle_year'   => 'Tahun kendaraan',

        // Servis
        'service_type'   => 'Jenis servis (contoh: Ganti Oli)',
        'service_name'   => 'Nama jadwal servis',
        'km_remaining'   => 'KM tersisa sebelum waktunya servis',
        'km_overdue'     => 'KM terlambat dari jadwal servis',
        'target_km'      => 'Target KM untuk servis berikutnya',
        'target_date'    => 'Tanggal target servis berikutnya',
        'days_remaining' => 'Hari tersisa sebelum jadwal servis',
        'last_service'   => 'Tanggal servis terakhir',
        'workshop_name'  => 'Nama bengkel terakhir',

        // Perjalanan
        'distance'       => 'Jarak perjalanan (km)',
        'duration'       => 'Durasi perjalanan',
        'avg_speed'      => 'Kecepatan rata-rata (km/h)',

        // Pengguna
        'user_name'      => 'Nama pengguna',

        // Umum
        'app_name'       => 'Nama aplikasi',
        'date_now'       => 'Tanggal sekarang',
    ];

    /**
     * Kirim notifikasi berdasarkan template dengan variabel yang diberikan.
     *
     * @param NotificationTemplate $template Template notifikasi
     * @param array $variables Data variabel untuk mengisi template
     * @param User|null $user User penerima (null jika guest)
     * @param string|null $deviceId Device ID untuk guest mode
     * @param Vehicle|null $vehicle Kendaraan terkait
     * @return Notification|null
     */
    public function sendFromTemplate(
        NotificationTemplate $template,
        array $variables = [],
        ?User $user = null,
        ?string $deviceId = null,
        ?Vehicle $vehicle = null
    ): ?Notification {
        // Cek apakah template aktif
        if (!$template->is_active) {
            Log::info("NotificationService: Template '{$template->name}' tidak aktif, skip.");
            return null;
        }

        // Cek preferensi user (jika ada user)
        if ($user && !NotificationPreference::isEnabledFor($user, $template)) {
            Log::info("NotificationService: Notifikasi dinonaktifkan oleh user {$user->id} untuk template '{$template->name}'.");
            return null;
        }

        // Isi variabel default
        $variables = $this->fillDefaultVariables($variables, $user, $vehicle);

        // Parse template pesan
        $message = $this->parseTemplate($template->message_template, $variables);
        $title = $this->generateTitle($template, $variables);

        // Simpan notifikasi ke database
        $notification = Notification::create([
            'user_id' => $user?->id,
            'device_id' => $deviceId,
            'vehicle_id' => $vehicle?->id,
            'category_key' => $template->category_key,
            'template_id' => $template->id,
            'title' => $title,
            'message' => $message,
            'data_payload' => [
                'template_id' => $template->id,
                'category_key' => $template->category_key,
                'trigger_type' => $template->trigger_type,
                'variables' => $variables,
            ],
            'priority' => $template->priority,
            'sent_via' => $template->channel,
        ]);

        // Kirim push notification jika channel = push
        if ($template->channel === 'push') {
            $pushResult = $this->sendPushNotification($notification, $user, $deviceId);
            
            // Update notification dengan hasil push
            if ($pushResult) {
                $notification->update([
                    'push_sent' => true,
                    'push_success' => $pushResult['success'] ?? false,
                ]);
                $notification->refresh(); // Reload from database
                
                Log::debug('NotificationService: Updated notification with push result', [
                    'notification_id' => $notification->id,
                    'push_sent' => $notification->push_sent,
                    'push_success' => $notification->push_success,
                ]);
            }
        }

        Log::info("NotificationService: Notifikasi '{$title}' berhasil dikirim.", [
            'notification_id' => $notification->id,
            'user_id' => $user?->id,
            'device_id' => $deviceId,
            'channel' => $template->channel,
        ]);

        return $notification;
    }

    /**
     * Kirim notifikasi langsung tanpa template (untuk kasus custom).
     */
    public function sendDirect(
        string $title,
        string $message,
        string $categoryKey,
        string $priority = 'normal',
        string $channel = 'in_app',
        ?User $user = null,
        ?string $deviceId = null,
        ?Vehicle $vehicle = null,
        array $dataPayload = []
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user?->id,
            'device_id' => $deviceId,
            'vehicle_id' => $vehicle?->id,
            'category_key' => $categoryKey,
            'title' => $title,
            'message' => $message,
            'data_payload' => $dataPayload,
            'priority' => $priority,
            'sent_via' => $channel,
        ]);

        if ($channel === 'push') {
            $pushResult = $this->sendPushNotification($notification, $user, $deviceId);
            
            // Update notification dengan hasil push
            if ($pushResult) {
                $notification->update([
                    'push_sent' => true,
                    'push_success' => $pushResult['success'] ?? false,
                ]);
                $notification->refresh(); // Reload from database
            }
        }

        return $notification;
    }

    /**
     * Test push notification dari admin panel.
     * Mengirim notifikasi test ke admin yang sedang login.
     */
    public function testPush(NotificationTemplate $template, User $admin): array
    {
        // Buat variabel contoh untuk preview
        $sampleVariables = $this->getSampleVariables();

        $message = $this->parseTemplate($template->message_template, $sampleVariables);
        $title = "[TEST] " . $this->generateTitle($template, $sampleVariables);

        // Simpan notifikasi test
        $notification = Notification::create([
            'user_id' => $admin->id,
            'category_key' => $template->category_key,
            'template_id' => $template->id,
            'title' => $title,
            'message' => $message,
            'data_payload' => [
                'is_test' => true,
                'template_id' => $template->id,
                'variables' => $sampleVariables,
            ],
            'priority' => $template->priority,
            'sent_via' => $template->channel,
        ]);

        // Kirim push notification ke device admin
        $pushResult = null;
        if ($template->channel === 'push') {
            $pushResult = $this->sendPushNotification($notification, $admin);
        }

        return [
            'notification' => $notification,
            'message_preview' => $message,
            'push_sent' => $pushResult !== null,
            'push_result' => $pushResult,
        ];
    }

    /**
     * Kirim notifikasi pengingat servis.
     */
    public function sendServiceReminder(
        Vehicle $vehicle,
        string $serviceType,
        int $kmRemaining,
        ?int $targetKm = null,
        ?string $targetDate = null
    ): ?Notification {
        $template = NotificationTemplate::where('trigger_type', 'km_before_interval')
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::warning("NotificationService: Tidak ada template aktif untuk trigger 'km_before_interval'.");
            return null;
        }

        $user = $vehicle->user_id ? User::find($vehicle->user_id) : null;

        return $this->sendFromTemplate($template, [
            'service_type' => $serviceType,
            'km_remaining' => number_format($kmRemaining),
            'target_km' => $targetKm ? number_format($targetKm) : '-',
            'target_date' => $targetDate ?? '-',
        ], $user, $vehicle->device_id, $vehicle);
    }

    /**
     * Kirim notifikasi servis terlambat.
     */
    public function sendServiceOverdue(
        Vehicle $vehicle,
        string $serviceType,
        int $kmOverdue
    ): ?Notification {
        $template = NotificationTemplate::where('trigger_type', 'overdue')
            ->where('is_active', true)
            ->first();

        if (!$template) return null;

        $user = $vehicle->user_id ? User::find($vehicle->user_id) : null;

        return $this->sendFromTemplate($template, [
            'service_type' => $serviceType,
            'km_overdue' => number_format($kmOverdue),
        ], $user, $vehicle->device_id, $vehicle);
    }

    /**
     * Kirim push notification menggunakan FcmNotificationService.
     */
    public function sendPushNotification(
        Notification $notification,
        ?User $user = null,
        ?string $deviceId = null
    ): ?array {
        if (!$this->fcmService->isConfigured()) {
            Log::warning('NotificationService: FCM tidak dikonfigurasi. Push notification tidak dikirim.');
            return ['success' => false, 'error' => 'FCM credentials tidak dikonfigurasi'];
        }

        $data = [
            'notification_id' => (string) $notification->id,
            'vehicle_id' => (string) ($notification->vehicle_id ?? ''),
        ];

        $success = false;
        $totalSent = 0;

        // Kirim ke user atau device
        if ($user) {
            $totalSent = $this->fcmService->sendToUser(
                $user->id,
                $notification->title,
                $notification->message,
                $data,
                $notification->category_key,
                $notification->priority
            );
            $success = $totalSent > 0;
        } elseif ($deviceId) {
            $success = $this->fcmService->sendToDeviceId(
                $deviceId,
                $notification->title,
                $notification->message,
                $data,
                $notification->category_key,
                $notification->priority
            );
            $totalSent = $success ? 1 : 0;
        }

        Log::info("NotificationService: Push notification result", [
            'notification_id' => $notification->id,
            'success' => $success,
            'total_sent' => $totalSent,
        ]);

        return [
            'success' => $success,
            'total_sent' => $totalSent,
        ];
    }

    /**
     * Parse template pesan, ganti {variable} dengan nilai sebenarnya.
     */
    public function parseTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        // Hapus variabel yang tidak terganti
        $template = preg_replace('/\{[a-z_]+\}/', '', $template);

        return trim($template);
    }

    /**
     * Generate judul notifikasi dari template.
     */
    private function generateTitle(NotificationTemplate $template, array $variables): string
    {
        // Gunakan nama template sebagai judul dasar, tapi bisa di-customize
        $title = $template->name;

        // Tambahkan konteks jika ada
        if (!empty($variables['vehicle_name'])) {
            $title .= ' - ' . $variables['vehicle_name'];
        }

        return $title;
    }

    /**
     * Isi variabel default yang selalu tersedia.
     */
    private function fillDefaultVariables(array $variables, ?User $user = null, ?Vehicle $vehicle = null): array
    {
        // Variabel umum
        $defaults = [
            'app_name' => config('app.name', 'TringGo'),
            'date_now' => now()->format('d M Y'),
        ];

        // Variabel user
        if ($user) {
            $defaults['user_name'] = $user->name ?? 'Pengguna';
        }

        // Variabel kendaraan
        if ($vehicle) {
            $vehicleName = $vehicle->title ?? trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? ''));
            $defaults['vehicle_name'] = $vehicleName ?: 'Motor Anda';
            $defaults['vehicle_type'] = $vehicle->tipe_motor ?? '-';
            $defaults['current_km'] = number_format($vehicle->odometer ?? 0);
            $defaults['vehicle_year'] = (string) ($vehicle->year ?? '-');
        }

        // Merge defaults, variabel yang di-pass secara eksplisit menang
        return array_merge($defaults, $variables);
    }

    /**
     * Variabel contoh untuk test/preview di admin.
     */
    public function getSampleVariables(): array
    {
        return [
            'vehicle_name'   => 'Honda Beat 2023',
            'vehicle_plate'  => 'B 1234 XYZ',
            'vehicle_type'   => 'Matic',
            'current_km'     => '15.500',
            'vehicle_color'  => 'Merah',
            'vehicle_year'   => '2023',
            'service_type'   => 'Ganti Oli',
            'service_name'   => 'Servis Rutin 16.000 km',
            'km_remaining'   => '500',
            'km_overdue'     => '200',
            'target_km'      => '16.000',
            'target_date'    => '15 Mar 2026',
            'days_remaining' => '14',
            'last_service'   => '01 Jan 2026',
            'workshop_name'  => 'Bengkel AHASS Maju Jaya',
            'distance'       => '25,5',
            'duration'       => '45 menit',
            'avg_speed'      => '35',
            'user_name'      => 'Budi Santoso',
            'app_name'       => config('app.name', 'TringGo'),
            'date_now'       => now()->format('d M Y'),
        ];
    }

    /**
     * Evaluasi jadwal servis dan kirim notifikasi jika diperlukan.
     * Dipanggil oleh scheduler/cron job.
     */
    public function evaluateServiceSchedules(): array
    {
        $results = [];
        $schedules = \App\Models\ServiceSchedule::where('is_active', true)
            ->with(['vehicle', 'serviceType', 'reminderOption'])
            ->get();

        foreach ($schedules as $schedule) {
            $vehicle = $schedule->vehicle;
            if (!$vehicle) continue;

            $currentKm = $vehicle->odometer ?? 0;

            // Evaluasi berdasarkan KM
            if ($schedule->schedule_type === 'km' && $schedule->target_km) {
                $kmRemaining = $schedule->target_km - $currentKm;

                // Cek apakah sudah terlambat (overdue)
                if ($kmRemaining < 0) {
                    $this->sendServiceOverdue(
                        $vehicle,
                        $schedule->serviceType?->name ?? $schedule->service_name ?? 'Servis',
                        abs($kmRemaining)
                    );
                    $results[] = [
                        'schedule_id' => $schedule->id,
                        'type' => 'overdue',
                        'km_overdue' => abs($kmRemaining),
                    ];
                    continue;
                }

                // Cek reminder berdasarkan reminder_option
                $reminderKm = $schedule->reminderOption?->value ?? 500;
                if ($schedule->reminderOption?->unit === 'km' && $kmRemaining <= $reminderKm) {
                    $this->sendServiceReminder(
                        $vehicle,
                        $schedule->serviceType?->name ?? $schedule->service_name ?? 'Servis',
                        $kmRemaining,
                        $schedule->target_km,
                        $schedule->target_date?->format('d M Y')
                    );
                    $results[] = [
                        'schedule_id' => $schedule->id,
                        'type' => 'reminder',
                        'km_remaining' => $kmRemaining,
                    ];
                }
            }

            // Evaluasi berdasarkan waktu
            if ($schedule->schedule_type === 'time' && $schedule->target_date) {
                $daysRemaining = now()->diffInDays($schedule->target_date, false);

                if ($daysRemaining < 0) {
                    // Terlambat
                    $this->sendServiceOverdue(
                        $vehicle,
                        $schedule->serviceType?->name ?? $schedule->service_name ?? 'Servis',
                        0 // kilometer overdue tidak relevan untuk time-based
                    );
                    $results[] = [
                        'schedule_id' => $schedule->id,
                        'type' => 'overdue_time',
                        'days_overdue' => abs($daysRemaining),
                    ];
                    continue;
                }

                // Cek reminder berdasarkan hari
                $reminderOption = $schedule->reminderOption;
                if ($reminderOption) {
                    $reminderDays = match ($reminderOption->unit) {
                        'days' => $reminderOption->value,
                        'weeks' => $reminderOption->value * 7,
                        'months' => $reminderOption->value * 30,
                        default => 7,
                    };

                    if ($daysRemaining <= $reminderDays) {
                        $template = NotificationTemplate::where('trigger_type', 'km_before_interval')
                            ->where('is_active', true)
                            ->first();

                        if ($template) {
                            $user = $vehicle->user_id ? User::find($vehicle->user_id) : null;
                            $this->sendFromTemplate($template, [
                                'service_type' => $schedule->serviceType?->name ?? $schedule->service_name ?? 'Servis',
                                'days_remaining' => $daysRemaining,
                                'target_date' => $schedule->target_date->format('d M Y'),
                            ], $user, $vehicle->device_id, $vehicle);
                        }

                        $results[] = [
                            'schedule_id' => $schedule->id,
                            'type' => 'reminder_time',
                            'days_remaining' => $daysRemaining,
                        ];
                    }
                }
            }
        }

        return $results;
    }
}
