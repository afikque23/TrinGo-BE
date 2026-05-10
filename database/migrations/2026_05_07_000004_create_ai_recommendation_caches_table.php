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
        Schema::create('ai_recommendation_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            $table->string('motor_type', 32);
            $table->json('inputs');
            $table->json('scores');
            $table->json('statuses');

            $table->string('config_hash', 64);
            $table->string('scores_hash', 64);
            $table->string('prompt_hash', 64);
            $table->string('model', 80)->nullable();

            // Gemini outputs multiple sections (home/service pages) as JSON.
            $table->json('sections');
            $table->timestamp('generated_at');

            $table->timestamps();

            $table->unique(['user_id', 'vehicle_id']);
            $table->index(['vehicle_id', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recommendation_caches');
    }
};
