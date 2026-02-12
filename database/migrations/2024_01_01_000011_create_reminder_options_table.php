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
        Schema::create('reminder_options', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // Contoh: "200 km sebelum"
            $table->integer('value'); // Contoh: 200
            $table->enum('unit', ['km', 'days', 'weeks', 'months', 'years']); // km, days, weeks, months, years
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index untuk optimasi query
            $table->index('is_active');
            $table->index('unit');
            $table->unique(['value', 'unit']); // Kombinasi value + unit harus unik
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_options');
    }
};
