<?php

namespace App\Http\Controllers;

use App\Services\Logistics\WarehouseStopService;
use Illuminate\Http\JsonResponse;
use Exception;

class WarehouseOperationsController extends Controller
{
    protected WarehouseStopService $warehouseStopService;

    public function __construct(WarehouseStopService $warehouseStopService)
    {
        $this->warehouseStopService = $warehouseStopService;
    }

    public function processStop(int $shipment_id, int $stop_id): JsonResponse
    {
        try {
            $result = $this->warehouseStopService->processWarehouseStop($shipment_id, $stop_id);
            return response()->json($result, 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
