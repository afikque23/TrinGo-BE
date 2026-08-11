<?php
 
namespace App\Services;

use App\Models\ComponentConfig;

class FuzzyEngine
{
    /**
     * Hitung derajat keanggotaan (trimf = triangular membership function)
     */
    public function trimf(float $x, float $a, float $b, float $c): float
    {
        if ($a === $b && $x <= $a) return 1.0;
        if ($x <= $a || $x >= $c) return 0.0;
        if ($x <= $b) return ($b - $a) > 0 ? ($x - $a) / ($b - $a) : 1.0;
        return ($c - $b) > 0 ? ($c - $x) / ($c - $b) : 1.0;
    }

    /**
     * Hitung skor Fuzzy untuk satu komponen
     *
     * @param ComponentConfig $component
     * @param array $inputs ['jarak' => 820, 'durasi' => 45, 'kecepatan' => 41.5, 'intensitas' => 11.1]
     * @return array ['score' => 74.3, 'label' => 'Perlu Servis', 'membership' => [...]]
     */
    public function calculate(ComponentConfig $component, array $inputs): array
    {
        $activeVars = $component->active_vars ?? [];
        $variables  = $component->fuzzyVariables->keyBy('var_key');
        $rules      = $component->fuzzyRules;

        // Step 1: Fuzzifikasi — hitung derajat keanggotaan tiap variabel aktif
        $membership = [];
        foreach ($activeVars as $varKey) {
            $val = $inputs[$varKey] ?? 0;
            $mf  = $variables->get($varKey);

            if (!$mf) continue;

            $membership[$varKey] = [
                'low'    => round($this->trimf($val, $mf->low_a,  $mf->low_b,  $mf->low_c),  3),
                'medium' => round($this->trimf($val, $mf->med_a,  $mf->med_b,  $mf->med_c),  3),
                'high'   => round($this->trimf($val, $mf->high_a, $mf->high_b, $mf->high_c), 3),
            ];
        }

        // Step 2: Evaluasi rule base
        $scores = ['Baik' => 0.0, 'Perlu Servis' => 0.0, 'Kritis' => 0.0];

        foreach ($rules as $rule) {
            $m1 = $membership[$rule->var1][$rule->label1] ?? 0;
            $m2 = $membership[$rule->var2][$rule->label2] ?? 0;

            $strength = $rule->operator === 'AND'
                ? min($m1, $m2)
                : max($m1, $m2);

            $fired = $strength * $rule->weight;
            $scores[$rule->output] = max($scores[$rule->output], $fired);
        }

        $hasAnyRuleFired = max($scores) > 0;

        // Defensive fallback: if rules never fire, derive urgency from active variable thresholds.
        // This avoids a misleading "Baik" label when admin config has active_vars that are not covered by rules.
        if (!$hasAnyRuleFired) {
            $fallbackLabel = 'Baik';

            foreach ($activeVars as $varKey) {
                $mf = $variables->get($varKey);
                if (!$mf) {
                    continue;
                }

                $val = (float) ($inputs[$varKey] ?? 0);
                $warnThreshold = (float) ($mf->med_b ?? 0);
                $criticalThreshold = (float) ($mf->high_b ?? 0);

                if ($criticalThreshold > 0 && $val >= $criticalThreshold) {
                    $fallbackLabel = 'Kritis';
                    break;
                }

                if ($warnThreshold > 0 && $val >= $warnThreshold) {
                    $fallbackLabel = 'Perlu Servis';
                }
            }

            $scores = match ($fallbackLabel) {
                'Kritis' => ['Baik' => 0.0, 'Perlu Servis' => 0.0, 'Kritis' => 1.0],
                'Perlu Servis' => ['Baik' => 0.0, 'Perlu Servis' => 1.0, 'Kritis' => 0.0],
                default => ['Baik' => 1.0, 'Perlu Servis' => 0.0, 'Kritis' => 0.0],
            };
        }

        // Step 3: Defuzzifikasi (centroid sederhana)
        $outputMap = ['Baik' => 20.0, 'Perlu Servis' => 60.0, 'Kritis' => 90.0];
        $totalWeight = array_sum($scores) ?: 1.0;

        $score = 0.0;
        foreach ($scores as $output => $weight) {
            $score += $outputMap[$output] * $weight;
        }
        $score = round($score / $totalWeight, 1);

        // Step 4: Tentukan label akhir
        $label = match(true) {
            $score >= 75 => 'Kritis',
            $score >= 40 => 'Perlu Servis',
            default      => 'Baik',
        };

        return [
            'score'      => $score,
            'label'      => $label,
            'membership' => $membership,
            'scores'     => $scores,
        ];
    }

    /**
     * Generate data grafik membership function untuk preview
     */
    public function generateChartData(array $mf, int $maxX = 3000, int $points = 80): array
    {
        $data = [];
        for ($i = 0; $i < $points; $i++) {
            $x = ($maxX / ($points - 1)) * $i;
            $data[] = [
                'x'      => round($x),
                'low'    => round($this->trimf($x, $mf['low'][0],  $mf['low'][1],  $mf['low'][2]),  3),
                'medium' => round($this->trimf($x, $mf['medium'][0], $mf['medium'][1], $mf['medium'][2]), 3),
                'high'   => round($this->trimf($x, $mf['high'][0], $mf['high'][1], $mf['high'][2]), 3),
            ];
        }
        return $data;
    }
}
