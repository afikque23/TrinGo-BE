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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('User who receives the notification');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('cascade')->comment('Related vehicle if any');
            $table->string('category_key')->comment('Category reference');
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->onDelete('set null')->comment('Template used');
            $table->string('title')->comment('Notification title');
            $table->text('message')->comment('Notification message');
            $table->json('data_payload')->nullable()->comment('Additional data in JSON format');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal')->comment('Priority level');
            $table->enum('sent_via', ['in_app', 'push', 'email'])->default('in_app')->comment('Channel used to send');
            $table->boolean('is_read')->default(false)->comment('Whether notification is read');
            $table->timestamp('read_at')->nullable()->comment('When notification was read');
            $table->timestamps();

            // Foreign key for category
            $table->foreign('category_key')
                  ->references('key')
                  ->on('notification_categories')
                  ->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('category_key');
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
