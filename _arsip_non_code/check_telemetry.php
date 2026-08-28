<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
foreach($tables as $t){
    $tb = array_values((array)$t)[0];
    if (in_array($tb, ['trip_points', 'telemetry', 'telemetries', 'trips'])) {
        echo "Table: $tb\n";
        $cols = DB::select("SHOW COLUMNS FROM $tb");
        foreach($cols as $c) {
            echo "  - " . $c->Field . "\n";
        }
        
        // If trip_points, try checking data
        if ($tb == 'trip_points' || $tb == 'telemetries') {
            $data = DB::table($tb)->orderBy('id', 'desc')->take(5)->get();
            echo "  Recent data count: " . count($data) . "\n";
            if (count($data) > 0) {
                print_r((array)$data[0]);
            }
        }
    }
}
