<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== All Notification Templates ===\n\n";

$templates = \App\Models\NotificationTemplate::all();

if ($templates->isEmpty()) {
    echo "❌ No templates found!\n";
    exit;
}

foreach ($templates as $t) {
    echo "ID: {$t->id}\n";
    echo "  Name: {$t->name}\n";
    echo "  Category: {$t->category_key}\n";
    echo "  Channel: {$t->channel}\n";
    echo "  Trigger: {$t->trigger_type}\n";
    echo "  Active: " . ($t->is_active ? 'YES ✅' : 'NO ❌') . "\n";
    echo "  Message: " . substr($t->message_template, 0, 80) . "...\n\n";
}
