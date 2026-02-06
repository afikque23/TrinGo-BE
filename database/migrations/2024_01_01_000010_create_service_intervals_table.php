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
        Schema::create('service_intervals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('service_name', 100); // e.g., 'Ganti Oli', 'Cek Rem', 'Servis Besar'
            $table->string('service_type', 50); // e.g., 'oil_change', 'brake_check', 'major_service'
            $table->integer('interval_km')->unsigned(); // Interval dalam kilometer
            $table->integer('next_due_km')->unsigned()->nullable(); // Kapan servis berikutnya (km)
            $table->date('next_due_date')->nullable(); // Kapan servis berikutnya (tanggal)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['vehicle_id', 'is_active']);
            $table->index('next_due_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_intervals');
    }
};
