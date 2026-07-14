<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tidak ada perubahan schema yang diperlukan untuk trip tracking BMP280.
     * Kolom elevation_gain sudah ada dari migration sebelumnya.
     * Parameter suhu (temperature_c) tidak dipakai dalam sistem ini.
     */
    public function up(): void
    {
        // No-op: tidak ada kolom baru yang perlu ditambah
    }

    public function down(): void
    {
        // No-op
    }
};
