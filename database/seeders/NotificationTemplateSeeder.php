<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Service Category Templates
            [
                'name' => 'Pengingat Servis Rutin',
                'category_key' => 'service',
                'trigger_type' => 'km_before_interval',
                'threshold_value' => 500,
                'priority' => 'high',
                'channel' => 'push',
                'message_template' => '🔧 Halo {user_name}, {vehicle_name} perlu {service_type} dalam {km_remaining} km lagi (target: {target_km} km). Jadwalkan sekarang!',
                'is_active' => true,
            ],
            [
                'name' => 'Pengingat Servis Berdasarkan Waktu',
                'category_key' => 'service',
                'trigger_type' => 'time_before_interval',
                'threshold_value' => 7,
                'priority' => 'high',
                'channel' => 'push',
                'message_template' => '📅 {user_name}, jadwal {service_type} untuk {vehicle_name} tinggal {days_remaining} hari lagi (tanggal: {target_date}). Jangan sampai terlewat!',
                'is_active' => true,
            ],
            [
                'name' => 'Konfirmasi Servis Selesai',
                'category_key' => 'service',
                'trigger_type' => 'service_completed',
                'threshold_value' => null,
                'priority' => 'normal',
                'channel' => 'in_app',
                'message_template' => '✅ Servis {service_type} untuk {vehicle_name} tercatat pada {date_now} di KM {current_km}. Terima kasih sudah merawat motor Anda!',
                'is_active' => true,
            ],
            // Alert Category Templates
            [
                'name' => 'Peringatan Servis Terlambat',
                'category_key' => 'alert',
                'trigger_type' => 'overdue',
                'threshold_value' => 0,
                'priority' => 'critical',
                'channel' => 'push',
                'message_template' => '⚠️ PENTING: {vehicle_name} sudah melewati jadwal {service_type} sebanyak {km_overdue} km! Segera lakukan servis untuk menjaga kondisi motor.',
                'is_active' => true,
            ],
            [
                'name' => 'Odometer Tinggi',
                'category_key' => 'alert',
                'trigger_type' => 'high_odometer',
                'threshold_value' => 50000,
                'priority' => 'normal',
                'channel' => 'in_app',
                'message_template' => '📊 {vehicle_name} sudah mencapai {current_km} km. Pastikan semua komponen dalam kondisi baik. Terakhir servis di {workshop_name}.',
                'is_active' => true,
            ],
            // Trip Category Templates
            [
                'name' => 'Perjalanan Selesai',
                'category_key' => 'trip',
                'trigger_type' => 'trip_completed',
                'threshold_value' => null,
                'priority' => 'normal',
                'channel' => 'push',
                'message_template' => '✅ Perjalanan selesai! {vehicle_name} menempuh {distance} km dalam {duration}. Kecepatan rata-rata: {avg_speed} km/h. Odometer sekarang: {current_km} km.',
                'is_active' => true,
            ],
            // Insight Category Templates
            [
                'name' => 'Insight Jadwal Servis',
                'category_key' => 'insight',
                'trigger_type' => 'service_insight',
                'threshold_value' => null,
                'priority' => 'low',
                'channel' => 'in_app',
                'message_template' => '💡 {user_name}, berdasarkan pola berkendara Anda, {vehicle_name} ({vehicle_plate}) sebaiknya servis {service_type} sebelum {target_date}. Terakhir servis: {last_service}.',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                [
                    'name' => $template['name'],
                    'category_key' => $template['category_key'],
                ],
                $template
            );
        }
    }
}
