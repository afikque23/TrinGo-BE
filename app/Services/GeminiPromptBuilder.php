<?php

namespace App\Services;

use Illuminate\Support\Arr;

class GeminiPromptBuilder
{
    /**
     * Build prompt lengkap untuk dikirim ke Gemini API.
     *
     * @param string $motorType   "matic" | "manual" | "sport" | "adventure"
     * @param array  $inputs      Data IoT mentah dari user
     * @param array  $scores      ['oli_mesin' => 74.3, 'ban' => 12.1, ...]
     * @param array  $statuses    ['oli_mesin' => 'warning', 'ban' => 'normal', ...]
     * @param array  $thresholds  ['oli_mesin' => ['warn' => 60, 'critical' => 40], ...]
     */
    public function build(
        string $motorType,
        array $inputs,
        array $scores,
        array $statuses,
        array $thresholds,
    ): string {
        $lines = [];

        // ── Peran ─────────────────────────────────────────────────────────────
        $lines[] = 'Kamu adalah asisten perawatan sepeda motor yang ramah dan praktis.';
        $lines[] = 'Tugasmu adalah menganalisa kondisi motor dan memberikan rekomendasi';
        $lines[] = 'yang jelas, spesifik, dan actionable kepada pemilik motor.';
        $lines[] = '';

        // ── Konteks motor ─────────────────────────────────────────────────────
        $motorTypeLabel = match (strtolower($motorType)) {
            'matic' => 'Matic',
            'manual' => 'Manual/Bebek',
            'sport' => 'Sport',
            default => ucfirst($motorType),
        };

        $odometer = (int) ($inputs['odometer'] ?? 0);
        $jarakServis = round((float) ($inputs['distance_since_service_km'] ?? 0), 1);
        $durasiServis = (int) round((float) ($inputs['duration_since_service_days'] ?? 0));
        $avgSpeed = round((float) ($inputs['avg_speed_kph'] ?? 0), 1);
        $intensitas = round((float) ($inputs['intensity_km_per_day'] ?? 0), 1);

        // Klasifikasi pola berkendara
        $pola = match (true) {
            $intensitas > 30 => 'Berat (> 30 km/hari)',
            $intensitas > 10 => 'Moderat (10–30 km/hari)',
            default => 'Ringan (< 10 km/hari)',
        };

        $lines[] = '== KONTEKS MOTOR ==';
        $lines[] = "Tipe motor       : {$motorTypeLabel}";
        $lines[] = "Odometer saat ini: {$odometer} km";
        $lines[] = "Jarak sejak servis terakhir: {$jarakServis} km";
        $lines[] = "Durasi sejak servis terakhir: {$durasiServis} hari";
        $lines[] = "Kecepatan rata-rata (30 hari): {$avgSpeed} km/jam";
        $lines[] = "Intensitas pakai (30 hari)  : {$intensitas} km/hari";
        $lines[] = "Klasifikasi pola berkendara  : {$pola}";
        $lines[] = '';

        // ── Suhu mesin DS18B20 ────────────────────────────────────────────────
        $engineTempC   = $inputs['engine_temp_c']   ?? null;
        $engineOverheat = (bool) ($inputs['engine_overheat'] ?? false);
        $engineTempAt  = $inputs['engine_temp_at']  ?? null;

        $lines[] = '== KONDISI SUHU MESIN (SENSOR DS18B20 — HANYA UNTUK NARASI) ==';
        if ($engineTempC === null) {
            $lines[] = 'Status sensor    : Tidak tersedia (sensor tidak terpasang atau belum ada data)';
            $lines[] = 'INSTRUKSI SUHU   : Jangan membuat asumsi tentang kondisi suhu mesin. Abaikan seksi ini.';
        } else {
            $engineTempRounded = round((float) $engineTempC, 1);

            $statusSuhu = match (true) {
                $engineTempRounded >= 110.0 => 'KRITIS - OVERHEAT (≥ 110°C)',
                $engineTempRounded >= 90.0  => 'Panas - Perlu Perhatian (90–109°C)',
                default                    => 'Normal (< 90°C)',
            };

            $lines[] = "Suhu Mesin Terkini : {$engineTempRounded} °C";
            $lines[] = "Status Suhu        : {$statusSuhu}";
            if ($engineTempAt) {
                $lines[] = "Waktu Pembacaan    : {$engineTempAt}";
            }
            $lines[] = '';
            $lines[] = 'INSTRUKSI SUHU:';
            $lines[] = '- Gunakan data suhu ini HANYA untuk memperkaya narasi dan konteks deskripsi kondisi motor.';
            $lines[] = '- JANGAN gunakan suhu ini sebagai input penilaian skor atau menentukan status komponen.';
            if ($engineTempRounded >= 110.0) {
                $lines[] = '- WAJIB: Karena suhu dalam kondisi OVERHEAT KRITIS, buat entri khusus di "wawasan_pintar" dengan prioritas="critical" yang menyebutkan suhu kritis dan urgensi pendinginan mesin.';
            } elseif ($engineTempRounded >= 90.0) {
                $lines[] = '- Dianjurkan: Singgung kondisi suhu tinggi dalam narasi "insight_sistem" atau "saran_adaptif" sebagai peringatan preventif.';
            }
        }
        $lines[] = '';

        // ── Skor komponen ─────────────────────────────────────────────────────
        // Skor 0–100: makin KECIL = makin mendesak untuk diservis (sesuai mapping statusFromScore)
        $lines[] = '== SKOR KONDISI KOMPONEN ==';
        $lines[] = 'Skala 0–100. Makin kecil skor = makin mendesak untuk diservis.';
        $lines[] = 'Status: normal = aman, warning = perlu perhatian, critical = segera servis.';
        $lines[] = '';

        // Urutkan: critical dulu, lalu warning, lalu normal
        $sorted = collect($scores)
            ->map(fn ($score, $key) => [
                'key' => (string) $key,
                'score' => round((float) $score, 1),
                'status' => $statuses[$key] ?? 'normal',
                'warn' => Arr::get($thresholds, "{$key}.warn"),
                'critical' => Arr::get($thresholds, "{$key}.critical"),
            ])
            ->sortBy(fn ($item) => match ($item['status']) {
                'critical' => 0,
                'warning' => 1,
                default => 2,
            })
            ->values();

        foreach ($sorted as $item) {
            $namaKomponen = $this->formatComponentName($item['key']);
            $score = $item['score'];
            $status = $item['status'];

            $meta = ["status={$status}"];
            if (is_numeric($item['warn'])) {
                $meta[] = "warn_threshold={$item['warn']}";
            }
            if (is_numeric($item['critical'])) {
                $meta[] = "critical_threshold={$item['critical']}";
            }

            $lines[] = "- {$namaKomponen}: {$score} (" . implode(', ', $meta) . ')';
        }
        $lines[] = '';

        // ── Instruksi output ──────────────────────────────────────────────────
        $lines[] = '== INSTRUKSI ==';
        $lines[] = '1. Balas HANYA dengan JSON valid. Tanpa markdown, tanpa backtick, tanpa penjelasan di luar JSON.';
        $lines[] = '2. Bahasa Indonesia. Edukatif, mendalam, rinci, dan ramah — seperti mekanik profesional yang dipercaya.';
        $lines[] = '3. Jangan sebut nama model AI, nama sistem internal, atau kata "fuzzy".';
        $lines[] = '4. Gunakan bahasa rekomendasi (hindari klaim pasti seperti "pasti rusak").';
        $lines[] = '5. Setiap "saran" pada rekomendasi_komponen harus actionable — beri tahu APA yang harus dilakukan dan KAPAN.';
        $lines[] = '6. Komponen dengan status=normal yang skornya tinggi (mendekati 100) boleh dikelompokkan dalam 1 entri jika sarannya sama.';
        $lines[] = '7. PENTING & WAJIB: Setiap deskripsi pada "ringkasan_kondisi" dan "isi" HARUS ditulis minimal 3 hingga 4 kalimat lengkap.';
        if ($inputs['is_new_data'] ?? false) {
            $lines[] = '8. KONDISI KHUSUS & MUTLAK: Motor ini baru saja ditambahkan dan belum ada riwayat servis atau data perjalanan (IoT).';
            $lines[] = '   - Pada "wawasan_pintar", WAJIB buat entri khusus menyambut motor baru (judul: "Motor Baru Terdaftar" atau "Selamat Datang di TrinGo") dengan penjelasan menyapa pengguna dan menyarankan segera mencatat riwayat servis atau mulai berkendara.';
            $lines[] = '   - Pada "insight_sistem", WAJIB isi "label" dengan "Belum Ada Data" dan "isi" dengan penjelasan bahwa AI membutuhkan data historis servis/trip untuk analisis pola berkendara.';
            $lines[] = '   - Pada "smart_maintenance", "prediksi_servis" dan "saran_adaptif" HARUS berisi pesan bahwa prediksi adaptif akan aktif setelah pengguna mencatatkan riwayat servis pertama atau melakukan perjalanan.';
            $lines[] = '   - Jelaskan pada "ringkasan_kondisi" bahwa semua komponen saat ini menggunakan asumsi awal pabrikan karena belum ada riwayat tercatat.';
        }
        $lines[] = '';

        // ── Struktur JSON output ──────────────────────────────────────────────
        $lines[] = '== FORMAT OUTPUT JSON ==';
        $lines[] = '{';

        // HOME — Wawasan Pintar
        $lines[] = '  "wawasan_pintar": [';
        $lines[] = '    {';
        $lines[] = '      "judul": "Judul singkat max 5 kata, kata kerja aktif. Contoh: Ganti Oli Segera",';
        $lines[] = '      "isi": "2–3 kalimat penjelasan rinci dan mendalam kenapa ini mendesak sekarang.",';
        $lines[] = '      "prioritas": "critical|warning|normal"';
        $lines[] = '    }';
        $lines[] = '  ],';

        // HOME — Insight Sistem
        $lines[] = '  "insight_sistem": {';
        $lines[] = '    "label": "Penggunaan Ringan|Penggunaan Moderat|Penggunaan Berat",';
        $lines[] = '    "isi": "3–4 kalimat analisa detail pola berkendara dan dampaknya ke kondisi komponen motor secara menyeluruh."';
        $lines[] = '  },';

        // HOME — Smart Maintenance Prediktif & Adaptif
        $lines[] = '  "smart_maintenance": {';
        $lines[] = '    "prediksi_servis": "1–2 kalimat estimasi rentang waktu kapan motor harus ke bengkel berdasarkan pola pemakaian harian.",';
        $lines[] = '    "fokus_komponen": ["Daftar nama komponen yang berstatus critical atau warning, diurutkan dari yang paling mendesak"],';
        $lines[] = '    "saran_adaptif": "2–3 kalimat saran pemeliharaan preventif yang disesuaikan dengan pola berkendara dan keausan komponen."';
        $lines[] = '  },';

        // SERVICE — Ringkasan kondisi keseluruhan
        $lines[] = '  "ringkasan_kondisi": "3–4 kalimat ringkasan kondisi motor secara mendalam. Sebutkan komponen paling mendesak, potensi risiko jika ditunda, dan kondisi umum motor.",';

        // SERVICE — Rekomendasi per komponen
        $lines[] = '  "rekomendasi_komponen": [';
        $lines[] = '    {';
        $lines[] = '      "komponen": "Nama komponen persis seperti di daftar skor di atas",';
        $lines[] = '      "prioritas": "critical|warning|normal",';
        $lines[] = '      "saran": "2–3 kalimat saran spesifik: tindakan teknis yang harus dilakukan, alasan, dan kapan waktu terbaik ke bengkel.",';
        $lines[] = '      "estimasi_waktu": "Contoh: dalam 1–2 minggu | bulan depan | 3 bulan lagi | aman hingga servis berikutnya"';
        $lines[] = '    }';
        $lines[] = '  ],';

        // Tips mandiri
        $lines[] = '  "tips_mandiri": "1–2 kalimat tips perawatan praktis yang bisa dilakukan sendiri tanpa ke bengkel."';

        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * Format nama komponen dari snake_case ke nama tampilan
     * Contoh: "oli_mesin" → "Oli Mesin"
     */
    private function formatComponentName(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }
}
