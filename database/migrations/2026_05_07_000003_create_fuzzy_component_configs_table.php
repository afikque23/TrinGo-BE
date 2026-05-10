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
        Schema::create('fuzzy_component_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_type_component_id')
                ->constrained('motor_type_components')
                ->cascadeOnDelete();

            // Threshold score condition (0..100). Higher is better.
            $table->unsignedTinyInteger('warn_score')->default(60);
            $table->unsignedTinyInteger('critical_score')->default(40);

            // All membership functions + rules in one JSON blob for dynamic configuration.
            $table->json('config');
            $table->unsignedInteger('version')->default(1);

            $table->timestamps();

            $table->unique(['motor_type_component_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuzzy_component_configs');
    }
};
