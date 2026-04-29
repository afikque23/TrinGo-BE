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
        Schema::create('tip_tag_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tip_id')->constrained('tips')->onDelete('cascade');
            $table->foreignId('tip_tag_id')->constrained('tip_tags')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['tip_id', 'tip_tag_id']);
            $table->index('tip_id');
            $table->index('tip_tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tip_tag_pivot');
    }
};
