<?php

namespace App\Services\Orders;

use App\Models\Cart;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    /**
     * Client: Confirms cart and creates orders grouped by warehouse.
     * Fetches unit price directly from warehouse inventory.
     */
    public function createOrdersFromCart(int $userId, ?int $destFacilityId, array $cartItems, ?string $notes = null, ?int $cartId = null): array
    {
        return DB::transaction(function () use ($userId, $destFacilityId, $cartItems, $notes, $cartId) {
            $groupedCart = collect($cartItems)->groupBy('warehouse_id');
            $createdOrders = [];

            foreach ($groupedCart as $warehouseId => $items) {
                $productIds = $items->pluck('product_id')->unique();

                // Retrieve inventory pricing for products within this specific warehouse's sections
                $inventories = Inventory::whereIn('product_id', $productIds)
                    ->whereHas('section', function ($query) use ($warehouseId) {
                        $query->where('warehouse_id', $warehouseId);
                    })
                    ->get()
                    ->groupBy('product_id');

                // Map product IDs to their warehouse unit prices (picks the max price if stocked in multiple sections)
                $productPrices = [];
                foreach ($productIds as $productId) {
                    $itemInventories = $inventories->get($productId);

                    if (!$itemInventories || $itemInventories->isEmpty()) {
                        throw new Exception("Product ID {$productId} is not currently stocked in warehouse ID {$warehouseId}.");
                    }

                    $productPrices[$productId] = $itemInventories->max('unit_price');
                }

                // Calculate subtotal for this warehouse
                $totalPrice = $items->sum(function ($item) use ($productPrices) {
                    return $productPrices[$item['product_id']] * $item['quantity'];
                });

                $order = Order::create([
                    'user_id'          => $userId,
                    'dest_facility_id' => $destFacilityId,
                    'src_facility_id'  => $warehouseId,
                    'order_type'       => 'business_purchase',
                    'expected_price'   => $totalPrice,
                    'status'           => 'pending',
                    'has_shipment'     => false,
                    'order_date'       => now()->toDateString(),
                    'notes'            => $notes ?? '',
                ]);

                foreach ($items as $item) {
                    $unitPrice = $productPrices[$item['product_id']];

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'status'     => 'pending',
                    ]);
                }

                $createdOrders[] = $order->load('products.product');
            }

            // Clear stored cart if ID provided
            if ($cartId) {
                $cart = Cart::where('id', $cartId)->where('user_id', $userId)->first();
                if ($cart) {
                    $cart->items()->delete();
                    $cart->delete();
                }
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

        if ($order->has_shipment || in_array($order->status, ['shipping', 'delivered', 'cancelled'])) {
            throw new Exception("Order cannot be cancelled because a shipment has already been created or processed.");
        }

        return DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            $order->products()
                ->whereIn('status', ['pending', 'approved'])
                ->update([
                    'status'           => 'rejected',
                    'rejection_reason' => 'Cancelled by client'
                ]);

            return true;
        });
    }

    /**
     * Warehouse Admin: Batch update item statuses (Approve/Reject).
     */
    /**
     * Warehouse Admin: Batch update item statuses (Approve/Reject).
     */
    public function processWarehouseDecision(Order $order, array $decisions): Order
    {
        return DB::transaction(function () use ($order, $decisions) {
            foreach ($decisions as $decision) {
                // Search strictly within the order's items relationship
                $item = $order->products()
                    ->where('id', $decision['item_id'])
                    ->first();

                if (!$item) {
                    throw new Exception("Order item ID {$decision['item_id']} does not belong to Order ID {$order->id}.");
                }

                $item->update([
                    'status'           => $decision['status'],
                    'rejection_reason' => $decision['reason'] ?? null,
                ]);
            }

            // Recalculate parent order expected price and overall status
            $order->recalculateStatusAndPrice();

            return $order->fresh(['products.product']);
        });
    }

    /**
     * Warehouse Manager: Creates an internal stock transfer between two owned warehouses.
     * Items are automatically approved upon creation.
     */
    /**
     * Warehouse Manager: Creates an internal stock transfer between two owned warehouses.
     * Items are automatically approved upon creation after verifying stock availability.
     */
    public function createWarehouseTransfer(int $userId, int $srcFacilityId, int $destFacilityId, array $items, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($userId, $srcFacilityId, $destFacilityId, $items, $notes) {
            $productIds = collect($items)->pluck('product_id')->unique();

            // Retrieve inventory records across all sections for the source warehouse
            $inventories = Inventory::whereIn('product_id', $productIds)
                ->whereHas('section', function ($query) use ($srcFacilityId) {
                    $query->where('warehouse_id', $srcFacilityId);
                })
                ->get()
                ->groupBy('product_id');

            $productPrices = [];

            // Verify stock availability and collect pricing for each requested item
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $requestedQty = $item['quantity'];

                $itemInventories = $inventories->get($productId);

                if (!$itemInventories || $itemInventories->isEmpty()) {
                    throw new Exception("Product ID {$productId} is not currently stocked in source warehouse ID {$srcFacilityId}.");
                }

                // Sum available stock across all sections in this warehouse
                $totalAvailableQuantity = $itemInventories->sum('quantity');

                if ($totalAvailableQuantity < $requestedQty) {
                    throw new Exception("Insufficient stock for Product ID {$productId} in warehouse ID {$srcFacilityId}. Available: {$totalAvailableQuantity}, Requested: {$requestedQty}.");
                }

                // Use the maximum unit price recorded across the warehouse's sections
                $productPrices[$productId] = $itemInventories->max('unit_price');
            }

            // Calculate expected total price
            $totalPrice = collect($items)->sum(function ($item) use ($productPrices) {
                return $productPrices[$item['product_id']] * $item['quantity'];
            });

            // Create the Order
            $order = Order::create([
                'user_id'          => $userId,
                'src_facility_id'  => $srcFacilityId,
                'dest_facility_id' => $destFacilityId,
                'order_type'       => 'warehouse_restock',
                'expected_price'   => $totalPrice,
                'status'           => 'approved', // Auto-approved for internal transfers
                'has_shipment'     => false,
                'order_date'       => now()->toDateString(),
                'notes'            => $notes ?? 'Internal warehouse transfer request',
            ]);

            // Create Order Items marked as 'approved'
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $productPrices[$item['product_id']],
                    'status'     => 'approved',
                ]);
            }

            $order->recalculateStatusAndPrice();

            return $order->load(['products.product', 'warehouseOfTheOrder']);
        });
    }
}
