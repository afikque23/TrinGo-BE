<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan device_id untuk mendukung Guest Mode
     * - user_id menjadi nullable
     * - device_id digunakan untuk identifikasi perangkat sebelum login
     * - Minimal salah satu (user_id atau device_id) harus ada
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Ubah user_id menjadi nullable
            $table->foreignId('user_id')->nullable()->change();
            
            // Tambahkan device_id
            $table->string('device_id', 100)->nullable()->after('user_id');
            
            // Index untuk performa
            $table->index('device_id');
            
            // Composite index untuk query pattern umum
            $table->index(['device_id', 'created_at']);
        });
        
        // Tambahkan constraint: salah satu harus ada (user_id atau device_id)
        DB::statement('ALTER TABLE vehicles ADD CONSTRAINT check_vehicle_owner CHECK (user_id IS NOT NULL OR device_id IS NOT NULL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop constraint
            DB::statement('ALTER TABLE vehicles DROP CONSTRAINT IF EXISTS check_vehicle_owner');
            
            // Drop indexes
            $table->dropIndex(['device_id']);
            $table->dropIndex(['device_id', 'created_at']);
            
            // Drop column
            $table->dropColumn('device_id');
            
            // Restore user_id to non-nullable
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
