<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan device_id untuk mendukung Guest Mode pada notifications
     * - user_id menjadi nullable
     * - device_id digunakan untuk notifikasi ke perangkat sebelum login
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Ubah user_id menjadi nullable
            $table->foreignId('user_id')->nullable()->change();
            
            // Tambahkan device_id
            $table->string('device_id', 100)->nullable()->after('user_id');
            
            // Index untuk performa
            $table->index('device_id');
            
            // Composite index untuk query pattern umum
            $table->index(['device_id', 'is_read']);
        });
        
        // Tambahkan constraint: salah satu harus ada (user_id atau device_id)
        DB::statement('ALTER TABLE notifications ADD CONSTRAINT check_notification_owner CHECK (user_id IS NOT NULL OR device_id IS NOT NULL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop constraint
            DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS check_notification_owner');
            
            // Drop indexes
            $table->dropIndex(['device_id']);
            $table->dropIndex(['device_id', 'is_read']);
            
            // Drop column
            $table->dropColumn('device_id');
            
            // Restore user_id to non-nullable
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
