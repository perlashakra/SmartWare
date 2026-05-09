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
        Schema::create('client_order_reciept_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_order_reciept_id')->constrained('client_order_reciepts')->cascadeOnDelete();
            $table->foreignId('inbook_product_id')->constrained('inbook_products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_order_reciept_products');
    }
};
