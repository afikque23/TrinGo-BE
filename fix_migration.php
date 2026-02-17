<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Mark duplicate migration as ran
DB::table('migrations')->insert([
    'migration' => '2026_02_09_095142_create_service_schedules_table',
    'batch' => 2
]);

echo "✅ Migration duplikat berhasil ditandai sebagai sudah dijalankan\n";
