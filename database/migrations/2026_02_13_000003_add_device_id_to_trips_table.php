<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan device_id untuk mendukung Guest Mode pada trips
     */
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Tambahkan device_id jika belum ada user_id
            // Cek dulu apakah trips table punya user_id atau tidak
            if (!Schema::hasColumn('trips', 'user_id')) {
                $table->string('device_id', 100)->nullable()->after('vehicle_id');
            } else {
                $table->foreignId('user_id')->nullable()->change();
                $table->string('device_id', 100)->nullable()->after('user_id');
            }
            
            // Index untuk performa
            $table->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropIndex(['device_id']);
            $table->dropColumn('device_id');
            
            if (Schema::hasColumn('trips', 'user_id')) {
                $table->foreignId('user_id')->nullable(false)->change();
            }
        });
    }
};
