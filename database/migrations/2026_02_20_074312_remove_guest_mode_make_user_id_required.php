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
     * Remove guest mode functionality:
     * - Drop constraints that allow device_id-only ownership
     * - Make user_id required (NOT NULL) for all user data
     * - Keep device_id for tracking purposes only
     * 
     * Data dengan user_id NULL akan dihapus (guest data)
     */
    public function up(): void
    {
        // 1. Delete guest data (records with user_id/filled_by/started_by = NULL)
        DB::table('fuel_logs')->whereNull('filled_by')->delete();
        DB::table('trips')->whereNull('started_by')->delete();
        DB::table('notifications')->whereNull('user_id')->delete();
        DB::table('vehicles')->whereNull('user_id')->delete();

        // 2. Drop check constraints for vehicles
        try {
            DB::statement('ALTER TABLE vehicles DROP CONSTRAINT IF EXISTS check_vehicle_owner');
        } catch (\Exception $e) {
            // MySQL uses different syntax
            // Constraint might not exist or already dropped
        }

        // 3. Drop check constraints for notifications
        try {
            DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS check_notification_owner');
        } catch (\Exception $e) {
            // MySQL uses different syntax
            // Constraint might not exist or already dropped
        }

        // 4. Make user_id NOT NULL for vehicles
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        // 5. Make user_id NOT NULL for notifications
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        // 6. Update trips foreign key: drop old, change column, recreate with CASCADE
        if (Schema::hasColumn('trips', 'started_by')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->dropForeign(['started_by']);
            });
            
            Schema::table('trips', function (Blueprint $table) {
                $table->unsignedBigInteger('started_by')->nullable(false)->change();
                $table->foreign('started_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 7. Update fuel_logs foreign key: drop old, change column, recreate with CASCADE
        if (Schema::hasColumn('fuel_logs', 'filled_by')) {
            Schema::table('fuel_logs', function (Blueprint $table) {
                $table->dropForeign(['filled_by']);
            });
            
            Schema::table('fuel_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('filled_by')->nullable(false)->change();
                $table->foreign('filled_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Note: device_id columns are kept for tracking purposes
        // but are no longer used for data ownership
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Update trips foreign key back to SET NULL
        if (Schema::hasColumn('trips', 'started_by')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->dropForeign(['started_by']);
            });
            
            Schema::table('trips', function (Blueprint $table) {
                $table->unsignedBigInteger('started_by')->nullable()->change();
                $table->foreign('started_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 2. Update fuel_logs foreign key back to SET NULL
        if (Schema::hasColumn('fuel_logs', 'filled_by')) {
            Schema::table('fuel_logs', function (Blueprint $table) {
                $table->dropForeign(['filled_by']);
            });
            
            Schema::table('fuel_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('filled_by')->nullable()->change();
                $table->foreign('filled_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 3. Make user_id nullable again for vehicles and notifications
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // 4. Re-add check constraints
        DB::statement('ALTER TABLE vehicles ADD CONSTRAINT check_vehicle_owner CHECK (user_id IS NOT NULL OR device_id IS NOT NULL)');
        DB::statement('ALTER TABLE notifications ADD CONSTRAINT check_notification_owner CHECK (user_id IS NOT NULL OR device_id IS NOT NULL)');
    }
};

