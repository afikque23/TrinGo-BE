<?php

namespace App\Services\Fuzzy;

use App\Models\MotorType;
use App\Services\FuzzyEngine as NormalizedFuzzyEngine;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FuzzyEngineV2
{
    public function __construct(
        private readonly NormalizedFuzzyEngine $engine,
    ) {
    }

    private function componentKeyFromName(?string $name, int $fallbackId): string
    {
        $name = trim((string) $name);

        $map = [
            'Oli Mesin' => 'engine_oil',
            'Ban' => 'tires',
            'Filter Udara' => 'air_filter',
            'Busi' => 'spark_plug',
            'Aki' => 'battery',
            'Rem' => 'brake',
            'CVT/Belt' => 'cvt_belt',
            'CVT / Belt' => 'cvt_belt',
            'Roller CVT' => 'cvt_roller',
            'Oli Gardan' => 'final_drive_oil',
            'Rantai' => 'chain',
            'Kopling' => 'clutch',
            'Kampas Kopling' => 'clutch',
        ];

        if ($name !== '' && array_key_exists($name, $map)) {
            return $map[$name];
        }

        $slug = Str::slug($name, '_');
        return $slug !== '' ? $slug : ('component_' . $fallbackId);
    }

    public function supportsMotorType(string $motorTypeSlug): bool
    {
        $motorTypeSlug = strtolower(trim($motorTypeSlug));
        if ($motorTypeSlug === '') {
            return false;
        }

        if (!Schema::hasTable('motor_types') || !Schema::hasTable('component_configs')) {
            return false;
        }

        return MotorType::query()->where('slug', $motorTypeSlug)->exists();
    }

    /**
     * Compute condition scores (0..100) per component for a given motor type.
     *
     * Output is aligned to the existing API contract: higher score means better condition.
     *
     * @param string $motorTypeSlug
     * @param array $inputs distance_since_service_km, duration_since_service_days, avg_speed_kph, intensity_km_per_day
     * @return array{scores: array<string, float>, statuses: array<string, string>, thresholds: array<string, array{warn:int,critical:int}>}
     */
    public function evaluateMotorType(string $motorTypeSlug, array $inputs): array
    {
        $motorTypeSlug = strtolower(trim($motorTypeSlug));

        if (!$this->supportsMotorType($motorTypeSlug)) {
            return [
                'scores' => [],
                'statuses' => [],
                'thresholds' => [],
            ];
        }

        $motorType = MotorType::query()
            ->where('slug', $motorTypeSlug)
            ->with([
                'componentConfigs' => function ($q) {
                    $q->where('is_active', true)
                        ->with(['fuzzyVariables', 'fuzzyRules']);
                },
            ])
            ->first();

        $scores = [];
        $statuses = [];

        $normalizedInputs = [
            'jarak' => (float) ($inputs['distance_since_service_km'] ?? 0),
            'durasi' => (float) ($inputs['duration_since_service_days'] ?? 0),
            'kecepatan' => (float) ($inputs['avg_speed_kph'] ?? 0),
            'intensitas' => (float) ($inputs['intensity_km_per_day'] ?? 0),
        ];

        foreach (($motorType?->componentConfigs ?? []) as $componentConfig) {
            $componentKey = $this->componentKeyFromName($componentConfig->name, (int) $componentConfig->id);

            $res = $this->engine->calculate($componentConfig, $normalizedInputs);

            // NormalizedFuzzyEngine returns urgency score: higher = more urgent.
            // Convert to API health score: higher = better.
            $urgencyScore = (float) ($res['score'] ?? 0);
            $healthScore = max(0.0, min(100.0, 100.0 - $urgencyScore));

            $label = (string) ($res['label'] ?? '');
            $status = match ($label) {
                'Kritis' => 'critical',
                'Perlu Servis' => 'warning',
                default => 'normal',
            };

            // Ensure uniqueness if there are duplicate names.
            if (array_key_exists($componentKey, $scores)) {
                $componentKey = $componentKey . '_' . $componentConfig->id;
            }

            $scores[$componentKey] = round($healthScore, 1);
            $statuses[$componentKey] = $status;
        }

        ksort($scores);
        ksort($statuses);

        return [
            'scores' => $scores,
            'statuses' => $statuses,
            // v2 thresholds are not score-based (they're operational limits), so omit from the API payload.
            'thresholds' => [],
        ];
    }

    /**
     * Hash of all v2 configs for the motor type (for trigger-based caching).
     */
    public function configHashForMotorType(string $motorTypeSlug): string
    {
        $motorTypeSlug = strtolower(trim($motorTypeSlug));

        if (!$this->supportsMotorType($motorTypeSlug)) {
            return hash('sha256', '');
        }

        $motorType = MotorType::query()
            ->where('slug', $motorTypeSlug)
            ->with([
                'componentConfigs' => function ($q) {
                    $q->with(['fuzzyVariables', 'fuzzyRules']);
                },
            ])
            ->first();

        $fingerprint = [
            'motor_type' => $motorTypeSlug,
            'components' => [],
        ];

        foreach (($motorType?->componentConfigs ?? []) as $componentConfig) {
            $fingerprint['components'][] = [
                'name' => (string) $componentConfig->name,
                'is_active' => (bool) $componentConfig->is_active,
                'active_vars' => $componentConfig->active_vars ?? [],
                'warn' => $componentConfig->warn,
                'critical' => $componentConfig->critical,
                'updated_at' => $componentConfig->updated_at?->toISOString(),
                'variables' => $componentConfig->fuzzyVariables
                    ->map(fn ($v) => [
                        'var_key' => $v->var_key,
                        'low_a' => $v->low_a,
                        'low_b' => $v->low_b,
                        'low_c' => $v->low_c,
                        'med_a' => $v->med_a,
                        'med_b' => $v->med_b,
                        'med_c' => $v->med_c,
                        'high_a' => $v->high_a,
                        'high_b' => $v->high_b,
                        'high_c' => $v->high_c,
                        'updated_at' => $v->updated_at?->toISOString(),
                    ])
                    ->values()
                    ->all(),
                'rules' => $componentConfig->fuzzyRules
                    ->map(fn ($r) => [
                        'var1' => $r->var1,
                        'label1' => $r->label1,
                        'operator' => $r->operator,
                        'var2' => $r->var2,
                        'label2' => $r->label2,
                        'output' => $r->output,
                        'weight' => $r->weight,
                        'updated_at' => $r->updated_at?->toISOString(),
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return hash('sha256', json_encode($fingerprint, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
