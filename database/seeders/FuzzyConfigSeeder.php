<?php

namespace Database\Seeders;

use App\Models\FuzzyComponentConfig;
use App\Models\MaintenanceComponent;
use App\Models\MotorTypeComponent;
use App\Services\Fuzzy\DefaultFuzzyConfig;
use Illuminate\Database\Seeder;

class FuzzyConfigSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            ['key' => 'engine_oil', 'name' => 'Oli Mesin'],
            ['key' => 'tires', 'name' => 'Ban'],
            ['key' => 'air_filter', 'name' => 'Filter Udara'],
            ['key' => 'spark_plug', 'name' => 'Busi'],
            ['key' => 'battery', 'name' => 'Aki'],
            ['key' => 'brake', 'name' => 'Rem'],
            ['key' => 'cvt_belt', 'name' => 'CVT / Belt'],
            ['key' => 'cvt_roller', 'name' => 'Roller CVT'],
            ['key' => 'final_drive_oil', 'name' => 'Oli Gardan'],
            ['key' => 'chain', 'name' => 'Rantai'],
            ['key' => 'clutch', 'name' => 'Kopling'],
        ];

        foreach ($components as $component) {
            MaintenanceComponent::firstOrCreate(
                ['key' => $component['key']],
                ['name' => $component['name'], 'is_active' => true]
            );
        }

        $typeToComponents = [
            'matic' => ['engine_oil', 'tires', 'air_filter', 'spark_plug', 'battery', 'brake', 'cvt_belt', 'cvt_roller', 'final_drive_oil'],
            'manual' => ['engine_oil', 'tires', 'air_filter', 'spark_plug', 'battery', 'brake', 'chain'],
            'sport' => ['engine_oil', 'tires', 'air_filter', 'spark_plug', 'battery', 'brake', 'chain', 'clutch'],
            'adventure' => ['engine_oil', 'tires', 'air_filter', 'spark_plug', 'battery', 'brake', 'chain', 'clutch'],
        ];

        $defaultFuzzyConfig = DefaultFuzzyConfig::make();

        foreach ($typeToComponents as $motorType => $componentKeys) {
            foreach ($componentKeys as $componentKey) {
                $component = MaintenanceComponent::where('key', $componentKey)->first();
                if (!$component) {
                    continue;
                }

                $pivot = MotorTypeComponent::firstOrCreate([
                    'motor_type' => $motorType,
                    'maintenance_component_id' => $component->id,
                ], [
                    'is_active' => true,
                ]);

                FuzzyComponentConfig::firstOrCreate([
                    'motor_type_component_id' => $pivot->id,
                ], [
                    'warn_score' => 60,
                    'critical_score' => 40,
                    'config' => $defaultFuzzyConfig,
                    'version' => 1,
                ]);
            }
        }

        $this->command->info('Fuzzy config seeded successfully!');
    }
}
