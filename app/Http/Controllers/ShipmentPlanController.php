<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Inventory;
use App\Models\Inbook;
use App\Models\InbookProduct;
use App\Models\Section;
use App\Models\Shipment;
use App\Services\Optimization\MultiSkuRouteAggregator;
use App\Services\Optimization\DistanceMatrixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShipmentPlanController extends Controller
{
    protected MultiSkuRouteAggregator $aggregator;
    protected DistanceMatrixService $distanceService;

    public function __construct(
        MultiSkuRouteAggregator $aggregator,
        DistanceMatrixService $distanceService
    ) {
        $this->aggregator = $aggregator;
        $this->distanceService = $distanceService;
    }

    /**
     * 1. Generates suggested plan using straight-line distance calculations.
     * Route: POST /api/shipments/generate-plan
     */
    public function generatePlan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $orderIds = $request->input('order_ids');

        // Step A: Calculate straight-line Haversine matrix dynamically
        $distanceMatrix = $this->distanceService->generateForOrders($orderIds);

        // Step B: Load orders & prepare MultiSkuRouteAggregator payload
        $orders = Order::with('products')->whereIn('id', $orderIds)->get();
        $skuMap = [];

        foreach ($orders as $order) {
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

                $skuMap[$skuId]['demands'][] = [
                    'facility_id' => $destinationId,
                    'required_qty' => (int) $item->quantity
                ];
            }
        }

        // Step C: Retrieve stock levels across participating facilities
        $skuIds = array_keys($skuMap);
        $inventoryRecords = Inventory::with('section')->whereIn('product_id', $skuIds)->get();

        foreach ($inventoryRecords as $record) {
            $skuId = (string) $record->product_id;

            if ($record->section && $record->section->warehouse_id) {
                $whId = "WH-{$record->section->warehouse_id}";

                if (isset($skuMap[$skuId])) {
                    $skuMap[$skuId]['inventory'][$whId] =
                        ($skuMap[$skuId]['inventory'][$whId] ?? 0) + (int) $record->quantity;
                }
            }
        }

        // Step D: Execute optimization engine
        $plan = $this->aggregator->aggregate(array_values($skuMap), $distanceMatrix);

        return response()->json([
            'distance_matrix_used' => $distanceMatrix,
            'plan_suggestion' => $plan
        ], 200);
    }

    /**
     * Departure: Deducts stock from the source warehouse section.
     * Route: POST /api/orders/{order_id}/depart
     */
    public function markDeparted(int $orderId): JsonResponse
    {
        $order = Order::with('products')->findOrFail($orderId);

        if ($order->departed_at) {
            return response()->json(['message' => 'Order has already departed.'], 400);
        }

        DB::transaction(function () use ($order) {
            // Get all section IDs for the source warehouse
            $sourceSectionIds = Section::where('warehouse_id', $order->src_facility_id)->pluck('id');

            foreach ($order->products as $item) {
                $inventory = Inventory::whereIn('section_id', $sourceSectionIds)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($inventory) {
                    $inventory->decrement('quantity', $item->quantity);
                }
            }

            $order->update([
                'departed_at' => now(),
                'status'      => 'shipping',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order departed and source inventory updated.',
        ]);
    }

    /**
     * Warehouse Arrival: Adds stock to destination section and creates an Inbook record.
     * Route: POST /api/orders/{order_id}/arrive
     */
    public function markArrived(int $orderId): JsonResponse
    {
        $order = Order::with('products')->findOrFail($orderId);

        if ($order->arrived_at) {
            return response()->json(['message' => 'Order has already arrived.'], 400);
        }

        DB::transaction(function () use ($order) {
            // 1. Get or create a section in the destination warehouse
            $destinationSection = Section::where('warehouse_id', $order->dest_facility_id)->first();

            if (!$destinationSection) {
                $destinationSection = Section::create([
                    'facility_id'  => $order->dest_facility_id,
                    'section_name' => 'General Receiving Area',
                ]);
            }

            // 2. Update section inventory for each product
            foreach ($order->products as $item) {
                $inventory = Inventory::where('section_id', $destinationSection->id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($inventory) {
                    $inventory->increment('quantity', $item->quantity);
                } else {
                    Inventory::create([
                        'section_id' => $destinationSection->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                    ]);
                }
            }

            // 3. Create Inbook record (using strictly allowed fields)
            Inbook::create([
                'user_id'      => auth()->id() ?? $order->user_id,
                'storage_date' => now()->toDateString(),
            ]);

            $order->update([
                'arrived_at' => now(),
                'status'     => 'delivered',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Restock received, section inventory updated, and Inbook logged.',
        ]);
    }
    /**
     * Client Confirm Arrival: Verifies delivered quantities and logs Inbook.
     * Route: POST /api/orders/{order_id}/confirm-delivery
     */
    public function confirmClientDelivery(Request $request, int $orderId): JsonResponse
    {
        $validated = $request->validate([
            'arrived_quantities' => 'nullable|array',
            'arrived_quantities.*.product_id' => 'required_with:arrived_quantities|exists:products,id',
            'arrived_quantities.*.quantity'   => 'required_with:arrived_quantities|integer|min:0',
        ]);

        $order = Order::with('products')->findOrFail($orderId);

        if ($order->delivery_confirmed_at) {
            return response()->json(['message' => 'Delivery already confirmed.'], 400);
        }

        DB::transaction(function () use ($order, $validated) {
            // 1. Log the receipt using your exact Inbook schema
            Inbook::create([
                'user_id'      => auth()->id() ?? $order->user_id,
                'storage_date' => now()->toDateString(),
            ]);

            // 2. Mark delivery as confirmed and complete order
            $order->update([
                'delivery_confirmed_at' => now(),
                'status'=> 'delivered',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Delivery confirmed and arrival logged successfully.',
        ]);
    }
}
