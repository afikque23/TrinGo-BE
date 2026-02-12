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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('service_type_id')->constrained('service_types')->onDelete('restrict');
            $table->date('service_date');
            $table->integer('odometer_km')->unsigned();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('workshop_name', 200)->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_image', 255)->nullable();
            $table->timestamps();
            
            $table->index(['vehicle_id', 'service_date']);
            $table->index('service_type_id');
            $table->index('service_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
