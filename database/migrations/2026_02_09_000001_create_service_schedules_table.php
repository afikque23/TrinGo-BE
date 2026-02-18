<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('service_schedules')) {
            Schema::create('service_schedules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('vehicle_id');
                $table->unsignedBigInteger('service_type_id');
                $table->enum('schedule_type', ['km', 'time']);
                $table->unsignedBigInteger('target_km')->nullable();
                $table->date('target_date')->nullable();
                $table->unsignedBigInteger('reminder_option_id');
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('vehicle_id');
                $table->index('is_active');
                $table->index('schedule_type');

                $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
                $table->foreign('service_type_id')->references('id')->on('service_types');
                $table->foreign('reminder_option_id')->references('id')->on('reminder_options');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['service_type_id']);
            $table->dropForeign(['reminder_option_id']);
        });
        Schema::dropIfExists('service_schedules');
    }
};
