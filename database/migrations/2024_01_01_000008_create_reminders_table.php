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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('reminder_type', ['service', 'inspection', 'insurance', 'registration', 'custom'])->default('custom');
            $table->date('due_date')->nullable();
            $table->integer('due_odometer')->nullable();
            $table->integer('advance_days')->default(7)->comment('Days before due date to notify');
            $table->integer('advance_km')->nullable()->comment('Km before due odometer to notify');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['vehicle_id', 'due_date']);
            $table->index(['vehicle_id', 'due_odometer']);
            $table->index(['user_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
