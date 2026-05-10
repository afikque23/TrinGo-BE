<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuzzy_config_audits', function (Blueprint $table) {
            $table->id();

            $table->string('motor_type');
            $table->foreignId('motor_type_component_id')
                ->constrained('motor_type_components')
                ->cascadeOnDelete();

            $table->foreignId('maintenance_component_id')
                ->nullable()
                ->constrained('maintenance_components')
                ->nullOnDelete();

            $table->string('admin_email')->nullable();
            $table->string('component_label')->nullable();
            $table->string('changed_field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            $table->timestamps();

            $table->index(['motor_type', 'created_at']);
            $table->index(['motor_type_component_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuzzy_config_audits');
    }
};
