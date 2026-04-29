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
        Schema::create('mqtt_messages', function (Blueprint $table) {
            $table->id();

            $table->string('topic', 255);
            $table->unsignedTinyInteger('qos')->default(0);
            $table->boolean('retained')->default(false);

            $table->longText('payload');
            $table->json('payload_json')->nullable();

            $table->timestamp('received_at')->useCurrent();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['topic', 'received_at']);
            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mqtt_messages');
    }
};
