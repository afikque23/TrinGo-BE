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
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('filled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('filled_at');
            $table->decimal('liters', 8, 3);
            $table->integer('price_cents')->unsigned();
            $table->string('currency', 3)->default('IDR');
            $table->integer('odometer')->nullable();
            $table->string('station_name', 150)->nullable();
            $table->decimal('fuel_efficiency', 8, 2)->nullable()->comment('km per liter');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['vehicle_id', 'filled_at']);
            $table->index('filled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};
