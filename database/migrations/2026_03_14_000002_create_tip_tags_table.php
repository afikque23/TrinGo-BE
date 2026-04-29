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
        Schema::create('tip_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->enum('type', ['trending', 'ai_recommended', 'verified', 'riding_style', 'custom'])->default('custom');
            $table->string('color', 7)->default('#6B7C4F');
            $table->string('icon', 50)->nullable();
            $table->timestamps();
            
            $table->unique(['name', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tip_tags');
    }
};
