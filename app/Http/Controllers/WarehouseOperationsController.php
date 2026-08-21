<?php
namespace App\Http\Controllers;

use App\Services\Logistics\WarehouseStopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class WarehouseOperationsController extends Controller
{
    protected WarehouseStopService $warehouseStopService;

    public function __construct(WarehouseStopService $warehouseStopService)
    {
        $this->warehouseStopService = $warehouseStopService;
    }

    /**
     * Process all pickups and drop-offs at a warehouse stop.
     * POST /api/warehouses/{facility_id}/shipments/{shipment_id}/process-stop
     */
    public function processStop(Request $request, int $facility_id, int $shipment_id): JsonResponse
    {
        try {
            $result = $this->warehouseStopService->processWarehouseStop($facility_id, $shipment_id);
            return response()->json($result, 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
