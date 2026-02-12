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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Template name');
            $table->string('category_key')->comment('Category key reference');
            $table->string('trigger_type')->nullable()->comment('Trigger type: km_before_interval, overdue, monthly_summary, etc');
            $table->integer('threshold_value')->nullable()->comment('Threshold value for trigger');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal')->comment('Priority level');
            $table->enum('channel', ['in_app', 'push', 'email'])->default('in_app')->comment('Notification channel');
            $table->text('message_template')->comment('Message template with variables');
            $table->boolean('is_active')->default(true)->comment('Whether template is active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Admin who created this template');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('category_key')
                  ->references('key')
                  ->on('notification_categories')
                  ->onDelete('cascade');

            // Indexes
            $table->index('category_key');
            $table->index('is_active');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
