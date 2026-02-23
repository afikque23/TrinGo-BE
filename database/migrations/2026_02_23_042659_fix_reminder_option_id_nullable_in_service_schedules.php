<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix reminder_option_id to be nullable for custom reminder feature.
     * Users can now choose between predefined reminder options OR custom reminder threshold.
     */
    public function up(): void
    {
        // Drop foreign key constraint first
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->dropForeign(['reminder_option_id']);
        });

        // Make column nullable using raw SQL (more reliable than change())
        DB::statement('ALTER TABLE `service_schedules` MODIFY `reminder_option_id` BIGINT UNSIGNED NULL');

        // Re-add foreign key constraint with nullable support
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->foreign('reminder_option_id')
                ->references('id')
                ->on('reminder_options')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->dropForeign(['reminder_option_id']);
        });

        // Make NOT NULL again
        DB::statement('ALTER TABLE `service_schedules` MODIFY `reminder_option_id` BIGINT UNSIGNED NOT NULL');

        // Re-add foreign key
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->foreign('reminder_option_id')
                ->references('id')
                ->on('reminder_options');
        });
    }
};
