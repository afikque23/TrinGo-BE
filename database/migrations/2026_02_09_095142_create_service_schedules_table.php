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
        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->onDelete('cascade');
            $table->foreignId('service_type_id')
                  ->constrained('service_types')
                  ->onDelete('restrict');
            $table->enum('schedule_type', ['km', 'time']);
            $table->unsignedInteger('target_km')->nullable();
            $table->date('target_date')->nullable();
            $table->foreignId('reminder_option_id')
                  ->constrained('reminder_options')
                  ->onDelete('restrict');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('vehicle_id');
            $table->index('is_active');
            $table->index('schedule_type');
            $table->index(['vehicle_id', 'is_active']); // Composite index for common query pattern
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_schedules');
    }
};
