<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tips') && Schema::hasColumn('tips', 'difficulty')) {
            Schema::table('tips', function (Blueprint $table) {
                $table->dropIndex(['difficulty']);
                $table->dropColumn('difficulty');
            });
        }

        if (Schema::hasTable('tip_tags')) {
            DB::table('tip_tags')->where('type', 'difficulty')->delete();

            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement("ALTER TABLE tip_tags MODIFY type ENUM('trending', 'ai_recommended', 'verified', 'riding_style', 'custom') NOT NULL DEFAULT 'custom'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tips') && !Schema::hasColumn('tips', 'difficulty')) {
            Schema::table('tips', function (Blueprint $table) {
                $table->enum('difficulty', ['Mudah', 'Sedang', 'Sulit'])->nullable()->after('riding_style');
                $table->index('difficulty');
            });
        }

        if (Schema::hasTable('tip_tags') && in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE tip_tags MODIFY type ENUM('trending', 'ai_recommended', 'verified', 'difficulty', 'riding_style', 'custom') NOT NULL DEFAULT 'custom'");
        }
    }
};