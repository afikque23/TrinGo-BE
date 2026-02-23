<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel device_tokens untuk menyimpan FCM token per device.
     * Mendukung Guest Mode (device_id) dan Authenticated User (user_id).
     * Satu user bisa punya banyak device, satu device punya satu token aktif.
     */
    public function up(): void
    {
        Schema::dropIfExists('device_tokens');
        
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')
                ->comment('User pemilik device (null jika guest)');
            $table->string('device_id', 100)->nullable()
                ->comment('Device ID untuk guest mode');
            $table->string('fcm_token', 500)
                ->comment('Firebase Cloud Messaging token');
            $table->string('device_type', 20)->default('android')
                ->comment('Tipe device: android, ios');
            $table->string('device_name', 200)->nullable()
                ->comment('Nama device untuk identifikasi');
            $table->boolean('is_active')->default(true)
                ->comment('Apakah token masih aktif');
            $table->timestamp('last_used_at')->nullable()
                ->comment('Terakhir digunakan untuk kirim notifikasi');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('device_id');
            $table->index('is_active');
            $table->unique(['user_id', 'device_id', 'fcm_token'], 'unique_device_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
