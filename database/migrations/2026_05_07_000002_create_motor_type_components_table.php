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
        Schema::create('motor_type_components', function (Blueprint $table) {
            $table->id();
            $table->string('motor_type', 32);
            $table->foreignId('maintenance_component_id')
                ->constrained('maintenance_components')
                ->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['motor_type', 'maintenance_component_id']);
            $table->index(['motor_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motor_type_components');
    }
};
