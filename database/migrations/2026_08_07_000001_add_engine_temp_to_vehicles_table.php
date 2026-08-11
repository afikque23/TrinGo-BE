<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom monitoring suhu mesin DS18B20.
     * Data ini HANYA untuk monitoring & rekomendasi Gemini AI.
     * TIDAK digunakan sebagai input variabel Fuzzy Mamdani.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Suhu mesin terakhir dari sensor DS18B20
            $table->decimal('last_engine_temp_c', 5, 2)->nullable()->after('last_grade_pct')
                ->comment('Suhu mesin terakhir dari DS18B20 (°C). Hanya untuk monitoring, bukan input fuzzy.');
            // Status overheat (true jika > 110°C)
            $table->boolean('last_engine_overheat')->nullable()->after('last_engine_temp_c')
                ->comment('True jika suhu mesin melebihi 110°C (overheat kritis).');
            // Timestamp terakhir kali suhu diterima
            $table->timestamp('last_engine_temp_at')->nullable()->after('last_engine_overheat')
                ->comment('Waktu terakhir data suhu mesin diterima dari IoT device.');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['last_engine_temp_c', 'last_engine_overheat', 'last_engine_temp_at']);
        });
    }
};
