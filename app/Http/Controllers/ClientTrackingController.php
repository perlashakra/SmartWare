<?php

namespace App\Http\Controllers;

use App\Services\Logistics\ShipmentTrackingService;
use Illuminate\Http\JsonResponse;
use Exception;

class ClientTrackingController extends Controller
{
    protected ShipmentTrackingService $trackingService;

    public function __construct(ShipmentTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public function trackOrder(int $order_id): JsonResponse
    {
        try {
            $result = $this->trackingService->getClientOrderTracking($order_id);
            return response()->json($result, 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
