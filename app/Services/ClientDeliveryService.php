<?php

namespace App\Services\Logistics;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ClientDeliveryService
{
    /**
     * Confirm delivery of goods received by the client/store.
     *
     * @param int $orderId
     * @param array $receivedItems Array of ['order_item_id' => int, 'received_quantity' => int]
     */
    public function confirmClientDelivery(int $orderId, array $receivedItems): array
    {
        return DB::transaction(function () use ($orderId, $receivedItems) {
            $order = Order::with('items')
                ->where('id', $orderId)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->order_type !== 'business_purchase') {
                throw new Exception("Only client business purchases can be confirmed using this method.");
            }

            if ($order->status !== 'shipping') {
                throw new Exception("Order cannot be confirmed because it is not currently in shipping status.");
            }

            $hasMissingStock = false;
            $issues = [];

            foreach ($receivedItems as $receivedItem) {
                $orderItem = $order->items->firstWhere('id', $receivedItem['order_item_id']);

                if (!$orderItem) {
                    throw new Exception("Invalid order item ID {$receivedItem['order_item_id']} provided for Order {$orderId}.");
                }

                if ($receivedItem['received_quantity'] > $orderItem->quantity) {
                    throw new Exception("Received quantity cannot exceed ordered quantity for Product ID {$orderItem->product_id}.");
                }

                $orderItem->update(['received_quantity' => $receivedItem['received_quantity']]);

                if ($receivedItem['received_quantity'] < $orderItem->quantity) {
                    $hasMissingStock = true;
                    $issues[] = [
                        'order_item_id' => $orderItem->id,
                        'product_id' => $orderItem->product_id,
                        'ordered' => $orderItem->quantity,
                        'received' => $receivedItem['received_quantity'],
                        'missing' => $orderItem->quantity - $receivedItem['received_quantity'],
                    ];
                }
            }

            $order->update([
                'status' => 'delivered',
                'arrived_at' => now(),
                'delivery_confirmed_at' => now(),
                'delivery_issue' => $hasMissingStock ? 'Missing stock reported by client.' : null,
            ]);

            // Check if all orders attached to this shipment are delivered
            if ($order->shipment_id) {
                $hasPending = Order::where('shipment_id', $order->shipment_id)
                    ->where('status', '!=', 'delivered')
                    ->exists();

                if (!$hasPending) {
                    Shipment::where('id', $order->shipment_id)->update(['status' => 'completed']);
                }
            }

            return [
                'message' => $hasMissingStock
                    ? 'Delivery confirmed with missing stock reported.'
                    : 'Delivery confirmed successfully.',
                'order_id' => $order->id,
                'status' => 'delivered',
                'has_missing_stock' => $hasMissingStock,
                'issues' => $issues,
            ];
        });
    }
}
