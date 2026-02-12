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
        Schema::table('service_histories', function (Blueprint $table) {
            // Add service_type_id as nullable foreign key
            $table->foreignId('service_type_id')
                  ->nullable()
                  ->after('vehicle_id')
                  ->constrained('service_types')
                  ->onDelete('restrict'); // Prevent deleting service type yang masih digunakan
            
            $table->index('service_type_id');
        });

        // Migrate existing data: match service_type string with service_types.name
        // This will populate service_type_id based on existing service_type string values
        DB::statement("
            UPDATE service_histories sh
            INNER JOIN service_types st ON sh.service_type = st.name
            SET sh.service_type_id = st.id
            WHERE sh.service_type_id IS NULL
        ");

        // Note: service_type column tetap ada untuk backward compatibility
        // Nanti bisa dihapus setelah mobile app sudah migrate ke service_type_id
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_histories', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->dropColumn('service_type_id');
        });
    }
};
