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
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'last_latitude')) {
                $table->decimal('last_latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'last_longitude')) {
                $table->decimal('last_longitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'last_speed_kph')) {
                $table->smallInteger('last_speed_kph')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'last_heading_deg')) {
                $table->smallInteger('last_heading_deg')->unsigned()->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'last_altitude')) {
                $table->float('last_altitude')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'last_accuracy_meters')) {
                $table->float('last_accuracy_meters')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'last_satellites')) {
                $table->smallInteger('last_satellites')->unsigned()->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'last_hdop')) {
                $table->float('last_hdop')->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'last_telemetry_at')) {
                $table->dateTime('last_telemetry_at')->nullable();
            }
            if (!Schema::hasColumn('vehicles', 'last_telemetry_received_at')) {
                $table->timestamp('last_telemetry_received_at')->nullable();

                // Only create index when we create the column to avoid duplicate-index errors.
                $table->index('last_telemetry_received_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'last_latitude',
            'last_longitude',
            'last_speed_kph',
            'last_heading_deg',
            'last_altitude',
            'last_accuracy_meters',
            'last_satellites',
            'last_hdop',
            'last_telemetry_at',
            'last_telemetry_received_at',
        ];

        $existing = array_values(array_filter($columns, fn (string $c) => Schema::hasColumn('vehicles', $c)));
        if (empty($existing)) {
            return;
        }

        Schema::table('vehicles', function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
};
