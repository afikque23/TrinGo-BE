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
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'avg_temperature_c')) {
                $table->decimal('avg_temperature_c', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('trips', 'max_temperature_c')) {
                $table->decimal('max_temperature_c', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('trips', 'min_temperature_c')) {
                $table->decimal('min_temperature_c', 5, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['avg_temperature_c', 'max_temperature_c', 'min_temperature_c']);
        });
    }
};
