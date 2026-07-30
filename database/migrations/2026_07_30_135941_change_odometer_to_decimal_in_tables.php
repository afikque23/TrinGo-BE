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
            $table->decimal('odometer', 10, 2)->unsigned()->default(0)->change();
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('start_odometer', 10, 2)->nullable()->change();
            $table->decimal('end_odometer', 10, 2)->nullable()->change();
        });
        
        Schema::table('service_histories', function (Blueprint $table) {
            $table->decimal('odometer', 10, 2)->nullable()->change();
        });
        
        Schema::table('fuel_logs', function (Blueprint $table) {
            $table->decimal('odometer', 10, 2)->nullable()->change();
        });
        
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('odometer_km', 10, 2)->unsigned()->change();
        });
        
        Schema::table('reminders', function (Blueprint $table) {
            $table->decimal('due_odometer', 10, 2)->nullable()->change();
            $table->decimal('advance_km', 10, 2)->nullable()->change();
        });
        
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->decimal('last_service_mileage', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('odometer')->unsigned()->default(0)->change();
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->integer('start_odometer')->nullable()->change();
            $table->integer('end_odometer')->nullable()->change();
        });
        
        Schema::table('service_histories', function (Blueprint $table) {
            $table->integer('odometer')->nullable()->change();
        });
        
        Schema::table('fuel_logs', function (Blueprint $table) {
            $table->integer('odometer')->nullable()->change();
        });
        
        Schema::table('services', function (Blueprint $table) {
            $table->integer('odometer_km')->unsigned()->change();
        });
        
        Schema::table('reminders', function (Blueprint $table) {
            $table->integer('due_odometer')->nullable()->change();
            $table->integer('advance_km')->nullable()->change();
        });
        
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->integer('last_service_mileage')->nullable()->change();
        });
    }
};
