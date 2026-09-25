<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$configs = \App\Models\ComponentConfig::get(['name', 'reset_interval', 'warn', 'critical'])->toArray();
echo json_encode($configs, JSON_PRETTY_PRINT);
