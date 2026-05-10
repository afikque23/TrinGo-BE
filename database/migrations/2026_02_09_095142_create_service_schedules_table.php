<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // NOTE:
        // A table-creation migration for `service_schedules` already exists in this repo.
        // This migration is kept to avoid breaking migration history, but it should not
        // attempt to create the table again.

        if (!Schema::hasTable('service_schedules')) {
            return;
        }

        $indexName = 'service_schedules_vehicle_id_is_active_index';

        $indexExists = !empty(DB::select(
            'SHOW INDEX FROM `service_schedules` WHERE Key_name = ?',
            [$indexName]
        ));

        if ($indexExists) {
            return;
        }

        Schema::table('service_schedules', function (Blueprint $table) use ($indexName) {
            $table->index(['vehicle_id', 'is_active'], $indexName);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('service_schedules')) {
            return;
        }

        $indexName = 'service_schedules_vehicle_id_is_active_index';

        $indexExists = !empty(DB::select(
            'SHOW INDEX FROM `service_schedules` WHERE Key_name = ?',
            [$indexName]
        ));

        if (!$indexExists) {
            return;
        }

        Schema::table('service_schedules', function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }
};
