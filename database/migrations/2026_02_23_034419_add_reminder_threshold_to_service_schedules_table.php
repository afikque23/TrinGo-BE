<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom reminder_threshold untuk custom reminder manual
     * dan reminder_sent untuk tracking notifikasi yang sudah dikirim
     */
    public function up(): void
    {
        Schema::table('service_schedules', function (Blueprint $table) {
            // Custom reminder threshold (dalam km atau hari)
            $table->unsignedInteger('reminder_threshold')->nullable()->after('reminder_option_id')
                ->comment('Jarak (km) atau waktu (hari) sebelum jadwal untuk trigger notifikasi');
            
            // Tracking apakah reminder sudah dikirim
            $table->boolean('reminder_sent')->default(false)->after('reminder_threshold')
                ->comment('Flag untuk menandai apakah notifikasi reminder sudah dikirim');
            
            // Timestamp kapan reminder terakhir dikirim
            $table->timestamp('reminder_sent_at')->nullable()->after('reminder_sent')
                ->comment('Timestamp saat notifikasi reminder terakhir dikirim');
        });

        // Create index untuk performance pada reminder checks
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->index(['vehicle_id', 'is_active', 'reminder_threshold', 'reminder_sent'], 'idx_schedule_reminders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedule_reminders');
            $table->dropColumn(['reminder_threshold', 'reminder_sent', 'reminder_sent_at']);
        });
    }
};
