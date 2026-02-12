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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->onDelete('cascade');
            $table->string('category_key')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            // Indexes for better query performance
            $table->index('user_id');
            $table->index('template_id');
            $table->index('category_key');

            // Unique constraints
            // User can have only one preference per template
            $table->unique(['user_id', 'template_id'], 'unique_user_template');
            // User can have only one preference per category
            $table->unique(['user_id', 'category_key'], 'unique_user_category');

            // Foreign key for category_key (references notification_categories.key)
            $table->foreign('category_key')
                  ->references('key')
                  ->on('notification_categories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
