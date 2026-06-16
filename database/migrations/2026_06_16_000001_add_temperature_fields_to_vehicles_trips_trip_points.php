<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah field suhu dari sensor BMP280.
     * - vehicles.last_temp_c      : suhu terakhir dari IoT (real-time)
     * - trip_points.temp_c        : suhu per titik perjalanan
     * - trips.ambient_temp_avg    : rata-rata suhu selama perjalanan (diisi saat STOP)
     */
    public function up(): void
    {
        // Tambah last_temp_c di vehicles (suhu terakhir dari IoT)
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'last_temp_c')) {
                $table->float('last_temp_c')->nullable()->after('last_grade_pct')
                      ->comment('Suhu ambient terakhir dari sensor BMP280 (°C)');
            }
        });

        // Tambah temp_c di trip_points (suhu per titik GPS)
        Schema::table('trip_points', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_points', 'temp_c')) {
                $table->float('temp_c')->nullable()->after('grade_pct')
                      ->comment('Suhu ambient dari BMP280 pada titik ini (°C)');
            }
        });

        // Tambah ambient_temp_avg di trips (rata-rata suhu selama trip, diisi saat STOP)
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'ambient_temp_avg')) {
                $table->decimal('ambient_temp_avg', 5, 2)->nullable()->after('elevation_gain')
                      ->comment('Rata-rata suhu ambient dari BMP280 selama perjalanan (°C)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'last_temp_c')) {
                $table->dropColumn('last_temp_c');
            }
        });

        Schema::table('trip_points', function (Blueprint $table) {
            if (Schema::hasColumn('trip_points', 'temp_c')) {
                $table->dropColumn('temp_c');
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'ambient_temp_avg')) {
                $table->dropColumn('ambient_temp_avg');
            }
        });
    }
};
