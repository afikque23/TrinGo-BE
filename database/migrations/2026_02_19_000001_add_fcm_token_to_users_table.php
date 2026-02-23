<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom fcm_token untuk menyimpan Firebase Cloud Messaging token
     * agar server bisa mengirim push notification ke device Flutter pengguna.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('fcm_token')->nullable()->after('device_name')
                ->comment('Firebase Cloud Messaging token untuk push notification');
            $table->timestamp('fcm_token_updated_at')->nullable()->after('fcm_token')
                ->comment('Kapan terakhir fcm_token diperbarui');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fcm_token', 'fcm_token_updated_at']);
        });
    }
};
