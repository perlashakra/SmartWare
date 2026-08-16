<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    /**
     * Client: Confirms cart and creates orders grouped by warehouse.
     */
    public function createOrdersFromCart(int $userId, ?int $destFacilityId, array $cartItems, ?string $notes = null): array
    {
        return DB::transaction(function () use ($userId, $destFacilityId, $cartItems, $notes) {
            $productIds = collect($cartItems)->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            // Group products by warehouse (src_facility_id)
            $groupedCart = collect($cartItems)->groupBy('warehouse_id');
            $createdOrders = [];

            foreach ($groupedCart as $warehouseId => $items) {
                // Calculate expected total for this warehouse
                $totalPrice = $items->sum(function ($item) use ($products) {
                    $product = $products->get($item['product_id']);
                    return $product->price * $item['quantity'];
                });

                $order = Order::create([
                    'user_id'          => $userId,
                    'dest_facility_id' => $destFacilityId,
                    'src_facility_id'  => $warehouseId,
                    'order_type'       => $destFacilityId ? 'warehouse_restock' : 'business_purchase',
                    'expected_price'   => $totalPrice,
                    'status'           => 'pending',
                    'has_shipment'     => false,
                    'order_date'       => now()->toDateString(),
                    'notes'            => $notes ?? '',
                ]);

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $product->price,
                        'status'     => 'pending',
                    ]);
                }

                $createdOrders[] = $order->load('items');
            }

            return $createdOrders;
        });
    }

    /**
     * Client: Cancels order prior to shipment creation.
     */
    public function cancelOrderByClient(Order $order, int $userId): bool
    {
        if ($order->user_id !== $userId) {
            throw new Exception("Unauthorized to cancel this order.");
        }

        // Rule: Cannot cancel if shipment exists or if it's already in shipping/delivered states
        if ($order->has_shipment || in_array($order->status, ['shipping', 'delivered', 'cancelled'])) {
            throw new Exception("Order cannot be cancelled because a shipment has already been created or processed.");
        }

        return DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            // Mark all underlying pending/approved items as cancelled/rejected
            $order->items()
                ->whereIn('status', ['pending', 'approved'])
                ->update(['status' => 'rejected', 'rejection_reason' => 'Cancelled by client']);

            return true;
        });
    }

    /**
     * Warehouse Admin: Batch update item statuses (Approve/Reject).
     *
     * $decisions format: [
     *    ['item_id' => 12, 'status' => 'approved'],
     *    ['item_id' => 13, 'status' => 'rejected', 'reason' => 'Out of stock']
     * ]
     */
    public function processWarehouseDecision(Order $order, array $decisions): Order
    {
        return DB::transaction(function () use ($order, $decisions) {
            foreach ($decisions as $decision) {
                $item = OrderItem::where('order_id', $order->id)
                    ->where('id', $decision['item_id'])
                    ->firstOrFail();

                $item->update([
                    'status'           => $decision['status'], // 'approved' or 'rejected'
                    'rejection_reason' => $decision['reason'] ?? null,
                ]);
            }

            // Sync parent order expected price and status based on decisions
            $order->recalculateStatusAndPrice();

            return $order->fresh(['items']);
        });
    }
}
