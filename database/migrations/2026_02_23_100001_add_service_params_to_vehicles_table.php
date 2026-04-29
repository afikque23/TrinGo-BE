<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah parameter profil motor untuk kalkulasi jadwal service yang akurat.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Kapasitas mesin
            $table->enum('kapasitas_cc', ['<125', '125-250', '>250'])
                  ->default('<125')
                  ->after('tipe_motor')
                  ->comment('Kapasitas mesin: <125cc, 125-250cc, >250cc');

            // Transmisi
            $table->enum('transmisi', ['manual', 'cvt'])
                  ->default('cvt')
                  ->after('kapasitas_cc')
                  ->comment('Jenis transmisi: manual atau CVT (otomatis)');

            // Parameter default penggunaan (dipakai sebagai baseline kalkulasi)
            $table->enum('default_beban', ['ringan', 'sedang', 'berat'])
                  ->default('ringan')
                  ->after('transmisi')
                  ->comment('Beban bawaan default sehari-hari');

            $table->boolean('default_penumpang')
                  ->default(false)
                  ->after('default_beban')
                  ->comment('Apakah sering membawa penumpang secara default');

            $table->enum('default_gaya_berkendara', ['pelan', 'normal', 'agresif'])
                  ->default('normal')
                  ->after('default_penumpang')
                  ->comment('Gaya berkendara dominan sehari-hari');

            $table->enum('default_kondisi_jalan', ['macet', 'sedang', 'lancar'])
                  ->default('sedang')
                  ->after('default_gaya_berkendara')
                  ->comment('Kondisi lalu lintas default sehari-hari');

            $table->enum('default_medan', ['datar', 'berbukit', 'campuran'])
                  ->default('datar')
                  ->after('default_kondisi_jalan')
                  ->comment('Medan jalan dominan sehari-hari');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'kapasitas_cc',
                'transmisi',
                'default_beban',
                'default_penumpang',
                'default_gaya_berkendara',
                'default_kondisi_jalan',
                'default_medan',
            ]);
        });
    }
};
