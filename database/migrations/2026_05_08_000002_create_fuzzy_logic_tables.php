<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motor_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('component_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_type_id')->constrained('motor_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('good');
            $table->integer('warn')->default(1500);
            $table->integer('critical')->default(2500);
            $table->integer('reset_interval')->default(90);
            // JSON default sering bermasalah di MySQL versi lama; set dari seeder/app.
            $table->json('active_vars')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_custom')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['motor_type_id', 'name']);
            $table->index(['motor_type_id', 'is_active']);
        });

        Schema::create('fuzzy_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_config_id')->constrained('component_configs')->cascadeOnDelete();
            $table->string('var_key');

            $table->float('low_a')->default(0);
            $table->float('low_b')->default(0);
            $table->float('low_c')->default(800);

            $table->float('med_a')->default(600);
            $table->float('med_b')->default(1000);
            $table->float('med_c')->default(1500);

            $table->float('high_a')->default(1200);
            $table->float('high_b')->default(2000);
            $table->float('high_c')->default(3000);

            $table->timestamps();

            $table->unique(['component_config_id', 'var_key']);
        });

        Schema::create('fuzzy_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_config_id')->constrained('component_configs')->cascadeOnDelete();
            $table->string('var1');
            $table->string('label1');
            $table->string('operator')->default('AND');
            $table->string('var2');
            $table->string('label2');
            $table->string('output');
            $table->float('weight')->default(1.0);
            $table->timestamps();
        });

        Schema::create('fuzzy_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('component_name');
            $table->string('motor_type');
            $table->string('field_changed');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuzzy_audit_logs');
        Schema::dropIfExists('fuzzy_rules');
        Schema::dropIfExists('fuzzy_variables');
        Schema::dropIfExists('component_configs');
        Schema::dropIfExists('motor_types');
    }
};
