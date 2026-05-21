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
            'adventure' => 'Adventure',
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
        $lines[] = '2. Bahasa Indonesia. Ringkas, jelas, ramah — seperti mekanik yang dipercaya.';
        $lines[] = '3. Jangan sebut nama model AI, nama sistem internal, atau kata "fuzzy".';
        $lines[] = '4. Gunakan bahasa rekomendasi (hindari klaim pasti seperti "pasti rusak").';
        $lines[] = '5. Setiap "saran" pada rekomendasi_komponen harus actionable — beri tahu APA yang harus dilakukan dan KAPAN.';
        $lines[] = '6. Komponen dengan status=normal yang skornya tinggi (mendekati 100) boleh dikelompokkan dalam 1 entri jika sarannya sama.';
        $lines[] = '';

        // ── Struktur JSON output ──────────────────────────────────────────────
        $lines[] = '== FORMAT OUTPUT JSON ==';
        $lines[] = '{';

        // HOME — Wawasan Pintar
        $lines[] = '  "wawasan_pintar": [';
        $lines[] = '    {';
        $lines[] = '      "judul": "Judul singkat max 5 kata, kata kerja aktif. Contoh: Ganti Oli Segera",';
        $lines[] = '      "isi": "1–2 kalimat penjelasan spesifik kenapa ini mendesak sekarang.",';
        $lines[] = '      "prioritas": "critical|warning|normal"';
        $lines[] = '    }';
        $lines[] = '  ],';

        // HOME — Insight Sistem
        $lines[] = '  "insight_sistem": {';
        $lines[] = '    "label": "Penggunaan Ringan|Penggunaan Moderat|Penggunaan Berat",';
        $lines[] = '    "isi": "2–3 kalimat analisa pola berkendara dan dampaknya ke kondisi motor secara umum."';
        $lines[] = '  },';

        // SERVICE — Ringkasan kondisi keseluruhan
        $lines[] = '  "ringkasan_kondisi": "2–3 kalimat ringkasan kondisi motor secara keseluruhan. Sebutkan komponen paling mendesak dan kondisi umum motor.",';

        // SERVICE — Rekomendasi per komponen
        $lines[] = '  "rekomendasi_komponen": [';
        $lines[] = '    {';
        $lines[] = '      "komponen": "Nama komponen persis seperti di daftar skor di atas",';
        $lines[] = '      "prioritas": "critical|warning|normal",';
        $lines[] = '      "saran": "1–2 kalimat saran spesifik: apa yang harus dilakukan dan kapan.",';
        $lines[] = '      "estimasi_waktu": "Contoh: dalam 1–2 minggu | bulan depan | 3 bulan lagi | aman hingga servis berikutnya"';
        $lines[] = '    }';
        $lines[] = '  ],';

        // Tips mandiri
        $lines[] = '  "tips_mandiri": "1 kalimat tips perawatan yang bisa dilakukan sendiri tanpa ke bengkel."';

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
