<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jenis motor (matic, manual, sport, adventure)
        Schema::create('motor_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // "Matic"
            $table->string('slug');           // "matic"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Komponen per jenis motor
        Schema::create('component_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');                      // "Oli Mesin"
            $table->string('status')->default('good');   // good|warning|critical
            $table->integer('warn')->default(1500);      // km
            $table->integer('critical')->default(2500);  // km
            $table->integer('reset_interval')->default(90); // hari
            $table->json('active_vars')->default('["jarak"]');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_custom')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Membership function per variabel per komponen
        Schema::create('fuzzy_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_config_id')->constrained()->cascadeOnDelete();
            $table->string('var_key');   // jarak|durasi|kecepatan|intensitas
            // Low [a,b,c]
            $table->float('low_a')->default(0);
            $table->float('low_b')->default(0);
            $table->float('low_c')->default(800);
            // Medium [a,b,c]
            $table->float('med_a')->default(600);
            $table->float('med_b')->default(1000);
            $table->float('med_c')->default(1500);
            // High [a,b,c]
            $table->float('high_a')->default(1200);
            $table->float('high_b')->default(2000);
            $table->float('high_c')->default(3000);
            $table->timestamps();
        });

        // Rule base per komponen
        Schema::create('fuzzy_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_config_id')->constrained()->cascadeOnDelete();
            $table->string('var1');           // jarak
            $table->string('label1');         // high|medium|low
            $table->string('operator')->default('AND'); // AND|OR
            $table->string('var2');           // durasi
            $table->string('label2');         // high|medium|low
            $table->string('output');         // Baik|Perlu Servis|Kritis
            $table->float('weight')->default(1.0);
            $table->timestamps();
        });

        // Cache hasil rekomendasi Gemini
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('motor_id');
            $table->string('fingerprint')->index();
            $table->json('content');
            $table->integer('reuse_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        // Audit log perubahan konfigurasi
        Schema::create('fuzzy_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('component_name');
            $table->string('motor_type');
            $table->string('field_changed');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuzzy_audit_logs');
        Schema::dropIfExists('ai_recommendations');
        Schema::dropIfExists('fuzzy_rules');
        Schema::dropIfExists('fuzzy_variables');
        Schema::dropIfExists('component_configs');
        Schema::dropIfExists('motor_types');
    }
};
