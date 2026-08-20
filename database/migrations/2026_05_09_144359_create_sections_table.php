<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('facilities')->restrictOnDelete();
            //$table->foreignId('company_id')->nullable()->constrained('companies')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->string('name');
            $table->string('capacity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
