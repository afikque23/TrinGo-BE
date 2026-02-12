<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\NotificationCategory;
use App\Models\NotificationTemplate;
use App\Models\User;

echo "=== Database Check ===\n\n";

echo "Users: " . User::count() . "\n";
echo "Notification Categories: " . NotificationCategory::count() . "\n";
echo "Notification Templates: " . NotificationTemplate::count() . "\n\n";

echo "=== Categories ===\n";
foreach (NotificationCategory::orderBy('id')->get() as $cat) {
    echo "- [{$cat->id}] {$cat->name} ({$cat->key})\n";
}

echo "\n=== Templates ===\n";
foreach (NotificationTemplate::orderBy('id')->get() as $tpl) {
    echo "- [{$tpl->id}] {$tpl->name}\n";
}

echo "\n✓ Database check completed!\n";
