<?php

namespace App\Services\Fuzzy;

use App\Models\MotorTypeComponent;

class FuzzyEngine
{
    /**
     * Compute condition scores (0..100) per component for a given motor type.
     *
     * @param string $motorType
     * @param array $inputs distance_since_service_km, duration_since_service_days, avg_speed_kph, intensity_km_per_day
     * @return array{scores: array<string, float>, statuses: array<string, string>, thresholds: array<string, array{warn:int,critical:int}>}
     */
    public function evaluateMotorType(string $motorType, array $inputs): array
    {
        $motorType = strtolower($motorType);

        $components = $this->getMotorTypeComponents($motorType);

        $scores = [];
        $statuses = [];
        $thresholds = [];

        foreach ($components as $row) {
            $componentKey = $row['component_key'];
            $warnScore = (int) $row['warn_score'];
            $criticalScore = (int) $row['critical_score'];
            $config = $row['config'];

            $score = $this->computeMamdaniScore($inputs, $config);
            $scores[$componentKey] = $score;
            $thresholds[$componentKey] = [
                'warn' => $warnScore,
                'critical' => $criticalScore,
            ];
            $statuses[$componentKey] = $this->statusFromScore($score, $warnScore, $criticalScore);
        }

        return [
            'scores' => $scores,
            'statuses' => $statuses,
            'thresholds' => $thresholds,
        ];
    }

    /**
     * Hash of all configs for the motor type (for trigger-based caching).
     */
    public function configHashForMotorType(string $motorType): string
    {
        $rows = $this->getMotorTypeComponents(strtolower($motorType));
        $fingerprint = [];

        foreach ($rows as $row) {
            $fingerprint[] = [
                'component_key' => $row['component_key'],
                'warn_score' => (int) $row['warn_score'],
                'critical_score' => (int) $row['critical_score'],
                'config' => $row['config'],
                'version' => (int) ($row['version'] ?? 1),
                'updated_at' => $row['updated_at'] ?? null,
            ];
        }

        return hash('sha256', json_encode($fingerprint, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Evaluate a single config (used by admin test UI).
     */
    public function evaluateConfigScore(array $inputs, array $config): float
    {
        return $this->computeMamdaniScore($inputs, $config);
    }

    /**
     * Public wrapper for status mapping (used by admin test UI).
     */
    public function statusFromScorePublic(float $score, int $warnScore, int $criticalScore): string
    {
        return $this->statusFromScore($score, $warnScore, $criticalScore);
    }

    private function getMotorTypeComponents(string $motorType): array
    {
        return MotorTypeComponent::query()
            ->where('motor_type', $motorType)
            ->where('is_active', true)
            ->whereHas('component', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['component', 'fuzzyConfig'])
            ->get()
            ->map(function (MotorTypeComponent $mtc) {
                $cfg = $mtc->fuzzyConfig;
                return [
                    'component_key' => $mtc->component?->key,
                    'warn_score' => $cfg?->warn_score ?? 60,
                    'critical_score' => $cfg?->critical_score ?? 40,
                    'config' => $cfg?->config ?? [],
                    'version' => $cfg?->version ?? 1,
                    'updated_at' => $cfg?->updated_at?->toISOString(),
                ];
            })
            ->filter(fn ($row) => !empty($row['component_key']))
            ->values()
            ->all();
    }

    private function statusFromScore(float $score, int $warnScore, int $criticalScore): string
    {
        if ($score <= $criticalScore) {
            return 'critical';
        }
        if ($score <= $warnScore) {
            return 'warning';
        }
        return 'normal';
    }

    /**
     * Mamdani inference + centroid defuzzification.
     */
    private function computeMamdaniScore(array $inputs, array $config): float
    {
        $inputDefs = $config['inputs'] ?? [];
        $outputDef = $config['output'] ?? [];
        $rules = $config['rules'] ?? [];

        if (empty($inputDefs) || empty($outputDef) || empty($outputDef['sets']) || empty($rules)) {
            return 50.0;
        }

        $fuzzyInputs = [];
        foreach ($inputDefs as $var => $def) {
            if (is_array($def) && array_key_exists('enabled', $def) && !$def['enabled']) {
                continue;
            }
            $x = (float) ($inputs[$var] ?? 0);
            $sets = $def['sets'] ?? [];
            $fuzzyInputs[$var] = [];
            foreach ($sets as $setName => $setDef) {
                $fuzzyInputs[$var][$setName] = $this->membership($x, $setDef);
            }
        }

        $outputActivations = [];
        foreach ($rules as $rule) {
            $thenSet = $rule['then'] ?? null;
            if (!$thenSet) {
                continue;
            }

            $degree = 0.0;
            if (isset($rule['all']) && is_array($rule['all'])) {
                $degree = 1.0;
                foreach ($rule['all'] as $cond) {
                    $degree = min($degree, $this->conditionDegree($fuzzyInputs, $cond));
                }
            } elseif (isset($rule['any']) && is_array($rule['any'])) {
                $degree = 0.0;
                foreach ($rule['any'] as $cond) {
                    $degree = max($degree, $this->conditionDegree($fuzzyInputs, $cond));
                }
            } else {
                continue;
            }

            $weight = 1.0;
            if (array_key_exists('weight', $rule)) {
                $weight = (float) ($rule['weight'] ?? 1.0);
                $weight = max(0.0, min(1.0, $weight));
            }
            $degree *= $weight;

            $outputActivations[$thenSet] = max($outputActivations[$thenSet] ?? 0.0, $degree);
        }

        $universe = $outputDef['universe'] ?? [0, 100];
        $minX = (float) ($universe[0] ?? 0);
        $maxX = (float) ($universe[1] ?? 100);
        $outputSets = $outputDef['sets'] ?? [];

        $step = 1.0;
        $numerator = 0.0;
        $denominator = 0.0;

        for ($x = $minX; $x <= $maxX; $x += $step) {
            $mu = 0.0;
            foreach ($outputActivations as $setName => $activation) {
                $setDef = $outputSets[$setName] ?? null;
                if (!$setDef) {
                    continue;
                }
                $mu = max($mu, min($activation, $this->membership($x, $setDef)));
            }
            $numerator += $x * $mu;
            $denominator += $mu;
        }

        if ($denominator <= 0.0) {
            return 50.0;
        }

        $score = $numerator / $denominator;
        $score = max($minX, min($maxX, $score));
        return round($score, 1);
    }

    private function conditionDegree(array $fuzzyInputs, array $cond): float
    {
        $var = $cond['var'] ?? null;
        $setName = $cond['is'] ?? null;
        if (!$var || !$setName) {
            return 0.0;
        }
        return (float) ($fuzzyInputs[$var][$setName] ?? 0.0);
    }

    private function membership(float $x, array $setDef): float
    {
        $type = $setDef['type'] ?? null;
        $params = $setDef['params'] ?? [];

        return match ($type) {
            'tri' => $this->triangular($x, (float) ($params[0] ?? 0), (float) ($params[1] ?? 0), (float) ($params[2] ?? 0)),
            'trap' => $this->trapezoidal($x, (float) ($params[0] ?? 0), (float) ($params[1] ?? 0), (float) ($params[2] ?? 0), (float) ($params[3] ?? 0)),
            default => 0.0,
        };
    }

    private function triangular(float $x, float $a, float $b, float $c): float
    {
        if ($a == $b && $b == $c) {
            return 0.0;
        }
        if ($x == $b) {
            return 1.0;
        }

        // Left shoulder (a == b): full membership until b, then decreasing to 0 at c.
        if ($a == $b) {
            if ($x <= $b) {
                return 1.0;
            }
            if ($x >= $c) {
                return 0.0;
            }
            return ($c == $b) ? 0.0 : (($c - $x) / ($c - $b));
        }

        // Right shoulder (b == c): increasing from 0 at a to full membership from b.
        if ($b == $c) {
            if ($x >= $b) {
                return 1.0;
            }
            if ($x <= $a) {
                return 0.0;
            }
            return ($b == $a) ? 0.0 : (($x - $a) / ($b - $a));
        }

        if ($x <= $a || $x >= $c) {
            return 0.0;
        }
        if ($x < $b) {
            return ($b == $a) ? 0.0 : (($x - $a) / ($b - $a));
        }
        return ($c == $b) ? 0.0 : (($c - $x) / ($c - $b));
    }

    private function trapezoidal(float $x, float $a, float $b, float $c, float $d): float
    {
        if ($x <= $a || $x >= $d) {
            return 0.0;
        }
        if ($x >= $b && $x <= $c) {
            return 1.0;
        }
        if ($x > $a && $x < $b) {
            return ($b == $a) ? 0.0 : (($x - $a) / ($b - $a));
        }
        return ($d == $c) ? 0.0 : (($d - $x) / ($d - $c));
    }
}
