<?php
namespace App\Services\Logistics;

use App\Models\Inbook;
use App\Models\InbookProduct;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Section;
use App\Models\Shipment;
use App\Models\ShipmentStop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class WarehouseStopService
{
    /**
     * Process shipment stop: updates inventory bidirectionally and advances route sequence.
     */
    public function processWarehouseStop(int $shipmentId, int $stopId): array
    {
        $user = Auth::user();

        return DB::transaction(function () use ($shipmentId, $stopId, $user) {
            $shipment = Shipment::lockForUpdate()->findOrFail($shipmentId);
            $currentStop = ShipmentStop::lockForUpdate()
                ->where('shipment_id', $shipmentId)
                ->where('id', $stopId)
                ->firstOrFail();

            // 1. Worker Authorization Guard
            if ($user->employmentWarehouse_id !== $currentStop->facility_id) {
                throw new Exception("Unauthorized: You are not assigned to work at Warehouse ID {$currentStop->facility_id}.");
            }

            // 2. Stop Status Guard
            if ($currentStop->status === 'completed') {
                throw new Exception("Stop #{$currentStop->sequence_order} has already been processed.");
            }

            // 3. Sequential Route Guard
            $priorPendingStops = ShipmentStop::where('shipment_id', $shipmentId)
                ->where('sequence_order', '<', $currentStop->sequence_order)
                ->where('status', '!=', 'completed')
                ->exists();

            if ($priorPendingStops) {
                throw new Exception("Cannot process Stop #{$currentStop->sequence_order}. Earlier stops in the route are still pending.");
            }

            $warehouseId = $currentStop->facility_id;

            // --- STEP A: SUBTRACT PICKUPS FROM ORIGIN WAREHOUSE INVENTORY ---
            $pickupOrders = Order::with('items')
                ->where('shipment_id', $shipmentId)
                ->where('src_facility_id', $warehouseId)
                ->whereIn('status', ['approved', 'preparing'])
                ->lockForUpdate()
                ->get();

            foreach ($pickupOrders as $order) {
                foreach ($order->items as $item) {
                    $requiredQty = $item->quantity;

                    $inventories = Inventory::whereHas('section', fn($q) => $q->where('warehouse_id', $warehouseId))
                        ->where('product_id', $item->product_id)
                        ->where('quantity', '>', 0)
                        ->lockForUpdate()
                        ->get();

                    if ($inventories->sum('quantity') < $requiredQty) {
                        throw new Exception("Insufficient inventory in Warehouse ID {$warehouseId} for Product ID {$item->product_id}.");
                    }

                    foreach ($inventories as $inv) {
                        if ($requiredQty <= 0) break;
                        $deduct = min($inv->quantity, $requiredQty);
                        $inv->decrement('quantity', $deduct);
                        $requiredQty -= $deduct;
                    }
                }

                $order->update([
                    'departed_at' => now(),
                    'status' => 'shipping'
                ]);
            }

            // --- STEP B: ADD DROPOFFS TO DESTINATION WAREHOUSE INVENTORY ---
            $dropoffOrders = Order::with('items')
                ->where('shipment_id', $shipmentId)
                ->where('dest_facility_id', $warehouseId)
                ->where('status', 'shipping')
                ->lockForUpdate()
                ->get();

            if ($dropoffOrders->isNotEmpty()) {
                $section = Section::where('warehouse_id', $warehouseId)->first();
                if (!$section) {
                    throw new Exception("No active storage section available in Warehouse ID {$warehouseId}.");
                }

                $totalIncomingQty = 0;
                foreach ($dropoffOrders as $order) {
                    $totalIncomingQty += $order->items->sum('quantity');
                }

                $currentStock = Inventory::where('section_id', $section->id)->sum('quantity');
                if (($section->capacity - $currentStock) < $totalIncomingQty) {
                    throw new Exception("Storage capacity exceeded in section {$section->id}. Needed: {$totalIncomingQty}.");
                }

                $inbook = Inbook::create([
                    'user_id' => $user->id,
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
                            ['section_id' => $section->id, 'product_id' => $item->product_id],
                            ['unit_price' => $item->unit_price ?? 0]
                        )->increment('quantity', $item->quantity);
                    }

                    $order->update([
                        'arrived_at' => now(),
                        'status' => 'delivered'
                    ]);
                }
            }

            // 4. Close current stop
            $currentStop->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // 5. Check remaining stops
            $hasRemainingStops = ShipmentStop::where('shipment_id', $shipmentId)
                ->where('status', '!=', 'completed')
                ->exists();

            $shipmentStatus = $hasRemainingStops ? 'in_transit' : 'completed';
            $shipment->update(['status' => $shipmentStatus]);

            return [
                'message' => "Stop #{$currentStop->sequence_order} completed successfully.",
                'shipment_id' => $shipmentId,
                'completed_stop_sequence' => $currentStop->sequence_order,
                'shipment_status' => $shipmentStatus
            ];
        });
    }
}
