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
            $table->decimal('odometer', 10, 2)->default(0)->change();
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('start_odometer', 10, 2)->nullable()->change();
            $table->decimal('end_odometer', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('odometer')->unsigned()->default(0)->change();
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->integer('start_odometer')->nullable()->change();
            $table->integer('end_odometer')->nullable()->change();
        });
    }
};
