<?php

namespace Database\Seeders;

use App\Models\ComponentConfig;
use App\Models\FuzzyRule;
use App\Models\FuzzyVariable;
use App\Models\MotorType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RemoveAdventureTypeSeeder extends Seeder
{
    public function run(): void
    {
        $adventureType = MotorType::where('slug', 'adventure')->first();

        if ($adventureType) {
            // Get all component_config IDs for adventure
            $componentIds = ComponentConfig::where('motor_type_id', $adventureType->id)->pluck('id');

            // Delete fuzzy rules linked to adventure components
            $rulesDeleted = FuzzyRule::whereIn('component_config_id', $componentIds)->delete();
            $this->command->info("[+] Fuzzy rules dihapus: $rulesDeleted");

            // Delete fuzzy variables linked to adventure components
            $varsDeleted = FuzzyVariable::whereIn('component_config_id', $componentIds)->delete();
            $this->command->info("[+] Fuzzy variables dihapus: $varsDeleted");

            // Delete component configs for adventure
            $configsDeleted = ComponentConfig::where('motor_type_id', $adventureType->id)->delete();
            $this->command->info("[+] Component configs dihapus: $configsDeleted");

            // Delete the adventure motor type
            $adventureType->delete();
            $this->command->info('[+] Motor type "adventure" berhasil dihapus!');
        } else {
            $this->command->warn('[i] Motor type "adventure" tidak ditemukan di database - sudah bersih.');
        }

        // Reset any vehicles with tipe_motor = 'adventure' to 'matic'
        // Table may be named 'vehicles' (local) or 'motors' (VPS)
        $motorsUpdated = 0;
        foreach (['vehicles', 'motors'] as $table) {
            try {
                $motorsUpdated += DB::table($table)
                    ->where('tipe_motor', 'adventure')
                    ->update(['tipe_motor' => 'matic']);
            } catch (\Exception $e) {
                // Table doesn't exist in this env, skip
            }
        }

        if ($motorsUpdated > 0) {
            $this->command->info("[+] $motorsUpdated kendaraan dengan tipe 'adventure' direset ke 'matic'.");
        }

        // Clear AI recommendation cache
        DB::table('ai_recommendation_caches')->truncate();
        $this->command->info('[+] AI recommendation cache dibersihkan.');

        $this->command->info('');
        $this->command->info('✓ Database selesai dibersihkan! Hanya 3 tipe motor yang aktif: Matic, Manual/Bebek, Sport.');
    }
}
