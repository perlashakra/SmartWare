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
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->constrained('addresses')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('facility_name')->nullable();
            $table->string('facility_type');
            $table->enum('facility_status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->enum('business_type', ['warehouse', 'restaurant', 'pharmacy', 'clothing_store', 'electronics_store', 'supermarket', 'makeup_store', 'furniture_store']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
