<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom license_plate dan color dari tabel vehicles.
     * Field-field ini tidak digunakan lagi dalam aplikasi.
     * Catatan: default_beban, default_penumpang, dll. TETAP dipertahankan
     * karena dibutuhkan untuk kalkulasi jadwal servis.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'license_plate',
                'color',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('license_plate', 20)->nullable()->after('odometer');
            $table->string('color', 50)->nullable()->after('license_plate');
        });
    }
};
