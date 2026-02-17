<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom service_name untuk mendukung custom nama service
     * yang berbeda dari service_type
     */
    public function up(): void
    {
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->string('service_name', 200)->nullable()->after('service_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->dropColumn('service_name');
        });
    }
};
