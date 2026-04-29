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
        Schema::table('mqtt_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('mqtt_messages', 'address')) {
                $table->text('address')->nullable()->after('geocoded_at');
            }
            if (!Schema::hasColumn('mqtt_messages', 'maps_url')) {
                $table->text('maps_url')->nullable()->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = ['address', 'maps_url'];
        $existing = array_values(array_filter($columns, fn (string $c) => Schema::hasColumn('mqtt_messages', $c)));
        if (empty($existing)) {
            return;
        }

        Schema::table('mqtt_messages', function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
};
