<?php

namespace App\Services;

use App\Models\AiRecommendation;
use App\Models\ComponentConfig;
use App\Models\MotorType;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    public function __construct(
        private FuzzyEngine   $fuzzy,
        private GeminiService $gemini,
    ) {}

    /**
     * Entry point utama — dipanggil dari API Flutter
     *
     * @param int   $userId
     * @param int   $motorId
     * @param int   $motorTypeId
     * @param array $iotData  ['odometer'=>8450,'avg_speed'=>41.5,'durasi_hari'=>45,
     *                         'jarak_sejak_servis'=>950,'servis_date'=>'2024-01-01']
     * @param bool  $forceRefresh  paksa panggil Gemini meski ada cache
     * @return array
     */
    public function getRecommendation(
        int   $userId,
        int   $motorId,
        int   $motorTypeId,
        array $iotData,
        bool  $forceRefresh = false
    ): array {
        // 1. Siapkan input Fuzzy dari data IoT
        $fuzzyInputs = $this->prepareFuzzyInputs($iotData);

        // 2. Hitung skor Fuzzy semua komponen
        $fuzzyScores = $this->fuzzy->calculateAll($motorTypeId, $fuzzyInputs);

        // 3. Buat fingerprint kondisi saat ini
        $fingerprint = $this->fuzzy->buildFingerprint($fuzzyInputs, $fuzzyScores);

        // 4. Cek apakah perlu panggil Gemini baru
        if (!$forceRefresh) {
            $cached = $this->findCache($userId, $motorId, $fingerprint, $fuzzyScores, $iotData);
            if ($cached) return $cached;
        }

        // 5. Panggil Gemini API
        $motorType  = MotorType::find($motorTypeId);
        $motorData  = $this->prepareMotorData($iotData, $motorType, $fuzzyInputs);
        $geminiResult = $this->gemini->generateRecommendation($fuzzyScores, $motorData);

        // 6. Fallback kalau Gemini gagal
        if (!$geminiResult) {
            $geminiResult = $this->generateFallback($fuzzyScores);
        }

        // 7. Simpan ke cache
        $recommendation = AiRecommendation::updateOrCreate(
            ['user_id' => $userId, 'motor_id' => $motorId, 'fingerprint' => $fingerprint],
            [
                'content'      => $geminiResult,
                'reuse_count'  => 0,
                'last_used_at' => now(),
            ]
        );

        return $this->buildResponse($fuzzyScores, $geminiResult, $fingerprint, false);
    }

    /**
     * Cek cache — ada 3 level pengecekan:
     * 1. Fingerprint exact sama (kondisi identik) → reuse
     * 2. Semua komponen baik (skor < 40) → pakai cached "semua baik"
     * 3. Ada trigger darurat (skor > 85) → paksa refresh
     */
    private function findCache(
        int    $userId,
        int    $motorId,
        string $fingerprint,
        array  $fuzzyScores,
        array  $iotData
    ): ?array {
        // Cek trigger darurat — kalau ada komponen sangat kritis, paksa refresh
        $hasEmergency = collect($fuzzyScores)->contains(fn($r) => $r['score'] >= 85);
        if ($hasEmergency) return null;

        // Cari cache dengan fingerprint yang sama
        $cache = AiRecommendation::where('user_id', $userId)
            ->where('motor_id', $motorId)
            ->where('fingerprint', $fingerprint)
            ->latest()
            ->first();

        if ($cache && $cache->isValid(3)) {
            $cache->markReused();
            return $this->buildResponse($fuzzyScores, $cache->content, $fingerprint, true, $cache->updated_at);
        }

        // Cek apakah kondisi serupa pernah ada (lintas user — shared pool)
        $shared = AiRecommendation::where('fingerprint', $fingerprint)
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->first();

        if ($shared) {
            // Simpan sebagai cache user ini juga
            AiRecommendation::updateOrCreate(
                ['user_id' => $userId, 'motor_id' => $motorId, 'fingerprint' => $fingerprint],
                ['content' => $shared->content, 'reuse_count' => 0, 'last_used_at' => now()]
            );
            return $this->buildResponse($fuzzyScores, $shared->content, $fingerprint, true, $shared->updated_at);
        }

        return null;
    }

    /**
     * Siapkan input Fuzzy dari data IoT mentah
     */
    private function prepareFuzzyInputs(array $iotData): array
    {
        $jarakSejak = $iotData['jarak_sejak_servis'] ?? 0;
        $odometer   = $iotData['odometer'] ?? 0;

        // Hitung durasi dari tanggal servis terakhir
        $durasi = 0;
        if (!empty($iotData['servis_date'])) {
            $durasi = now()->diffInDays($iotData['servis_date']);
        } elseif (!empty($iotData['durasi_hari'])) {
            $durasi = $iotData['durasi_hari'];
        }

        // Hitung intensitas (avg km/hari) — dari odometer dibagi hari aktif
        $intensitas = $iotData['avg_km_hari'] ?? ($durasi > 0 ? $jarakSejak / $durasi : 0);

        return [
            'jarak'      => (float) $jarakSejak,
            'durasi'     => (float) $durasi,
            'kecepatan'  => (float) ($iotData['avg_speed'] ?? 0),
            'intensitas' => (float) $intensitas,
        ];
    }

    /**
     * Siapkan data motor untuk prompt Gemini
     */
    private function prepareMotorData(array $iotData, ?MotorType $motorType, array $fuzzyInputs): array
    {
        $pola = match(true) {
            $fuzzyInputs['intensitas'] > 20 => 'Berat',
            $fuzzyInputs['intensitas'] > 8  => 'Moderat',
            default                          => 'Ringan',
        };

        return [
            'jenis'       => $motorType?->name ?? 'Motor',
            'odometer'    => $iotData['odometer'] ?? 0,
            'avg_km_hari' => round($fuzzyInputs['intensitas'], 1),
            'avg_speed'   => round($fuzzyInputs['kecepatan'], 1),
            'pola'        => $pola,
        ];
    }

    /**
     * Fallback kalau Gemini tidak bisa dihubungi
     */
    private function generateFallback(array $fuzzyScores): array
    {
        $kritis = collect($fuzzyScores)->filter(fn($r) => $r['score'] >= 75)->keys()->first();
        $perlu  = collect($fuzzyScores)->filter(fn($r) => $r['score'] >= 40)->keys()->first();

        $prioritas = $kritis ?? $perlu ?? null;
        $insight   = $prioritas
            ? "Komponen {$prioritas} perlu perhatian segera. Segera jadwalkan servis."
            : "Kondisi motor secara keseluruhan baik. Tetap pantau secara rutin.";

        return [
            'insight_singkat'  => $insight,
            'prioritas_utama'  => $prioritas,
            'estimasi_servis'  => $prioritas ? 'Segera' : 'Rutin sesuai jadwal',
            'rekomendasi'      => collect($fuzzyScores)->map(fn($r, $name) => [
                'komponen'       => $name,
                'skor'           => $r['score'],
                'status'         => $r['label'] === 'Kritis' ? 'segera_servis' : ($r['label'] === 'Perlu Servis' ? 'perlu_servis' : 'baik'),
                'urgensi'        => $r['score'] >= 75 ? 'tinggi' : ($r['score'] >= 40 ? 'sedang' : 'rendah'),
                'pesan'          => $r['label'] === 'Baik' ? 'Kondisi masih baik.' : "Segera periksa {$name}.",
                'estimasi_biaya' => null,
            ])->values()->toArray(),
            'catatan_pola'  => 'Data pola berkendara sedang dianalisa.',
            'tips_mandiri'  => 'Periksa tekanan ban dan level oli secara berkala.',
            'is_fallback'   => true,
        ];
    }

    /**
     * Format response akhir yang dikirim ke Flutter
     */
    private function buildResponse(
        array   $fuzzyScores,
        array   $geminiResult,
        string  $fingerprint,
        bool    $fromCache,
        mixed   $cacheDate = null
    ): array {
        return [
            'fuzzy_scores'  => $fuzzyScores,
            'recommendation'=> $geminiResult,
            'meta' => [
                'fingerprint'  => $fingerprint,
                'from_cache'   => $fromCache,
                'cache_date'   => $cacheDate?->toISOString(),
                'generated_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Cek apakah perlu trigger update Gemini
     * Dipanggil setiap kali ada data IoT baru masuk
     */
    public function shouldRefresh(int $userId, int $motorId, array $iotData): bool
    {
        $last = AiRecommendation::where('user_id', $userId)
            ->where('motor_id', $motorId)
            ->latest()
            ->first();

        if (!$last) return true; // Belum pernah ada rekomendasi

        // Trigger 1: Sudah lebih dari 3 hari
        if ($last->created_at->diffInDays(now()) >= 3) return true;

        // Trigger 2: Jarak bertambah > 200 km sejak rekomendasi terakhir
        $lastOdometer = $last->content['meta']['odometer'] ?? 0;
        if (($iotData['odometer'] ?? 0) - $lastOdometer >= 200) return true;

        // Trigger 3: Ada komponen yang baru masuk kategori kritis
        // (dicek dari fingerprint perubahan)

        return false;
    }
}
