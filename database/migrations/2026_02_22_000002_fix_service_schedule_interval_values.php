<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix existing service_schedules data by populating interval_value
     * based on schedule_type and existing target values
     */
    public function up(): void
    {
        // Fix mileage-based schedules (schedule_type = 'km')
        DB::statement("
            UPDATE service_schedules
            SET interval_value = target_km - COALESCE(last_service_mileage, 0)
            WHERE schedule_type = 'km'
              AND target_km IS NOT NULL
              AND (interval_value = 0 OR interval_value IS NULL)
        ");

        // Fix time-based schedules (schedule_type = 'time')
        // Calculate days between target_date and last_service_date
        DB::statement("
            UPDATE service_schedules
            SET interval_value = DATEDIFF(target_date, COALESCE(last_service_date, DATE_SUB(target_date, INTERVAL 30 DAY)))
            WHERE schedule_type = 'time'
              AND target_date IS NOT NULL
              AND (interval_value = 0 OR interval_value IS NULL)
        ");

        // Log untuk debugging
        $fixed = DB::select("
            SELECT COUNT(*) as total_fixed
            FROM service_schedules
            WHERE interval_value > 0
        ");

        \Log::info('Service Schedule Interval Value Fix Migration', [
            'total_schedules_with_interval' => $fixed[0]->total_fixed ?? 0
        ]);
    }

    /**
     * Reverse the migrations.
     * 
     * Note: We don't reset interval_value to 0 because it would break existing functionality
     */
    public function down(): void
    {
        // Don't reset to 0 as it would break the app
        // Just log that migration was rolled back
        \Log::info('Service Schedule Interval Value Fix Migration rolled back (data preserved)');
    }
};
