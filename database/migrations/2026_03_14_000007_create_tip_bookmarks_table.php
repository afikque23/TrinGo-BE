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
        Schema::create('tip_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tip_id')->constrained('tips')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('device_id', 255)->nullable();
            $table->timestamps();
            
            $table->unique(['tip_id', 'user_id']);
            $table->index(['tip_id', 'device_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tip_bookmarks');
    }
};
