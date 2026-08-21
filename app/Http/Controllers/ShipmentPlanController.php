<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\Inventory;
use App\Services\Optimization\MultiSkuRouteAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShipmentPlanController extends Controller
{
    protected MultiSkuRouteAggregator $aggregator;

    public function __construct(MultiSkuRouteAggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * Generate or Re-calculate the primary shipment plan.
     * Route: POST /api/shipments/generate-plan
     */
    public function generatePlan(Request $request): JsonResponse
    {
        // 1. Validate inputs (order_ids replacing selected_items)
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'distance_matrix' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $orderIds = $request->input('order_ids');
        $distanceMatrix = $request->input('distance_matrix');

        // 2. Load orders with their item relationships
        $orders = Order::with('products')->whereIn('id', $orderIds)->get();

        // 3. Transform database entities into the structure MultiSkuRouteAggregator expects
        $skuMap = [];

        foreach ($orders as $order) {
            // Map target destination based on order type
            $destinationId = ($order->order_type === 'warehouse_restock')
                ? "WH-{$order->dest_facility_id}"
                : "CLIENT-{$order->user_id}";

            foreach ($order->products as $item) {
                $skuId = (string) $item->product_id;

                if (!isset($skuMap[$skuId])) {
                    $skuMap[$skuId] = [
                        'sku_id' => $skuId,
                        'demands' => [],
                        'inventory' => []
                    ];
                }

                // Aggregate demand per destination
                $skuMap[$skuId]['demands'][] = [
                    'facility_id' => $destinationId,
                    'required_qty' => (int) $item->quantity
                ];
            }
        }

        // 4. Retrieve stock across all relevant warehouses for the requested SKUs
        $skuIds = array_keys($skuMap);

        // Adjust this query based on your actual Inventory table schema
        $inventoryRecords = Inventory::whereIn('product_id', $skuIds)->get();

        foreach ($inventoryRecords as $record) {
            $skuId = (string) $record->product_id;
            $whId = "WH-{$record->warehouse_id}";

            if (isset($skuMap[$skuId])) {
                $skuMap[$skuId]['inventory'][$whId] = (int) $record->quantity;
            }
        }

        $selectedItems = array_values($skuMap);

        // 5. Execute optimization engine
        $plan = $this->aggregator->aggregate($selectedItems, $distanceMatrix);

        return response()->json($plan, 200);
    }

    /**
     * Finalize and split confirmed plan into scheduled shipment batches.
     * Route: POST /api/shipments/confirm-batches
     */
    // Route::post('/api/shipments/confirm-plan', [ShipmentPlanController::class, 'confirmPlan']);

    public function confirmPlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1|exists:orders,id',
            'route_sequence' => 'required|array',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Create parent Shipment
            $shipment = Shipment::create([
                'status' => 'planned',
                'route_sequence' => $validated['route_sequence'],
            ]);

            // 2. Attach selected orders and update status
            Order::whereIn('id', $validated['order_ids'])->update([
                'shipment_id' => $shipment->id,
                'has_shipment' => true,
                'status' => 'preparing',
            ]);

            return response()->json([
                'message' => 'Shipment confirmed and orders updated.',
                'shipment_id' => $shipment->id
            ], 201);
        });
    }
}
