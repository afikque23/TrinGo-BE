<?php

namespace Database\Seeders;

use App\Models\Tip;
use App\Models\TipStep;
use App\Models\TipTag;
use App\Models\TipTool;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplatePerawatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()
            ->where('email', 'admin@mototracker.com')
            ->first();

        if (!$admin) {
            $admin = User::query()->where('role', 'admin')->first();
        }

        if (!$admin) {
            $admin = User::query()->create([
                'name' => 'Administrator',
                'email' => 'admin@mototracker.com',
                'password' => 'admin123',
                'role' => 'admin',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }

        $templates = [
            [
                'title' => 'Ganti Oli Mesin Rutin',
                'description' => 'Panduan ganti oli mesin secara rutin untuk menjaga performa, mengurangi gesekan, dan memperpanjang usia mesin.',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'Vario 160',
                'vehicle_year' => 2023,
                'riding_style' => 'Harian / Commuter',
                'estimated_time' => '20-30 menit',
                'interval_distance_km' => 2000,
                'interval_time_months' => 2,
                'important_notes' => 'Gunakan oli sesuai spesifikasi pabrikan dan pastikan volume tepat.',
                'hashtags' => ['oli', 'servis', 'harian'],
                'tools' => ['Kunci pas', 'Wadah penampung oli', 'Corong'],
                'steps' => [
                    'Panaskan mesin 2-3 menit lalu matikan',
                    'Buka baut pembuangan dan tiriskan oli lama',
                    'Pasang kembali baut, isi oli baru sesuai takaran',
                ],
            ],
            [
                'title' => 'Cek dan Setel Rem Depan/Belakang',
                'description' => 'Pemeriksaan sistem pengereman untuk memastikan kampas rem, minyak rem, dan respons rem tetap aman dipakai harian.',
                'vehicle_brand' => 'Yamaha',
                'vehicle_model' => 'NMAX 155',
                'vehicle_year' => 2022,
                'riding_style' => 'Urban / City',
                'estimated_time' => '15-25 menit',
                'interval_distance_km' => 3000,
                'interval_time_months' => 3,
                'important_notes' => 'Jika bunyi berdecit atau rem terasa blong, segera servis di bengkel.',
                'hashtags' => ['rem', 'aman', 'kota'],
                'tools' => ['Kunci L', 'Obeng'],
                'steps' => [
                    'Periksa ketebalan kampas dan kondisi piringan/drum',
                    'Cek freeplay tuas/pedal dan setel jika perlu',
                    'Tes pengereman di kecepatan rendah',
                ],
            ],
            [
                'title' => 'Cek Tekanan Angin & Kondisi Ban',
                'description' => 'Tekanan angin dan kondisi ban yang tepat meningkatkan grip, efisiensi bahan bakar, dan mencegah aus tidak merata.',
                'vehicle_brand' => 'Suzuki',
                'vehicle_model' => 'Address 110',
                'vehicle_year' => 2021,
                'riding_style' => 'Harian / Commuter',
                'estimated_time' => '10 menit',
                'interval_distance_km' => 1000,
                'interval_time_months' => 1,
                'important_notes' => 'Cek tekanan saat ban dingin untuk hasil akurat.',
                'hashtags' => ['ban', 'safety', 'harian'],
                'tools' => ['Pressure gauge', 'Pompa angin'],
                'steps' => [
                    'Cek tekanan ban depan dan belakang sesuai rekomendasi',
                    'Periksa retak, benjol, atau benda asing menancap',
                    'Pastikan kedalaman tapak masih aman',
                ],
            ],
            [
                'title' => 'Bersihkan Filter Udara',
                'description' => 'Filter udara bersih menjaga suplai udara ke mesin tetap optimal sehingga pembakaran lebih efisien dan tarikan stabil.',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'Beat',
                'vehicle_year' => 2020,
                'riding_style' => 'Urban / City',
                'estimated_time' => '20 menit',
                'interval_distance_km' => 4000,
                'interval_time_months' => 4,
                'important_notes' => 'Jika filter kertas terlalu kotor, lebih baik ganti baru.',
                'hashtags' => ['filter', 'mesin', 'irit'],
                'tools' => ['Obeng', 'Kain lap bersih'],
                'steps' => [
                    'Buka cover box filter udara',
                    'Keluarkan filter dan bersihkan sesuai tipe filter',
                    'Pasang kembali filter dan cover dengan rapat',
                ],
            ],
            [
                'title' => 'Cek Aki dan Sistem Kelistrikan',
                'description' => 'Pemeriksaan aki, terminal, dan pengisian agar starter lebih ringan dan lampu/klakson bekerja normal.',
                'vehicle_brand' => 'Yamaha',
                'vehicle_model' => 'Aerox 155',
                'vehicle_year' => 2021,
                'riding_style' => 'Sport / Track',
                'estimated_time' => '15-30 menit',
                'interval_distance_km' => 5000,
                'interval_time_months' => 6,
                'important_notes' => 'Jika tegangan turun drastis, pertimbangkan ganti aki.',
                'hashtags' => ['aki', 'kelistrikan', 'starter'],
                'tools' => ['Multimeter', 'Kunci 10'],
                'steps' => [
                    'Cek kondisi terminal aki dan bersihkan korosi',
                    'Ukur tegangan aki saat mesin mati dan hidup',
                    'Pastikan lampu dan klakson normal',
                ],
            ],
            [
                'title' => 'Servis CVT (Matic): Bersihkan & Cek V-belt',
                'description' => 'Perawatan CVT untuk motor matic agar akselerasi halus, mengurangi getar, dan mencegah v-belt cepat aus.',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'PCX 160',
                'vehicle_year' => 2022,
                'riding_style' => 'Touring',
                'estimated_time' => '45-60 menit',
                'interval_distance_km' => 8000,
                'interval_time_months' => 8,
                'important_notes' => 'Gunakan kompresor angin/kuas untuk bersihkan debu CVT, hindari oli/grease berlebih.',
                'hashtags' => ['cvt', 'matic', 'touring'],
                'tools' => ['Kunci T', 'Kompresor angin / kuas', 'Sarung tangan'],
                'steps' => [
                    'Buka cover CVT dengan hati-hati',
                    'Bersihkan debu dan cek kondisi roller & v-belt',
                    'Pasang kembali cover dan test jalan pelan',
                ],
            ],
            [
                'title' => 'Cek Rantai & Pelumasan (Motor Manual)',
                'description' => 'Rantai yang kencang dan terlumasi mengurangi noise, menjaga efisiensi tenaga, dan mencegah loncat rantai.',
                'vehicle_brand' => 'Kawasaki',
                'vehicle_model' => 'W175',
                'vehicle_year' => 2020,
                'riding_style' => 'Kombinasi',
                'estimated_time' => '20-30 menit',
                'interval_distance_km' => 2000,
                'interval_time_months' => 2,
                'important_notes' => 'Setel kekencangan sesuai standar (jangan terlalu kencang).',
                'hashtags' => ['rantai', 'manual', 'perawatan'],
                'tools' => ['Chain lube', 'Sikat rantai', 'Kunci ring'],
                'steps' => [
                    'Bersihkan rantai dari kotoran dan pasir',
                    'Lumasi rantai merata lalu tunggu meresap',
                    'Cek dan setel slack rantai sesuai standar',
                ],
            ],
            [
                'title' => 'Cek Cairan Pendingin (Radiator)',
                'description' => 'Cek volume dan kondisi coolant untuk mencegah overheat serta menjaga suhu kerja mesin tetap stabil.',
                'vehicle_brand' => 'Yamaha',
                'vehicle_model' => 'R15',
                'vehicle_year' => 2020,
                'riding_style' => 'Sport / Track',
                'estimated_time' => '10-15 menit',
                'interval_distance_km' => 5000,
                'interval_time_months' => 6,
                'important_notes' => 'Buka tutup radiator saat mesin dingin untuk keamanan.',
                'hashtags' => ['radiator', 'coolant', 'overheat'],
                'tools' => ['Senter', 'Kain lap'],
                'steps' => [
                    'Pastikan mesin dingin lalu cek reservoir coolant',
                    'Periksa kebocoran pada selang dan sambungan',
                    'Tambahkan coolant jika berada di bawah batas minimum',
                ],
            ],
            [
                'title' => 'Cek Busi & Kondisi Pembakaran',
                'description' => 'Busi berpengaruh ke mudahnya starter, konsumsi bahan bakar, dan kestabilan idle. Cek warna dan celah busi.',
                'vehicle_brand' => 'Suzuki',
                'vehicle_model' => 'GSX-R150',
                'vehicle_year' => 2021,
                'riding_style' => 'Sport / Track',
                'estimated_time' => '20-30 menit',
                'interval_distance_km' => 6000,
                'interval_time_months' => 6,
                'important_notes' => 'Jika elektroda aus atau warna busi tidak normal, konsultasikan penyetelan campuran/bahan bakar.',
                'hashtags' => ['busi', 'mesin', 'tuneup'],
                'tools' => ['Kunci busi', 'Feeler gauge (opsional)'],
                'steps' => [
                    'Lepas busi menggunakan kunci busi',
                    'Cek warna busi dan kondisi elektroda',
                    'Pasang kembali dengan torsi yang tepat',
                ],
            ],
            [
                'title' => 'Cek Bearing Roda & Suspensi',
                'description' => 'Pemeriksaan bearing roda dan suspensi untuk mengurangi gejala oblak, bunyi, dan menjaga handling tetap stabil.',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'CB150R',
                'vehicle_year' => 2019,
                'riding_style' => 'Touring',
                'estimated_time' => '25-40 menit',
                'interval_distance_km' => 10000,
                'interval_time_months' => 12,
                'important_notes' => 'Jika terasa seret/berbunyi, sebaiknya ganti bearing dan cek seal.',
                'hashtags' => ['bearing', 'suspensi', 'handling'],
                'tools' => ['Standar tengah / paddock stand', 'Senter', 'Kunci ring'],
                'steps' => [
                    'Angkat roda (aman) lalu cek oblak kiri-kanan',
                    'Putar roda dan dengarkan suara bearing',
                    'Cek kebocoran suspensi dan respons redam',
                ],
            ],
        ];

        DB::transaction(function () use ($templates, $admin) {
            foreach ($templates as $template) {
                /** @var \App\Models\Tip $tip */
                $tip = Tip::query()->updateOrCreate(
                    [
                        'user_id' => $admin->id,
                        'title' => $template['title'],
                    ],
                    [
                        'description' => $template['description'],
                        'vehicle_brand' => $template['vehicle_brand'],
                        'vehicle_model' => $template['vehicle_model'],
                        'vehicle_year' => $template['vehicle_year'],
                        'riding_style' => $template['riding_style'],
                        'estimated_time' => $template['estimated_time'],
                        'interval_distance_km' => $template['interval_distance_km'],
                        'interval_time_months' => $template['interval_time_months'],
                        'important_notes' => $template['important_notes'],
                        'hashtags' => $template['hashtags'],
                        'is_copyable' => true,
                        'status' => 'published',
                    ]
                );

                // Re-sync tools
                $tip->tools()->delete();
                foreach ($template['tools'] as $index => $toolName) {
                    TipTool::query()->create([
                        'tip_id' => $tip->id,
                        'name' => $toolName,
                        'is_optional' => false,
                        'order' => $index + 1,
                    ]);
                }

                // Re-sync steps
                $tip->steps()->delete();
                foreach ($template['steps'] as $index => $stepTitle) {
                    TipStep::query()->create([
                        'tip_id' => $tip->id,
                        'step_number' => $index + 1,
                        'title' => $stepTitle,
                        'description' => $stepTitle,
                    ]);
                }

                // Ensure riding_style tag is attached (mirrors admin controller behavior)
                $tag = TipTag::query()->firstOrCreate(
                    [
                        'name' => $tip->riding_style,
                        'type' => 'riding_style',
                    ],
                    [
                        'color' => '#2B7FFF',
                        'icon' => 'directions_bike',
                    ]
                );

                $existingRidingStyleIds = $tip->tags()
                    ->where('type', 'riding_style')
                    ->pluck('tip_tags.id')
                    ->all();

                if (!empty($existingRidingStyleIds)) {
                    $tip->tags()->detach($existingRidingStyleIds);
                }

                $tip->tags()->syncWithoutDetaching([$tag->id]);
            }
        });

        $this->command?->info('Template perawatan (tips) seeded: 10 items for admin.');
    }
}
