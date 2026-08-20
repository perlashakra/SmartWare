<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('planned'); // planned, shipping, completed, cancelled
            $table->json('route_sequence')->nullable();   // Array output from MultiSkuRouteAggregator
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipment_id']);
            $table->dropColumn(['shipment_id', 'has_shipment']);
        });

        Schema::dropIfExists('shipments');
    }
};
