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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 200);
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->smallInteger('year')->unsigned()->nullable();
            $table->enum('tipe_motor', ['matic', 'manual', 'sport'])->default('matic');
            $table->string('vin', 64)->nullable()->unique();
            $table->integer('odometer')->unsigned()->default(0);
            $table->string('license_plate', 20)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
