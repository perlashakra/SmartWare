<?php

namespace App\Http\Controllers;

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
    public function confirmBatches(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_summary' => 'required|array',
            'route_sequence' => 'required|array|min:1',
            'batches' => 'required|array|min:1',
            'batches.*.scheduled_at' => 'required|date|after:now',
            'batches.*.capacity_percentage' => 'required|numeric|min:1|max:100',
            'batches.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $batches = $request->input('batches');
        $routeSequence = $request->input('route_sequence');

        // Total batch capacity percentage must equal 100%
        $totalPercentage = array_sum(array_column($batches, 'capacity_percentage'));
        if (abs($totalPercentage - 100.0) > 0.01) {
            return response()->json([
                'status' => 'error',
                'message' => 'The combined capacity percentage across all batches must equal exactly 100%.'
            ], 422);
        }

        // Persist the confirmed plan and its scheduled batches within a database transaction
        DB::beginTransaction();
        try {
            // Example DB logic — adjust model names according to your schema:
            /*
            $masterPlan = MasterShipmentPlan::create([
                'total_stops' => count($routeSequence),
                'route_payload' => json_encode($routeSequence),
                'status' => 'confirmed',
            ]);

            foreach ($batches as $index => $batch) {
                $masterPlan->shipmentBatches()->create([
                    'batch_number' => $index + 1,
                    'scheduled_at' => $batch['scheduled_at'],
                    'capacity_percentage' => $batch['capacity_percentage'],
                    'notes' => $batch['notes'] ?? null,
                    'status' => 'scheduled',
                ]);
            }
            */

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Shipment plan successfully confirmed and split into ' . count($batches) . ' batch(es).',
                'data' => [
                    'total_batches' => count($batches),
                    'scheduled_batches' => $batches
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to persist shipment plan: ' . $e->getMessage()
            ], 500);
        }
    }
}
