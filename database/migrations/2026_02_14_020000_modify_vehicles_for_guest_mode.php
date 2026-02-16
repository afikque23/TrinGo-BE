<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Modify vehicles table to support Guest Mode:
     * - Make user_id nullable
     * - Add device_id column
     * - Add check constraint to ensure at least one owner identifier exists
     */
    public function up(): void
    {
        // Drop existing check constraint if exists
        try {
            DB::statement('ALTER TABLE vehicles DROP CHECK check_vehicle_owner;');
        } catch (\Exception $e) {
            // Constraint might not exist, that's okay
        }

        // Check if device_id column already exists
        $hasDeviceId = Schema::hasColumn('vehicles', 'device_id');

        // Drop foreign key constraint to modify column (if exists)
        try {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Exception $e) {
            // Foreign key might not exist, that's okay
        }

        // Make user_id nullable using raw SQL (avoids requiring doctrine/dbal)
        DB::statement('ALTER TABLE vehicles MODIFY user_id BIGINT UNSIGNED NULL;');

        // Add device_id column and index only if it doesn't exist
        if (!$hasDeviceId) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('device_id', 64)->nullable()->after('user_id');
                $table->index(['device_id']);
            });
        } else {
            // If column exists, ensure it has index
            if (!$this->hasIndex('vehicles', 'device_id')) {
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->index(['device_id']);
                });
            }
        }

        // Recreate foreign key constraint (now nullable)
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add check constraint to ensure at least one owner identifier exists
        // This requires MySQL 8.0.16+ or MariaDB 10.2.1+
        try {
            DB::statement('ALTER TABLE vehicles ADD CONSTRAINT check_vehicle_owner CHECK (user_id IS NOT NULL OR device_id IS NOT NULL);');
        } catch (\Exception $e) {
            // Constraint might already exist, that's okay
        }
    }

    /**
     * Check if index exists on a table column
     */
    private function hasIndex(string $table, string $column): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Column_name = ?", [$column]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop check constraint
        DB::statement('ALTER TABLE vehicles DROP CHECK check_vehicle_owner;');

        // Drop foreign key and index
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['device_id']);
            $table->dropColumn('device_id');
        });

        // Make user_id NOT NULL again
        DB::statement('ALTER TABLE vehicles MODIFY user_id BIGINT UNSIGNED NOT NULL;');

        // Recreate foreign key constraint
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
