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
        Schema::table('users', function (Blueprint $table) {
            // Only add refresh token related columns
            $table->text('refresh_token')->nullable()->after('password');
            $table->timestamp('refresh_token_expires_at')->nullable()->after('refresh_token');
            $table->string('device_id')->nullable()->after('refresh_token_expires_at');
            $table->string('device_name')->nullable()->after('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['refresh_token', 'refresh_token_expires_at', 'device_id', 'device_name']);
        });
    }
};
