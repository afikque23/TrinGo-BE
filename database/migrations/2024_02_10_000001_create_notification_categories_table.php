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
        Schema::create('notification_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Display name of the category');
            $table->string('key')->unique()->comment('Unique identifier key');
            $table->string('icon')->nullable()->comment('Icon name/identifier');
            $table->string('color')->nullable()->comment('Color code or name');
            $table->boolean('is_active')->default(true)->comment('Whether category is active');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->timestamps();

            // Indexes
            $table->index('key');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_categories');
    }
};
