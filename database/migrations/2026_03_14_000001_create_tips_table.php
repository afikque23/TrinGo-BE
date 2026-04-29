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
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Vehicle info
            $table->string('vehicle_brand', 50);
            $table->string('vehicle_model', 100);
            $table->integer('vehicle_year')->unsigned();
            $table->string('riding_style', 50);
            
            // Estimated time
            $table->string('estimated_time', 50)->nullable();
            
            // Maintenance interval
            $table->integer('interval_distance_km')->unsigned()->nullable();
            $table->integer('interval_time_months')->unsigned()->nullable();
            
            // Additional info
            $table->text('important_notes')->nullable();
            $table->json('hashtags')->nullable();
            $table->boolean('is_copyable')->default(true);
            
            // Status and moderation
            $table->enum('status', ['pending_review', 'published', 'rejected'])->default('pending_review');
            
            // Stats
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('likes_count')->unsigned()->default(0);
            $table->integer('bookmarks_count')->unsigned()->default(0);
            $table->integer('shares_count')->unsigned()->default(0);
            $table->integer('views_count')->unsigned()->default(0);
            $table->integer('usage_count')->unsigned()->default(0);
            $table->integer('success_count')->unsigned()->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('vehicle_brand');
            $table->index('riding_style');
            $table->index(['status', 'created_at']);
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tips');
    }
};
