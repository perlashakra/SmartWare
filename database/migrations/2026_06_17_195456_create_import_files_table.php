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
        Schema::create('import_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            //$table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            //$table->string('file_hash', 64);
            $table->timestamp('uploaded_at');
            $table->enum('status', ['processing', 'success', 'failed'])->default('processing');
            //$table->unique(['facility_id', 'section_id', 'file_hash']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_files');
    }
};
