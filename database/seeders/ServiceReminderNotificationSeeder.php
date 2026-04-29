<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class ServiceReminderNotificationSeeder extends Seeder
{
    /**
     * Seed notification templates untuk service schedule reminders
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Pengingat Servis (KM)',
                'category_key' => 'service',
                'trigger_type' => 'schedule_reminder_km',
                'message_template' => '🔧 Pengingat Servis: {service_name} dalam {km_remaining} km lagi! Target: {target_km} km. Odometer saat ini: {current_km} km.',
                'priority' => 'high',
                'channel' => 'push',
                'threshold_value' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Pengingat Servis (Waktu)',
                'category_key' => 'service',
                'trigger_type' => 'schedule_reminder_time',
                'message_template' => '🔧 Pengingat Servis: {service_name} dalam {days_remaining} hari lagi! Jadwal: {target_date}. Jangan sampai terlambat!',
                'priority' => 'high',
                'channel' => 'push',
                'threshold_value' => null,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $templateData) {
            NotificationTemplate::updateOrCreate(
                [
                    'category_key' => $templateData['category_key'],
                    'trigger_type' => $templateData['trigger_type'],
                ],
                $templateData
            );
        }

        $this->command->info('✓ Service reminder notification templates seeded successfully!');
    }
}
