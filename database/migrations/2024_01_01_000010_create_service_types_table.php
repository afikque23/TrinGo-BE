<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('name');
            $table->index('is_active');
        });

        // Insert default service types
        DB::table('service_types')->insert([
            ['name' => 'Ganti Oli', 'description' => 'Penggantian oli mesin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tune Up', 'description' => 'Tune up mesin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ganti Ban', 'description' => 'Penggantian ban motor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ganti Kampas Rem', 'description' => 'Penggantian kampas rem', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Service Berkala', 'description' => 'Service berkala standar', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Perbaikan Mesin', 'description' => 'Perbaikan komponen mesin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ganti Aki', 'description' => 'Penggantian aki/battery', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lainnya', 'description' => 'Jenis service lainnya', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
