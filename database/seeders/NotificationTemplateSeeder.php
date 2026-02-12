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
                'threshold_value' => 200,
                'priority' => 'high',
                'channel' => 'push',
                'message_template' => '🔧 Servis {service_type} dalam {km_remaining} km lagi! Estimasi biaya: {predicted_cost}',
                'is_active' => true,
            ],
            [
                'name' => 'Peringatan Servis Kritikal',
                'category_key' => 'alert',
                'trigger_type' => 'overdue',
                'threshold_value' => 10,
                'priority' => 'critical',
                'channel' => 'push',
                'message_template' => '⚠️ URGENT: Motor Anda sudah melewati jadwal {service_type} sebanyak {km_overdue} km!',
                'is_active' => true,
            ],
            [
                'name' => 'Rekomendasi Bengkel Terdekat',
                'category_key' => 'insight',
                'trigger_type' => 'km_before_interval',
                'threshold_value' => 150,
                'priority' => 'normal',
                'channel' => 'in_app',
                'message_template' => '📍 {workshop_name} ({rating} ⭐) berjarak {distance} km. Booking sekarang!',
                'is_active' => true,
            ],
            [
                'name' => 'Insight Pola Berkendara',
                'category_key' => 'insight',
                'trigger_type' => 'monthly_summary',
                'threshold_value' => 0,
                'priority' => 'low',
                'channel' => 'in_app',
                'message_template' => '📊 Pola berkendara Anda: {riding_pattern}, rata-rata {avg_distance} km/hari 🏍️',
                'is_active' => true,
            ],
            // Trip Category Templates
            [
                'name' => 'Perjalanan Selesai',
                'category_key' => 'trip',
                'trigger_type' => 'trip_completed',
                'threshold_value' => null,
                'priority' => 'normal',
                'channel' => 'in_app',
                'message_template' => '✅ Perjalanan selesai! Jarak: {distance} km, Durasi: {duration}',
                'is_active' => true,
            ],
            [
                'name' => 'Rekor Perjalanan Baru',
                'category_key' => 'trip',
                'trigger_type' => 'new_record',
                'threshold_value' => null,
                'priority' => 'normal',
                'channel' => 'in_app',
                'message_template' => '🏆 Selamat! Anda mencatatkan perjalanan terjauh: {distance} km',
                'is_active' => true,
            ],
            // Alert Category Templates
            [
                'name' => 'BBM Menipis',
                'category_key' => 'alert',
                'trigger_type' => 'fuel_low',
                'threshold_value' => 50,
                'priority' => 'high',
                'channel' => 'push',
                'message_template' => '⛽ BBM tersisa {fuel_percentage}%. SPBU terdekat: {nearest_station} ({distance} km)',
                'is_active' => true,
            ],
            [
                'name' => 'Dokumen Akan Kedaluwarsa',
                'category_key' => 'alert',
                'trigger_type' => 'document_expiring',
                'threshold_value' => 30,
                'priority' => 'high',
                'channel' => 'push',
                'message_template' => '📄 {document_type} Anda akan kedaluwarsa dalam {days_remaining} hari!',
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
