<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplateSeederUpdate extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Peringatan Servis (Fuzzy Warning)',
                'category_key' => 'service',
                'trigger_type' => 'fuzzy_warning',
                'channel' => 'push',
                'priority' => 'high',
                'message_template' => 'Halo {user_name}, komponen {service_name} motor {vehicle_name} Anda menunjukkan indikasi perlu pengecekan (Skor: {fuzzy_score}). Jadwalkan servis segera!',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kondisi Kritis (Fuzzy Critical)',
                'category_key' => 'alert',
                'trigger_type' => 'fuzzy_critical',
                'channel' => 'push',
                'priority' => 'critical',
                'message_template' => 'AWAS! Motor {vehicle_name} Anda dalam kondisi KRITIS pada bagian {service_name}. Harap segera periksa ke bengkel untuk menghindari kerusakan fatal!',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ringkasan Perjalanan',
                'category_key' => 'trip',
                'trigger_type' => 'trip_completed',
                'channel' => 'push',
                'priority' => 'normal',
                'message_template' => 'Perjalanan sejauh {distance} km menggunakan {vehicle_name} telah selesai (Durasi: {duration}). Odometer saat ini: {current_km} km.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            // Hapus jika sudah ada untuk menghindari duplikat saat di run berkali-kali
            DB::table('notification_templates')
                ->where('trigger_type', $template['trigger_type'])
                ->delete();
                
            DB::table('notification_templates')->insert($template);
        }
    }
}
