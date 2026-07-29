<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct(private readonly GeminiPromptBuilder $promptBuilder)
    {
        $this->apiKey = (string) config('services.gemini.api_key', '');
        $this->model = (string) config('services.gemini.model', 'gemini-2.0-flash');
    }

    /**
     * Generate rekomendasi lengkap dari data IoT + skor Fuzzy.
     * Return array siap simpan ke kolom cache `sections` (AiRecommendationCache).
     */
    public function generateRecommendation(
        string $motorType,
        array $inputs,
        array $scores,
        array $statuses,
        array $thresholds,
    ): ?array {
        if ($this->apiKey === '') {
            return null;
        }

        $prompt = $this->promptBuilder->build(
            motorType: $motorType,
            inputs: $inputs,
            scores: $scores,
            statuses: $statuses,
            thresholds: $thresholds,
        );

        $temperature = (float) config('services.gemini.temperature', 0.4);
        $maxOutputTokens = (int) config('services.gemini.max_output_tokens', 900);

        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => $maxOutputTokens,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'model' => $this->model,
                ]);
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            $parsed = $this->parseAndValidate($text);
            if (!$parsed) {
                return null;
            }

            return $parsed;
        } catch (\Throwable $e) {
            Log::error('Gemini API exception', [
                'message' => $e->getMessage(),
                'model' => $this->model,
            ]);
            return null;
        }
    }

    /**
     * Parse JSON dari Gemini dan validasi struktur wajib.
     */
    private function parseAndValidate(?string $text): ?array
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }

        // Bersihkan jika Gemini tetap wrap dengan backtick
        $clean = preg_replace('/```json\s*|\s*```/', '', $text);
        $clean = trim((string) $clean);

        try {
            $data = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('Gemini JSON parse error', [
                'error' => $e->getMessage(),
                'text' => $text,
            ]);
            return null;
        }

        if (!is_array($data)) {
            return null;
        }

        // Validasi field wajib
        $required = ['wawasan_pintar', 'insight_sistem', 'ringkasan_kondisi', 'rekomendasi_komponen', 'tips_mandiri'];
        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                Log::warning("Gemini response missing field: {$field}");
                return null;
            }
        }

        // Pastikan wawasan_pintar adalah array
        if (!is_array($data['wawasan_pintar'])) {
            $data['wawasan_pintar'] = [];
        }

        // Pastikan rekomendasi_komponen adalah array
        if (!is_array($data['rekomendasi_komponen'])) {
            $data['rekomendasi_komponen'] = [];
        }

        // Pastikan rekomendasi_komponen diurutkan: critical → warning → normal
        usort($data['rekomendasi_komponen'], function ($a, $b) {
            $order = ['critical' => 0, 'warning' => 1, 'normal' => 2];
            $pa = is_array($a) ? ($a['prioritas'] ?? 'normal') : 'normal';
            $pb = is_array($b) ? ($b['prioritas'] ?? 'normal') : 'normal';
            return ($order[$pa] ?? 2) <=> ($order[$pb] ?? 2);
        });

        return $data;
    }

    /**
     * Fallback jika Gemini tidak bisa dihubungi.
     * Generate rekomendasi minimal dari data Fuzzy saja.
     */
    public function generateFallback(
        array $scores,
        array $statuses,
        float $intensitas = 0,
    ): array {
        $kritisItems = [];
        $warningItems = [];
        $normalItems = [];

        foreach ($statuses as $key => $status) {
            $nama = ucwords(str_replace('_', ' ', (string) $key));
            match ($status) {
                'critical' => $kritisItems[] = $nama,
                'warning' => $warningItems[] = $nama,
                default => $normalItems[] = $nama,
            };
        }

        // Wawasan pintar — ambil yang paling mendesak
        $wawasanPintar = [];
        foreach ($kritisItems as $nama) {
            $wawasanPintar[] = [
                'judul' => "Servis {$nama} Segera",
                'isi' => "{$nama} membutuhkan perhatian segera berdasarkan kondisi terkini motor Anda.",
                'prioritas' => 'critical',
            ];
        }
        foreach ($warningItems as $nama) {
            $wawasanPintar[] = [
                'judul' => "Pantau {$nama}",
                'isi' => "{$nama} mendekati batas servis. Rencanakan pemeriksaan dalam waktu dekat.",
                'prioritas' => 'warning',
            ];
        }
        if (count($wawasanPintar) === 0) {
            $wawasanPintar[] = [
                'judul' => 'Kondisi Umum Baik',
                'isi' => 'Kondisi motor masih aman. Tetap lakukan pengecekan rutin agar performa tetap stabil.',
                'prioritas' => 'normal',
            ];
        }

        // Insight sistem
        $polaLabel = match (true) {
            $intensitas > 30 => 'Penggunaan Berat',
            $intensitas > 10 => 'Penggunaan Moderat',
            default => 'Penggunaan Ringan',
        };

        // Ringkasan
        $prioritasUtama = $kritisItems[0] ?? $warningItems[0] ?? null;
        $ringkasan = $prioritasUtama
            ? "Motor Anda memerlukan perhatian khusus pada komponen {$prioritasUtama} yang saat ini terindikasi berada dalam kondisi kritis. Penundaan pemeriksaan dapat menyebabkan penurunan performa mesin dan risiko keausan komponen lainnya secara berantai. Disarankan untuk segera melakukan inspeksi dan penanganan di bengkel terpercaya agar keamanan berkendara tetap terjamin."
            : 'Kondisi kendaraan secara keseluruhan saat ini berada dalam keadaan yang cukup baik dan stabil. Tetap pertahankan pola pemeliharaan rutin sesuai rekomendasi pabrikan untuk mencegah penurunan fungsi komponen. Lakukan pemeriksaan berkala secara mandiri maupun pada jadwal servis resmi berikutnya.';

        // Rekomendasi komponen
        $rekomendasiKomponen = [];
        foreach ($statuses as $key => $status) {
            $nama = ucwords(str_replace('_', ' ', (string) $key));
            $score = round((float) ($scores[$key] ?? 0), 1);

            $rekomendasiKomponen[] = [
                'komponen' => $nama,
                'prioritas' => $status,
                'saran' => match ($status) {
                    'critical' => "Komponen {$nama} membutuhkan perbaikan atau penggantian segera karena telah mencapai batas kritis keausan. Mengabaikan kondisi ini berpotensi merusak komponen terkait lainnya dan mengganggu kenyamanan berkendara. Segera bawa kendaraan Anda ke bengkel resmi terdekat untuk penanganan teknis.",
                    'warning' => "Kondisi {$nama} telah mendekati ambang batas toleransi penggunaan normal. Disarankan untuk memasukkan komponen ini ke dalam daftar prioritas pemeriksaan pada servis berikutnya. Hal ini penting untuk mencegah penurunan kinerja kendaraan yang lebih parah.",
                    default => "{$nama} saat ini dalam kondisi optimal (skor {$score}) dan berfungsi dengan sangat baik. Lanjutkan pola berkendara secara normal dan lakukan pemantauan secara periodik. Tidak diperlukan tindakan perbaikan darurat untuk komponen ini saat ini.",
                },
                'estimasi_waktu' => match ($status) {
                    'critical' => 'secepatnya',
                    'warning' => 'dalam 2–4 minggu',
                    default => 'aman hingga servis berikutnya',
                },
            ];
        }

        usort($rekomendasiKomponen, fn ($a, $b) =>
            (['critical' => 0, 'warning' => 1, 'normal' => 2][$a['prioritas']] ?? 2)
            <=>
            (['critical' => 0, 'warning' => 1, 'normal' => 2][$b['prioritas']] ?? 2)
        );

        return [
            'wawasan_pintar' => $wawasanPintar,
            'insight_sistem' => [
                'label' => $polaLabel,
                'isi' => "Pola berkendara {$polaLabel}. Lakukan pemeriksaan berkala agar kondisi komponen motor tetap terjaga.",
            ],
            'smart_maintenance' => [
                'prediksi_servis' => $prioritasUtama
                    ? "Berdasarkan intensitas pemakaian saat ini, kendaraan diproyeksikan membutuhkan perawatan pada {$prioritasUtama} dalam 1–2 minggu ke depan."
                    : "Kondisi motor tergolong prima. Diperkirakan servis rutin berikutnya sekitar 1–2 bulan ke depan.",
                'fokus_komponen' => count($kritisItems) > 0 ? $kritisItems : ($warningItems ?: ['Oli Mesin']),
                'saran_adaptif' => "Lakukan pengecekan rutin tekanan ban dan pelumasan rantai/CVT secara berkala untuk menjaga efisiensi dan keamanan berkendara.",
            ],
            'ringkasan_kondisi' => $ringkasan,
            'rekomendasi_komponen' => $rekomendasiKomponen,
            'tips_mandiri' => 'Periksa tekanan ban dan level oli secara mandiri setiap 2 minggu.',
        ];
    }
}
