<?php

namespace App\Services\Logistics;

use App\Models\Inbook;
use App\Models\InbookProduct;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Section;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class WarehouseStopService
{
    /**
     * Process all pickups (loading) and drop-offs (unloading) at a warehouse stop.
     *
     * @param int $warehouseId The ID of the warehouse where the worker is stationed
     * @param int $shipmentId  The ID of the shipment passing through
     */
    public function processWarehouseStop(int $warehouseId, int $shipmentId): array
    {
        return DB::transaction(function () use ($warehouseId, $shipmentId) {
            $shipment = Shipment::lockForUpdate()->findOrFail($shipmentId);

            if ($shipment->status === 'completed' || $shipment->status === 'cancelled') {
                throw new Exception("Shipment is already {$shipment->status}.");
            }

            // Find all restock orders where this warehouse is EITHER the source (pickup) OR destination (drop-off)
            $pickupOrders = Order::with('items')
                ->where('shipment_id', $shipmentId)
                ->where('src_facility_id', $warehouseId)
                ->where('order_type', 'warehouse_restock')
                ->whereIn('status', ['approved', 'preparing'])
                ->lockForUpdate()
                ->get();

            $dropoffOrders = Order::with('items')
                ->where('shipment_id', $shipmentId)
                ->where('dest_facility_id', $warehouseId)
                ->where('order_type', 'warehouse_restock')
                ->where('status', 'shipping')
                ->lockForUpdate()
                ->get();

            if ($pickupOrders->isEmpty() && $dropoffOrders->isEmpty()) {
                throw new Exception("No active pickups or drop-offs found for Warehouse ID {$warehouseId} on Shipment {$shipmentId}.");
            }

            // ==========================================
            // STEP A: HANDLE PICKUPS (Deduct from Warehouse)
            // ==========================================
            foreach ($pickupOrders as $order) {
                foreach ($order->items as $item) {
                    $requiredQty = $item->quantity;

                    // Query available inventories in this warehouse sections
                    $inventories = Inventory::whereHas('section', fn($q) => $q->where('warehouse_id', $warehouseId))
                        ->where('product_id', $item->product_id)
                        ->where('quantity', '>', 0)
                        ->lockForUpdate()
                        ->get();

                    $totalAvailable = $inventories->sum('quantity');
                    if ($totalAvailable < $requiredQty) {
                        throw new Exception("Insufficient inventory in Warehouse {$warehouseId} for Product ID {$item->product_id}. Required: {$requiredQty}, Available: {$totalAvailable}.");
                    }

                    // Deduct stock across section inventories
                    foreach ($inventories as $inv) {
                        if ($requiredQty <= 0) break;

                        $deduct = min($inv->quantity, $requiredQty);
                        $inv->decrement('quantity', $deduct);
                        $requiredQty -= $deduct;
                    }
                }

                $order->update([
                    'departed_at' => now(),
                    'status' => 'shipping',
                ]);
            }

            // ==========================================
            // STEP B: HANDLE DROP-OFFS (Add to Warehouse)
            // ==========================================
            if ($dropoffOrders->isNotEmpty()) {
                $section = Section::where('warehouse_id', $warehouseId)->first();
                if (!$section) {
                    throw new Exception("No storage section available in Warehouse ID {$warehouseId}.");
                }

                // Calculate incoming volume vs. available capacity
                $totalIncomingQty = $dropoffOrders->flatMap->items->sum('quantity');
                $currentStock = Inventory::where('section_id', $section->id)->sum('quantity');
                $availableCapacity = $section->capacity - $currentStock;

                if ($availableCapacity < $totalIncomingQty) {
                    throw new Exception("Insufficient storage capacity in section {$section->id}. Required space: {$totalIncomingQty}, Available: {$availableCapacity}.");
                }

                $inbook = Inbook::create([
                    'user_id' => Auth::id(),
                    'storage_date' => now()->toDateString(),
                ]);

                foreach ($dropoffOrders as $order) {
                    foreach ($order->items as $item) {
                        InbookProduct::create([
                            'inbook_id' => $inbook->id,
                            'product_id' => $item->product_id,
                            'section_id' => $section->id,
                            'quantity' => $item->quantity,
                        ]);

                        Inventory::updateOrCreate(
                            [
                                'section_id' => $section->id,
                                'product_id' => $item->product_id,
                            ],
                            [
                                'unit_price' => $item->unit_price ?? 0,
                            ]
                        )->increment('quantity', $item->quantity);
                    }

                    $order->update([
                        'arrived_at' => now(),
                        'status' => 'delivered',
                    ]);
                }
            }

            // Check if all orders attached to this shipment are delivered
            $this->checkAndUpdateShipmentCompletion($shipmentId);

            return [
                'message' => 'Warehouse stop processed successfully.',
                'shipment_id' => $shipmentId,
                'pickups_processed' => $pickupOrders->count(),
                'dropoffs_processed' => $dropoffOrders->count(),
            ];
        });
    }

    private function checkAndUpdateShipmentCompletion(int $shipmentId): void
    {
        $hasPending = Order::where('shipment_id', $shipmentId)
            ->where('status', '!=', 'delivered')
            ->exists();

        if (!$hasPending) {
            Shipment::where('id', $shipmentId)->update(['status' => 'completed']);
        }
    }
}
