<?php

namespace App\Http\Controllers;

use App\Services\Logistics\ClientDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ClientOrderController extends Controller
{
    protected ClientDeliveryService $clientDeliveryService;

    public function __construct(ClientDeliveryService $clientDeliveryService)
    {
        $this->clientDeliveryService = $clientDeliveryService;
    }

    /**
     * Confirm delivery of goods received by the client/store.
     * POST /api/orders/{order_id}/confirm-delivery
     */
    public function confirmDelivery(Request $request, int $order_id): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer'],
            'items.*.received_quantity' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $result = $this->clientDeliveryService->confirmClientDelivery($order_id, $validated['items']);
            return response()->json($result, 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
