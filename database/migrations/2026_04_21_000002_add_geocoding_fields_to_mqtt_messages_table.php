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
            if (!Schema::hasColumn('mqtt_messages', 'geocoded_address')) {
                $table->text('geocoded_address')->nullable()->after('payload_json');
            }
            if (!Schema::hasColumn('mqtt_messages', 'geocoded_raw')) {
                $table->json('geocoded_raw')->nullable()->after('geocoded_address');
            }
            if (!Schema::hasColumn('mqtt_messages', 'geocoded_at')) {
                $table->timestamp('geocoded_at')->nullable()->after('geocoded_raw');
                $table->index('geocoded_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = ['geocoded_address', 'geocoded_raw', 'geocoded_at'];
        $existing = array_values(array_filter($columns, fn (string $c) => Schema::hasColumn('mqtt_messages', $c)));
        if (empty($existing)) {
            return;
        }

        Schema::table('mqtt_messages', function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
};
