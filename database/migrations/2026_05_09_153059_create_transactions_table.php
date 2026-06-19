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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->nullable()->constrained('facilities')->restrictOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained('facilities')->restrictOnDelete();
            $table->enum('transaction_type', ['inbound', 'outbound', 'transfer', 'return']);
            $table->timestamp('transaction_date');
            $table->enum('status', ['pending', 'approved', 'cancelled', 'preparing', 'shipping', 'delivered'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
