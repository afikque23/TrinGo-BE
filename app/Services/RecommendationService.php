<?php

namespace App\Services;

use App\Models\AiRecommendationCache;
use App\Models\Vehicle;
use App\Services\Fuzzy\FuzzyEngine;
use App\Services\Fuzzy\FuzzyEngineV2;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    private const CACHE_MAX_AGE_DAYS = 3;

    public function __construct(
        private readonly FuzzyEngine $fuzzyEngine,
        private readonly FuzzyEngineV2 $fuzzyEngineV2,
        private readonly GeminiService $gemini,
        private readonly GeminiPromptBuilder $promptBuilder,
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

        if ($useV2 && empty($fuzzy['scores'] ?? [])) {
            $useV2 = false;
            $fuzzy = $this->fuzzyEngine->evaluateMotorType($motorType, $inputs);
        }
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
            $prompt = $this->promptBuilder->build(
                motorType: $motorType,
                inputs: $inputs,
                scores: $scores,
                statuses: $statuses,
                thresholds: $thresholds,
            );
            $promptHash = hash('sha256', $prompt);

            try {
                $result = $this->gemini->generateRecommendation(
                    motorType: $motorType,
                    inputs: $inputs,
                    scores: $scores,
                    statuses: $statuses,
                    thresholds: $thresholds,
                );

                if (is_array($result)) {
                    $sections = $result;

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
                        'model' => config('services.gemini.model'),
                        'sections' => $sections,
                        'generated_at' => now(),
                    ]);

                    $usedCache = false;
                    $generatedAt = now();
                } else {
                    // Gemini failed: prefer existing cache; otherwise fallback.
                    if (!$sections) {
                        $sections = $this->gemini->generateFallback(
                            $scores,
                            $statuses,
                            (float) ($inputs['intensity_km_per_day'] ?? 0),
                        );
                        $usedCache = false;
                        $generatedAt = $generatedAt ?? now();
                    } else {
                        $usedCache = true;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Gemini call failed: ' . $e->getMessage());
                if (!$sections) {
                    $sections = $this->gemini->generateFallback(
                        $scores,
                        $statuses,
                        (float) ($inputs['intensity_km_per_day'] ?? 0),
                    );
                    $usedCache = false;
                    $generatedAt = $generatedAt ?? now();
                } else {
                    $usedCache = true;
                }
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

    /**
     * Compute fuzzy scores/statuses without calling Gemini.
     * Intended for fast UI refresh endpoints.
     */
    public function getVehicleFuzzySnapshot(Vehicle $vehicle): array
    {
        $inputs = $this->buildInputsFromVehicle($vehicle);

        $motorType = strtolower($vehicle->tipe_motor ?? '');
        $useV2 = $this->fuzzyEngineV2->supportsMotorType($motorType);
        $fuzzy = $useV2
            ? $this->fuzzyEngineV2->evaluateMotorType($motorType, $inputs)
            : $this->fuzzyEngine->evaluateMotorType($motorType, $inputs);

        if ($useV2 && empty($fuzzy['scores'] ?? [])) {
            $useV2 = false;
            $fuzzy = $this->fuzzyEngine->evaluateMotorType($motorType, $inputs);
        }

        return [
            'vehicle_id' => $vehicle->id,
            'motor_type' => $motorType,
            'inputs' => $inputs,
            'component_scores' => $this->roundScores($fuzzy['scores'] ?? []),
            'component_statuses' => $fuzzy['statuses'] ?? [],
            'thresholds' => $fuzzy['thresholds'] ?? [],
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
            ->get(['distance_meters', 'avg_speed_kph', 'elevation_gain', 'ambient_temp_avg']);

        $totalDistanceKm = (float) ($trips->sum('distance_meters') / 1000);
        $intensityKmPerDay = $totalDistanceKm > 0 ? round($totalDistanceKm / 30, 1) : 0.0;
        
        $elevationGainM = (float) $trips->sum('elevation_gain');

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
            'elevation_gain_m' => $elevationGainM,
            'ambient_temp_c'   => round(
                $trips->whereNotNull('ambient_temp_avg')->avg('ambient_temp_avg') ?? 28.0,
                1
            ),
        ];
    }

    private function shouldCallGemini(?AiRecommendationCache $cache, array $scores, array $statuses, string $configHash): bool
    {
        if (!$cache) {
            return true;
        }

        if ($cache->generated_at && $cache->generated_at->lt(now()->subDays(self::CACHE_MAX_AGE_DAYS))) {
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

}
