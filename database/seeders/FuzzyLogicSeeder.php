<?php

namespace Database\Seeders;

use App\Models\ComponentConfig;
use App\Models\FuzzyRule;
use App\Models\FuzzyVariable;
use App\Models\MotorType;
use Illuminate\Database\Seeder;

class FuzzyLogicSeeder extends Seeder
{
    private array $defaultMF = [
        'jarak'      => ['low' => [0, 0, 800],    'medium' => [600, 1000, 1500], 'high' => [1200, 2000, 3000]],
        'durasi'     => ['low' => [0, 0, 30],     'medium' => [20, 45, 75],      'high' => [60, 90, 999]],
        'kecepatan'  => ['low' => [0, 0, 40],     'medium' => [30, 55, 80],      'high' => [70, 100, 150]],
        'intensitas' => ['low' => [0, 0, 15],     'medium' => [10, 25, 40],      'high' => [30, 50, 100]],
    ];

    private array $components = [
        'matic' => [
            ['name' => 'Oli Mesin',   'warn' => 1500,  'critical' => 2500,  'reset' => 90,  'vars' => ['jarak', 'durasi']],
            ['name' => 'Ban',         'warn' => 10000, 'critical' => 15000, 'reset' => 365, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Filter Udara','warn' => 8000,  'critical' => 12000, 'reset' => 180, 'vars' => ['jarak', 'intensitas']],
            ['name' => 'Busi',        'warn' => 6000,  'critical' => 10000, 'reset' => 180, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Aki',         'warn' => 365,   'critical' => 540,   'reset' => 730, 'vars' => ['durasi', 'intensitas']],
            ['name' => 'Rem',         'warn' => 10000, 'critical' => 15000, 'reset' => 365, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'CVT/Belt',    'warn' => 20000, 'critical' => 25000, 'reset' => 730, 'vars' => ['jarak', 'intensitas']],
            ['name' => 'Roller CVT',  'warn' => 15000, 'critical' => 20000, 'reset' => 540, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Oli Gardan',  'warn' => 8000,  'critical' => 12000, 'reset' => 365, 'vars' => ['jarak', 'durasi']],
        ],
        'manual' => [
            ['name' => 'Oli Mesin',      'warn' => 2000,  'critical' => 3000,  'reset' => 90,  'vars' => ['jarak', 'durasi']],
            ['name' => 'Ban',            'warn' => 10000, 'critical' => 15000, 'reset' => 365, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Filter Udara',   'warn' => 8000,  'critical' => 12000, 'reset' => 180, 'vars' => ['jarak', 'intensitas']],
            ['name' => 'Busi',           'warn' => 6000,  'critical' => 10000, 'reset' => 180, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Aki',            'warn' => 365,   'critical' => 540,   'reset' => 730, 'vars' => ['durasi', 'intensitas']],
            ['name' => 'Rem',            'warn' => 10000, 'critical' => 15000, 'reset' => 365, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Rantai',         'warn' => 12000, 'critical' => 18000, 'reset' => 365, 'vars' => ['jarak', 'intensitas']],
            ['name' => 'Kampas Kopling', 'warn' => 15000, 'critical' => 20000, 'reset' => 540, 'vars' => ['jarak', 'kecepatan']],
        ],
        'sport' => [
            ['name' => 'Oli Mesin',      'warn' => 3000,  'critical' => 5000,  'reset' => 90,  'vars' => ['jarak', 'durasi']],
            ['name' => 'Ban',            'warn' => 10000, 'critical' => 15000, 'reset' => 365, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Filter Udara',   'warn' => 10000, 'critical' => 15000, 'reset' => 180, 'vars' => ['jarak', 'intensitas']],
            ['name' => 'Busi',           'warn' => 8000,  'critical' => 12000, 'reset' => 180, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Aki',            'warn' => 365,   'critical' => 730,   'reset' => 730, 'vars' => ['durasi', 'intensitas']],
            ['name' => 'Rem',            'warn' => 10000, 'critical' => 15000, 'reset' => 365, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Rantai',         'warn' => 15000, 'critical' => 20000, 'reset' => 365, 'vars' => ['jarak', 'intensitas']],
            ['name' => 'Kopling',        'warn' => 18000, 'critical' => 25000, 'reset' => 540, 'vars' => ['jarak', 'kecepatan']],
        ],
        'adventure' => [
            ['name' => 'Oli Mesin',      'warn' => 3000,  'critical' => 5000,  'reset' => 90,  'vars' => ['jarak', 'durasi']],
            ['name' => 'Ban',            'warn' => 10000, 'critical' => 15000, 'reset' => 365, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Filter Udara',   'warn' => 8000,  'critical' => 12000, 'reset' => 180, 'vars' => ['jarak', 'intensitas']],
            ['name' => 'Busi',           'warn' => 8000,  'critical' => 12000, 'reset' => 180, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Aki',            'warn' => 365,   'critical' => 730,   'reset' => 730, 'vars' => ['durasi', 'intensitas']],
            ['name' => 'Rem',            'warn' => 10000, 'critical' => 15000, 'reset' => 365, 'vars' => ['jarak', 'kecepatan']],
            ['name' => 'Rantai',         'warn' => 18000, 'critical' => 25000, 'reset' => 365, 'vars' => ['jarak', 'intensitas']],
            ['name' => 'Kopling',        'warn' => 20000, 'critical' => 28000, 'reset' => 540, 'vars' => ['jarak', 'kecepatan']],
        ],
    ];

    public function run(): void
    {
        $types = [];
        foreach (['matic' => 'Matic', 'manual' => 'Manual/Bebek', 'sport' => 'Sport', 'adventure' => 'Adventure'] as $slug => $name) {
            $types[$slug] = MotorType::firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]);
        }

        foreach ($this->components as $typeSlug => $comps) {
            $motorType = $types[$typeSlug];

            foreach ($comps as $comp) {
                $component = ComponentConfig::firstOrCreate(
                    ['motor_type_id' => $motorType->id, 'name' => $comp['name']],
                    [
                        'warn'           => $comp['warn'],
                        'critical'       => $comp['critical'],
                        'reset_interval' => $comp['reset'],
                        'active_vars'    => $comp['vars'],
                        'is_active'      => true,
                        'is_custom'      => false,
                    ]
                );

                foreach ($this->defaultMF as $varKey => $mf) {
                    FuzzyVariable::firstOrCreate(
                        ['component_config_id' => $component->id, 'var_key' => $varKey],
                        [
                            'low_a'  => $mf['low'][0],    'low_b'  => $mf['low'][1],    'low_c'  => $mf['low'][2],
                            'med_a'  => $mf['medium'][0], 'med_b'  => $mf['medium'][1], 'med_c'  => $mf['medium'][2],
                            'high_a' => $mf['high'][0],   'high_b' => $mf['high'][1],   'high_c' => $mf['high'][2],
                        ]
                    );
                }

                if ($component->fuzzyRules()->count() === 0) {
                    $var1 = $comp['vars'][0];
                    $var2 = $comp['vars'][1] ?? $comp['vars'][0];

                    $defaultRules = [
                        ['var1' => $var1, 'label1' => 'high',   'op' => 'AND', 'var2' => $var2, 'label2' => 'high',   'output' => 'Kritis',       'weight' => 1.0],
                        ['var1' => $var1, 'label1' => 'high',   'op' => 'AND', 'var2' => $var2, 'label2' => 'medium', 'output' => 'Kritis',       'weight' => 0.8],
                        ['var1' => $var1, 'label1' => 'medium', 'op' => 'AND', 'var2' => $var2, 'label2' => 'high',   'output' => 'Perlu Servis', 'weight' => 0.9],
                        ['var1' => $var1, 'label1' => 'medium', 'op' => 'AND', 'var2' => $var2, 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                        ['var1' => $var1, 'label1' => 'low',    'op' => 'AND', 'var2' => $var2, 'label2' => 'low',    'output' => 'Baik',         'weight' => 1.0],
                        ['var1' => $var1, 'label1' => 'low',    'op' => 'AND', 'var2' => $var2, 'label2' => 'medium', 'output' => 'Baik',         'weight' => 0.8],
                    ];

                    foreach ($defaultRules as $r) {
                        FuzzyRule::create([
                            'component_config_id' => $component->id,
                            'var1' => $r['var1'],
                            'label1' => $r['label1'],
                            'operator' => $r['op'],
                            'var2' => $r['var2'],
                            'label2' => $r['label2'],
                            'output' => $r['output'],
                            'weight' => $r['weight'],
                        ]);
                    }
                }
            }
        }

        $totalComponents = array_sum(array_map('count', $this->components));
        $this->command->info("✓ Fuzzy Logic seeded: 4 motor types, {$totalComponents} components");
    }
}
