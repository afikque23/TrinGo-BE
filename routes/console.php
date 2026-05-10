<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use Database\Seeders\FuzzyLogicSeeder;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('fuzzy:reset {--include-legacy : Also clear legacy fuzzy-config tables}', function () {
    $this->info('Resetting fuzzy configuration...');

    $newTables = [
        'fuzzy_audit_logs',
        'fuzzy_rules',
        'fuzzy_variables',
        'component_configs',
        'motor_types',
    ];

    $legacyTables = [
        'fuzzy_config_audits',
        'fuzzy_component_configs',
        'motor_type_components',
        'ai_recommendation_caches',
    ];

    // NOTE: TRUNCATE melakukan implicit commit di MySQL, jadi jangan dibungkus transaction.
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    try {
        foreach ($newTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        if ($this->option('include-legacy')) {
            foreach ($legacyTables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        }
    } finally {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    $this->call('db:seed', [
        '--class' => FuzzyLogicSeeder::class,
        '--force' => true,
    ]);

    $this->info('✓ Fuzzy configuration reset + seeded.');
})->purpose('Clear fuzzy config tables and seed defaults');
