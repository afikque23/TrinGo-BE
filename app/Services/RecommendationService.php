<?php

namespace App\Services;

use App\Models\AiRecommendationCache;
use App\Models\Vehicle;
use App\Services\Ai\GeminiClient;
use App\Services\Fuzzy\FuzzyEngine;
use App\Services\Fuzzy\FuzzyEngineV2;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    public function __construct(
        private readonly FuzzyEngine $fuzzyEngine,
        private readonly FuzzyEngineV2 $fuzzyEngineV2,
        private readonly GeminiClient $geminiClient,
    ) {
    }

    public function getVehicleRecommendations(int $userId, Vehicle $vehicle): array
    {
        $inputs = $this->buildInputsFromVehicle($vehicle);

        $motorType = strtolower($vehicle->tipe_motor ?? '');
        $useV2 = $this->fuzzyEngineV2->supportsMotorType($motorType);
        $fuzzy = $useV2
            ? $this->fuzzyEngineV2->evaluateMotorType($motorType, $inputs)
            : $this->fuzzyEngine->evaluateMotorType($motorType, $inputs);
        $scores = $fuzzy['scores'];
        $statuses = $fuzzy['statuses'];
        $thresholds = $fuzzy['thresholds'];

        $configHash = $useV2
            ? $this->fuzzyEngineV2->configHashForMotorType($motorType)
            : $this->fuzzyEngine->configHashForMotorType($motorType);
        $scoresHash = hash('sha256', json_encode([
            'scores' => $this->roundScores($scores),
            'statuses' => $statuses,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $cache = AiRecommendationCache::query()
            ->where('user_id', $userId)
            ->where('vehicle_id', $vehicle->id)
            ->first();

        $shouldCallGemini = $this->shouldCallGemini($cache, $scores, $statuses, $configHash);

        $sections = $cache?->sections ?? null;
        $usedCache = !$shouldCallGemini && $sections;
        $generatedAt = $cache?->generated_at;

        if ($shouldCallGemini) {
            $prompt = $this->buildGeminiPrompt(
                motorType: $motorType,
                vehicle: $vehicle,
                inputs: $inputs,
                scores: $scores,
                statuses: $statuses,
                thresholds: $thresholds,
            );
            $promptHash = hash('sha256', $prompt);

            try {
                $res = $this->geminiClient->generate($prompt);
                $parsed = $this->parseGeminiSections($res['text'] ?? '');
                $sections = $parsed ?: $this->fallbackSections($scores, $statuses);

                AiRecommendationCache::updateOrCreate([
                    'user_id' => $userId,
                    'vehicle_id' => $vehicle->id,
                ], [
                    'motor_type' => $motorType,
                    'inputs' => $inputs,
                    'scores' => $this->roundScores($scores),
                    'statuses' => $statuses,
                    'config_hash' => $configHash,
                    'scores_hash' => $scoresHash,
                    'prompt_hash' => $promptHash,
                    'model' => $res['model'] ?? null,
                    'sections' => $sections,
                    'generated_at' => now(),
                ]);
                $usedCache = false;
                $generatedAt = now();
            } catch (\Throwable $e) {
                Log::error('Gemini call failed: ' . $e->getMessage());
                $sections = $sections ?: $this->fallbackSections($scores, $statuses);
                $usedCache = (bool) $cache;
            }
        }

        return [
            'vehicle_id' => $vehicle->id,
            'motor_type' => $motorType,
            'inputs' => $inputs,
            'component_scores' => $this->roundScores($scores),
            'component_statuses' => $statuses,
            'thresholds' => $thresholds,
            'sections' => $sections,
            'used_cache' => $usedCache,
            'generated_at' => $generatedAt,
        ];
    }

    private function buildInputsFromVehicle(Vehicle $vehicle): array
    {
        $currentOdometer = (int) ($vehicle->odometer ?? 0);

        $lastService = $vehicle->serviceHistories()
            ->latest('performed_at')
            ->first();

        $distanceSinceServiceKm = 0.0;
        $durationSinceServiceDays = 0.0;

        if ($lastService && $lastService->odometer) {
            $distanceSinceServiceKm = (float) max(0, $currentOdometer - (int) $lastService->odometer);
        } else {
            $distanceSinceServiceKm = (float) max(0, $currentOdometer);
        }

        if ($lastService && $lastService->performed_at) {
            $durationSinceServiceDays = (float) max(0, $lastService->performed_at->diffInDays(now()));
        } elseif ($vehicle->created_at) {
            $durationSinceServiceDays = (float) max(0, $vehicle->created_at->diffInDays(now()));
        }

        $from = now()->subDays(30);
        $trips = $vehicle->trips()
            ->where('start_at', '>=', $from)
            ->get(['distance_meters', 'avg_speed_kph']);

        $totalDistanceKm = (float) ($trips->sum('distance_meters') / 1000);
        $intensityKmPerDay = $totalDistanceKm > 0 ? round($totalDistanceKm / 30, 1) : 0.0;

        $weightedSpeedSum = 0.0;
        $weightedDistanceSum = 0.0;
        foreach ($trips as $trip) {
            $dKm = ((float) $trip->distance_meters) / 1000;
            $s = (float) ($trip->avg_speed_kph ?? 0);
            if ($dKm > 0 && $s > 0) {
                $weightedSpeedSum += $s * $dKm;
                $weightedDistanceSum += $dKm;
            }
        }
        $avgSpeedKph = $weightedDistanceSum > 0
            ? round($weightedSpeedSum / $weightedDistanceSum, 1)
            : (float) ($vehicle->last_speed_kph ?? 0);

        return [
            'odometer' => $currentOdometer,
            'distance_since_service_km' => $distanceSinceServiceKm,
            'duration_since_service_days' => $durationSinceServiceDays,
            'avg_speed_kph' => $avgSpeedKph,
            'intensity_km_per_day' => $intensityKmPerDay,
        ];
    }

    private function shouldCallGemini(?AiRecommendationCache $cache, array $scores, array $statuses, string $configHash): bool
    {
        if (!$cache) {
            return true;
        }

        if (($cache->config_hash ?? '') !== $configHash) {
            return true;
        }

        $oldStatuses = $cache->statuses ?? [];
        foreach ($statuses as $k => $v) {
            if (($oldStatuses[$k] ?? null) !== $v) {
                return true;
            }
        }

        $deltaTrigger = (float) config('services.gemini.score_delta_trigger', 5);
        $oldScores = $cache->scores ?? [];
        foreach ($scores as $k => $v) {
            $old = (float) ($oldScores[$k] ?? 0);
            if (abs(((float) $v) - $old) >= $deltaTrigger) {
                return true;
            }
        }

        return false;
    }

    private function roundScores(array $scores): array
    {
        $out = [];
        foreach ($scores as $k => $v) {
            $out[$k] = round((float) $v, 1);
        }
        ksort($out);
        return $out;
    }

    public function buildGeminiPrompt(
        string $motorType,
        Vehicle $vehicle,
        array $inputs,
        array $scores,
        array $statuses,
        array $thresholds,
    ): string {
        $lines = [];
        $lines[] = 'Kamu adalah asisten perawatan motor.';
        $lines[] = '';
        $lines[] = 'Konteks motor:';
        $lines[] = '- Tipe motor: ' . $motorType;
        $lines[] = '- Odometer saat ini: ' . ((int) ($inputs['odometer'] ?? 0)) . ' km';
        $lines[] = '- Jarak sejak servis terakhir: ' . round((float) ($inputs['distance_since_service_km'] ?? 0), 1) . ' km';
        $lines[] = '- Durasi sejak servis terakhir: ' . round((float) ($inputs['duration_since_service_days'] ?? 0), 0) . ' hari';
        $lines[] = '- Kecepatan rata-rata (30 hari): ' . round((float) ($inputs['avg_speed_kph'] ?? 0), 1) . ' km/jam';
        $lines[] = '- Intensitas pakai (rata-rata 30 hari): ' . round((float) ($inputs['intensity_km_per_day'] ?? 0), 1) . ' km/hari';
        $lines[] = '';
        $lines[] = 'Skor kondisi per komponen (0-100, makin tinggi makin baik) + status:';

        $sortedKeys = array_keys($scores);
        sort($sortedKeys);
        foreach ($sortedKeys as $key) {
            $score = round((float) $scores[$key], 1);
            $status = $statuses[$key] ?? 'normal';
            $warn = Arr::get($thresholds, "$key.warn");
            $critical = Arr::get($thresholds, "$key.critical");

            $meta = ["status={$status}"];
            if (is_numeric($warn)) {
                $meta[] = 'warn<=' . (int) $warn;
            }
            if (is_numeric($critical)) {
                $meta[] = 'critical<=' . (int) $critical;
            }

            $lines[] = "- {$key}: {$score} (" . implode(', ', $meta) . ')';
        }

        $lines[] = '';
        $lines[] = 'Instruksi keluaran:';
        $lines[] = '1) Balas dalam JSON valid (tanpa markdown, tanpa backtick).';
        $lines[] = '2) Bahasa Indonesia ringkas, jelas, dan ramah.';
        $lines[] = '3) Jangan menyebut nama sistem internal atau model AI.';
        $lines[] = '4) Hindari klaim pasti; gunakan bahasa rekomendasi.';
        $lines[] = '5) Maksimal 3 kalimat per section.';
        $lines[] = '';
        $lines[] = 'Keluarkan struktur JSON berikut:';
        $lines[] = '{';
        $lines[] = '  "wawasan_pintar": "...",';
        $lines[] = '  "home_penggunaan_moderat": "...",';
        $lines[] = '  "home_rekomendasi": "...",';
        $lines[] = '  "service_ringkasan_pola": "...",';
        $lines[] = '  "rekomendasi_komponen": [';
        $lines[] = '    {"komponen": "...", "prioritas": "critical|warning|normal", "saran": "..."}';
        $lines[] = '  ]';
        $lines[] = '}';

        return implode("\n", $lines);
    }

    private function parseGeminiSections(?string $text): ?array
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }

        $json = json_decode($text, true);
        if (is_array($json)) {
            return $json;
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $slice = substr($text, $start, $end - $start + 1);
        $json = json_decode($slice, true);
        return is_array($json) ? $json : null;
    }

    private function fallbackSections(array $scores, array $statuses): array
    {
        $critical = array_keys(array_filter($statuses, fn ($s) => $s === 'critical'));
        $warning = array_keys(array_filter($statuses, fn ($s) => $s === 'warning'));

        $wawasan = 'Pantau kondisi komponen secara berkala untuk menjaga kenyamanan dan keamanan berkendara.';
        if (count($critical) > 0) {
            $wawasan = 'Ada komponen yang perlu segera dicek untuk menjaga keamanan berkendara.';
        } elseif (count($warning) > 0) {
            $wawasan = 'Beberapa komponen mendekati waktu perawatan; rencanakan pengecekan dalam waktu dekat.';
        }

        return [
            'wawasan_pintar' => $wawasan,
            'home_penggunaan_moderat' => 'Sesuaikan gaya berkendara dan lakukan pemeriksaan rutin agar kondisi motor tetap prima.',
            'home_rekomendasi' => 'Prioritaskan komponen berstatus darurat terlebih dahulu, lalu susul komponen yang mendekati waktu perawatan.',
            'service_ringkasan_pola' => 'Ringkasan pola penggunaan dihitung dari jarak tempuh, intensitas, dan kecepatan rata-rata beberapa waktu terakhir.',
            'rekomendasi_komponen' => array_values(array_map(function ($k) use ($statuses) {
                return [
                    'komponen' => $k,
                    'prioritas' => $statuses[$k] ?? 'normal',
                    'saran' => 'Lakukan inspeksi dan servis sesuai kebutuhan.',
                ];
            }, array_merge($critical, $warning))),
        ];
    }
}
