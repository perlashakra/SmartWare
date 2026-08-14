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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            //either company or user only one could be null
            //client, warehouse
            //this One
            $table->foreignId('dest_facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->foreignId('src_facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('order_type',['warehouse_restock', 'business_purchase']);
            $table->float('expected_price');//this is a derived attri
            $table->enum('status', ['pending', 'approved', 'cancelled', 'preparing', 'shipping', 'delivered'])->nullable();
            $table->date('order_date');
            $table->string('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_orders');
    }
};
