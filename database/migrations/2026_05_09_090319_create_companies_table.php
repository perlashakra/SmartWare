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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->constrained('addresses')->restrictOnDelete();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('website');
            $table->enum('translation_status', ['pending', 'failed', 'successful'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
