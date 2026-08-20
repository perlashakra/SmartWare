<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dest_facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->foreignId('src_facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('order_type', ['warehouse_restock', 'business_purchase']);
            $table->decimal('expected_price', 12, 2)->default(0.00);

            // Order level tracking
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'preparing',
                'shipping',
                'delivered'
            ])->default('pending');
            $table->string('rejection_reason')->nullable();

            $table->boolean('has_shipment')->default(false); // Quick guard for client cancellations
            $table->date('order_date');
            $table->text('notes')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('delivery_confirmed_at')->nullable();
            $table->text('delivery_issue')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
