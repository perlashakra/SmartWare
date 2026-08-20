<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
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
        $validator = Validator::make($request->all(), [
            'selected_items' => 'required|array|min:1',
            'selected_items.*.sku_id' => 'required|string',
            'selected_items.*.inventory' => 'required|array',
            'selected_items.*.demands' => 'required|array',
            'distance_matrix' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $selectedItems = $request->input('selected_items');
        $distanceMatrix = $request->input('distance_matrix');

        // Execute optimization engine
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
