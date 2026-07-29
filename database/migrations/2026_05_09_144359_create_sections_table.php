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
        //this one
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('name');
            $table->integer('parent_id')->nullable();
            $table->string('capacity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
