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
        Schema::create('employee_announcements', function (Blueprint $table) {
            $table->id();
            $table->ForeignId('employmentWarehouse_id')->constrained('facilities')->cascadeOnDelete();
            $table->ForeignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('national_id')->unique();
            $table->boolean('claimed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_announcements');
    }
};
