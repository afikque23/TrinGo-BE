<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom interval_value, last_service_mileage, dan last_service_date
     * untuk mendukung perhitungan persentase progress servis dengan benar
     */
    public function up(): void
    {
        Schema::table('service_schedules', function (Blueprint $table) {
            // Interval value (dalam km untuk mileage, atau hari untuk time-based)
            $table->unsignedInteger('interval_value')->default(0)->after('schedule_type')
                ->comment('Interval dalam km (schedule_type=km) atau hari (schedule_type=time)');
            
            // Last service tracking
            $table->unsignedInteger('last_service_mileage')->nullable()->after('interval_value')
                ->comment('Odometer saat terakhir servis (untuk schedule_type=km)');
            
            $table->date('last_service_date')->nullable()->after('last_service_mileage')
                ->comment('Tanggal terakhir servis (untuk schedule_type=time)');
            
            // Rename target_km to next_service_mileage for clarity (optional, but recommended)
            // Keep target_km for backward compatibility
            
            // Rename target_date to next_service_date for clarity (optional)
            // Keep target_date for backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->dropColumn(['interval_value', 'last_service_mileage', 'last_service_date']);
        });
    }
};
