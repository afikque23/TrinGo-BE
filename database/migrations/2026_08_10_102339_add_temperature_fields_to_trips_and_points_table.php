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
            if (!Schema::hasColumn('trip_points', 'engine_temp_c')) {
                $table->decimal('engine_temp_c', 5, 2)->nullable()->after('accuracy_meters');
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'max_temperature_c')) {
                $table->decimal('max_temperature_c', 5, 2)->nullable()->after('avg_temperature_c');
            }
            if (!Schema::hasColumn('trips', 'min_temperature_c')) {
                $table->decimal('min_temperature_c', 5, 2)->nullable()->after('max_temperature_c');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_points', function (Blueprint $table) {
            $table->dropColumn(['engine_temp_c']);
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['max_temperature_c', 'min_temperature_c']);
        });
    }
};
