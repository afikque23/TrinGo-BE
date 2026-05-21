<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'last_baro_ok')) {
                $table->boolean('last_baro_ok')->nullable()->after('last_hdop');
            }
            if (!Schema::hasColumn('vehicles', 'last_baro_rel_alt_m')) {
                $table->float('last_baro_rel_alt_m')->nullable()->after('last_baro_ok');
            }
            if (!Schema::hasColumn('vehicles', 'last_grade_ratio')) {
                $table->float('last_grade_ratio')->nullable()->after('last_baro_rel_alt_m');
            }
            if (!Schema::hasColumn('vehicles', 'last_grade_pct')) {
                $table->float('last_grade_pct')->nullable()->after('last_grade_ratio');
            }
        });

        Schema::table('trip_points', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_points', 'baro_rel_alt_m')) {
                $table->float('baro_rel_alt_m')->nullable()->after('altitude');
            }
            if (!Schema::hasColumn('trip_points', 'grade_pct')) {
                $table->float('grade_pct')->nullable()->after('baro_rel_alt_m');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $vehicleColumns = ['last_baro_ok', 'last_baro_rel_alt_m', 'last_grade_ratio', 'last_grade_pct'];
        $existingVehicleColumns = array_values(array_filter($vehicleColumns, fn (string $c) => Schema::hasColumn('vehicles', $c)));
        if (!empty($existingVehicleColumns)) {
            Schema::table('vehicles', function (Blueprint $table) use ($existingVehicleColumns) {
                $table->dropColumn($existingVehicleColumns);
            });
        }

        $tripPointColumns = ['baro_rel_alt_m', 'grade_pct'];
        $existingTripPointColumns = array_values(array_filter($tripPointColumns, fn (string $c) => Schema::hasColumn('trip_points', $c)));
        if (!empty($existingTripPointColumns)) {
            Schema::table('trip_points', function (Blueprint $table) use ($existingTripPointColumns) {
                $table->dropColumn($existingTripPointColumns);
            });
        }
    }
};
