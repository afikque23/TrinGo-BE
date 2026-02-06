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
        Schema::create('trip_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->integer('sequence')->unsigned();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('altitude')->nullable();
            $table->smallInteger('speed_kph')->nullable();
            $table->float('accuracy_meters')->nullable();
            $table->dateTime('recorded_at');
            $table->timestamp('created_at')->nullable();
            
            $table->index(['trip_id', 'sequence']);
            $table->index(['latitude', 'longitude']);
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_points');
    }
};
