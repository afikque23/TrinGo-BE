<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan device_id untuk mendukung Guest Mode pada fuel logs
     */
    public function up(): void
    {
        Schema::table('fuel_logs', function (Blueprint $table) {
            // Tambahkan device_id
            $table->string('device_id', 100)->nullable()->after('vehicle_id');
            
            // Index untuk performa
            $table->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_logs', function (Blueprint $table) {
            $table->dropIndex(['device_id']);
            $table->dropColumn('device_id');
        });
    }
};
