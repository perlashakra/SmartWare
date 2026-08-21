<?php

namespace App\Services\Logistics;

use App\Models\Order;
use App\Models\ShipmentStop;
use Illuminate\Support\Facades\Auth;
use Exception;

class ShipmentTrackingService
{
    /**
     * Get real-time status and remaining stop count for a client's order.
     */
    public function getClientOrderTracking(int $orderId): array
    {
        $order = Order::with(['shipment', 'srcFacility', 'destFacility'])->findOrFail($orderId);

        if ($order->user_id !== Auth::id()) {
            throw new Exception("Unauthorized access to Order ID {$orderId}.");
        }

        if (!$order->shipment_id) {
            return [
                'order_id' => $orderId,
                'status' => $order->status,
                'message' => 'Order is awaiting shipment assignment.',
                'remaining_stops' => null
            ];
        }

        // Find the destination stop on this route
        $destStop = ShipmentStop::where('shipment_id', $order->shipment_id)
            ->where('facility_id', $order->dest_facility_id)
            ->first();

        if (!$destStop) {
            throw new Exception("Destination stop not found on the assigned shipment route.");
        }

        // Find the current active stop (the lowest uncompleted sequence order)
        $currentActiveStop = ShipmentStop::where('shipment_id', $order->shipment_id)
            ->where('status', '!=', 'completed')
            ->orderBy('sequence_order', 'asc')
            ->first();

        // If no uncompleted stops remain, the delivery is at its final state
        if (!$currentActiveStop) {
            $remainingStops = 0;
        } else {
            // Stops remaining = Destination Stop Sequence - Current Active Stop Sequence
            $remainingStops = max(0, $destStop->sequence_order - $currentActiveStop->sequence_order);
        }

        return [
            'order_id' => $order->id,
            'order_status' => $order->status,
            'shipment_status' => $order->shipment->status,
            'origin_facility' => $order->srcFacility->name ?? null,
            'destination_facility' => $order->destFacility->name ?? null,
            'current_active_stop_sequence' => $currentActiveStop->sequence_order ?? $destStop->sequence_order,
            'destination_stop_sequence' => $destStop->sequence_order,
            'remaining_stops' => $remainingStops,
            'eta_message' => $remainingStops === 0
                ? 'Shipment has arrived or is at your facility.'
                : "Your shipment is {$remainingStops} " . ($remainingStops === 1 ? 'stop' : 'stops') . " away."
        ];
    }
}
