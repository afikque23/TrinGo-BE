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
        Schema::table('trip_points', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
        });

        Schema::table('trip_points', function (Blueprint $table) {
            $table->boolean('has_fix')->nullable()->after('sequence');
            $table->decimal('latitude', 10, 7)->nullable()->change();
            $table->decimal('longitude', 10, 7)->nullable()->change();
            $table->float('est_distance_m')->nullable()->after('speed_kph');
            $table->boolean('mpu_is_moving')->nullable()->after('est_distance_m');
            $table->float('mpu_g_force')->nullable()->after('mpu_is_moving');
        });

        Schema::table('trip_points', function (Blueprint $table) {
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_points', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
        });

        Schema::table('trip_points', function (Blueprint $table) {
            $table->dropColumn(['has_fix', 'est_distance_m', 'mpu_is_moving', 'mpu_g_force']);
            $table->decimal('latitude', 10, 7)->nullable(false)->change();
            $table->decimal('longitude', 10, 7)->nullable(false)->change();
        });

        Schema::table('trip_points', function (Blueprint $table) {
            $table->index(['latitude', 'longitude']);
        });
    }
};
