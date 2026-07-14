<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom license_plate, color, dan semua parameter default dari tabel vehicles.
     * Field-field ini dipindahkan ke konteks perjalanan (trip) dan tidak lagi disimpan di level kendaraan.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'license_plate',
                'color',
                'default_beban',
                'default_penumpang',
                'default_gaya_berkendara',
                'default_kondisi_jalan',
                'default_medan',
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
            $table->enum('default_beban', ['ringan', 'sedang', 'berat'])->default('ringan')->after('transmisi');
            $table->boolean('default_penumpang')->default(false)->after('default_beban');
            $table->enum('default_gaya_berkendara', ['pelan', 'normal', 'agresif'])->default('normal')->after('default_penumpang');
            $table->enum('default_kondisi_jalan', ['macet', 'sedang', 'lancar'])->default('sedang')->after('default_gaya_berkendara');
            $table->enum('default_medan', ['datar', 'berbukit', 'campuran'])->default('datar')->after('default_kondisi_jalan');
        });
    }
};
