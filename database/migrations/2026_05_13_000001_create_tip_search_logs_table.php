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
        Schema::create('tip_search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('device_id', 255)->nullable();
            $table->string('keyword', 200);
            $table->string('source', 50)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['device_id', 'created_at']);
            $table->index(['keyword', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tip_search_logs');
    }
};
