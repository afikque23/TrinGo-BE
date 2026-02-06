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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable'); // Already creates index automatically
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('filename', 255);
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->integer('size_bytes')->unsigned();
            $table->string('disk', 50)->default('local');
            $table->string('document_type', 50)->nullable()->comment('e.g., receipt, photo, manual');
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Note: morphs() already creates the index, so we don't need to add it again
            $table->index('uploaded_by');
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
