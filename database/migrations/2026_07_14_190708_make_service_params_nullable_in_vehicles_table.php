<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'default_beban')) {
                $table->string('default_beban')->nullable()->after('transmisi');
                $table->boolean('default_penumpang')->nullable()->after('default_beban');
                $table->string('default_gaya_berkendara')->nullable()->after('default_penumpang');
                $table->string('default_kondisi_jalan')->nullable()->after('default_gaya_berkendara');
                $table->string('default_medan')->nullable()->after('default_kondisi_jalan');
            } else {
                $table->string('default_beban')->nullable()->change();
                $table->boolean('default_penumpang')->nullable()->change();
                $table->string('default_gaya_berkendara')->nullable()->change();
                $table->string('default_kondisi_jalan')->nullable()->change();
                $table->string('default_medan')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'default_beban')) {
                $table->enum('default_beban', ['ringan', 'sedang', 'berat'])->default('ringan')->change();
                $table->boolean('default_penumpang')->default(false)->change();
                $table->enum('default_gaya_berkendara', ['pelan', 'normal', 'agresif'])->default('normal')->change();
                $table->enum('default_kondisi_jalan', ['macet', 'sedang', 'lancar'])->default('sedang')->change();
                $table->enum('default_medan', ['datar', 'berbukit', 'campuran'])->default('datar')->change();
            }
        });
    }
};
