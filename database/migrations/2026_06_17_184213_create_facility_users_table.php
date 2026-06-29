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
        Schema::create('facility_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('users')->restrictOnDelete();
            $table->enum('role', ['super_admin', 'warehouse_admin', 'worker', 'business_owner']);
            $table->date('joined_at');
            //when a user is not working in a facility anymore we do is_active = false, so that we don't delete history.
            //we only delete the relationship with the facility
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_users');
    }
};
