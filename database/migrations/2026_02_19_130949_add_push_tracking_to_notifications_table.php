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
        Schema::table('notifications', function (Blueprint $table) {
            $table->boolean('push_sent')->default(false)->after('sent_via')->comment('Whether push notification was attempted');
            $table->boolean('push_success')->default(false)->after('push_sent')->comment('Whether push notification succeeded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['push_sent', 'push_success']);
        });
    }
};
