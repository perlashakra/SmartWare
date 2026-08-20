<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    //
    public function clientOrders()
    {
        $user = Auth::user();
    
        $orders = Order::with([
            'warehouseOfTheOrder',
            'destination',
            'products.product',
        ])
            ->where('user_id', $user->id)
            ->latest('order_date')
            ->get()
            ->map(function ($order) {
                $warehouse = $order->warehouseOfTheOrder;
                return [
                    'order_id' => $order->id,
    
                    'status' => $order->status,
    
                    'has_shipment' => $order->has_shipment,
    
                    'order_date' => $order->order_date,
    
                    'departed_at' => $order->departed_at,
    
                    'arrived_at' => $order->arrived_at,
    
                    'delivery_confirmed_at' =>
                        $order->delivery_confirmed_at,
    
                    'warehouse' => $warehouse
                        ? [
                            'id' => $warehouse->id,
                            'name' => $warehouse->facility_name_en,
                        ]
                        : null,
    
                    'products' => $order->products->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name_en,
                            'quantity' => $item->quantity,
                            'status' => $item->status,
                        ];
                    }),
                ];
            });
    
        return response()->json([
            'data' => $orders,
        ], 200);
    }

    public function clientOrder($order_id)
    {
        $order = Order::with([
            'warehouseOfTheOrder',
            'destination',
            'products.product',
        ])
            ->where('id', $order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'data' => $order,
        ], 200);
    }

    public function confirmDelivery(Request $request, $order_id)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
    
            'items.*.order_item_id' =>
                ['required', 'integer'],
    
            'items.*.received_quantity' =>
                ['required', 'integer', 'min:0'],
        ]);
    
    
        return DB::transaction(function () use (
            $validated,
            $order_id
        ) {
    
            $order = Order::with('products')
                ->where('id', $order_id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();
    
    
            if ($order->status !== 'shipping') {
                return response()->json([
                    'message' =>
                        'This order cannot be confirmed in its current status.'
                ], 422);
            }
    
    
            $hasMissingStock = false;
            $issues = [];
    
    
            foreach ($validated['items'] as $receivedItem) {
    
                $orderItem = $order->products
                    ->firstWhere(
                        'id',
                        $receivedItem['order_item_id']
                    );
    
    
                if (!$orderItem) {
                    return response()->json([
                        'message' => 'Invalid order item.'
                    ], 422);
                }
    
    
                if (
                    $receivedItem['received_quantity']
                    > $orderItem->quantity
                ) {
                    return response()->json([
                        'message' =>
                            'Received quantity cannot exceed ordered quantity.'
                    ], 422);
                }
    
    
                $orderItem->update([
                    'received_quantity' =>
                        $receivedItem['received_quantity'],
                ]);
    
    
                if (
                    $receivedItem['received_quantity']
                    < $orderItem->quantity
                ) {
    
                    $hasMissingStock = true;
    
                    $issues[] = [
                        'order_item_id' => $orderItem->id,
                        'product_id' => $orderItem->product_id,
                        'ordered' => $orderItem->quantity,
                        'received' =>
                            $receivedItem['received_quantity'],
                        'missing' =>
                            $orderItem->quantity
                            - $receivedItem['received_quantity'],
                    ];
                }
            }
    
    
            $order->update([
                'status' => 'delivered',
                'arrived_at' => now(),
                'delivery_confirmed_at' => now(),
    
                'delivery_issue' => $hasMissingStock
                    ? 'Missing stock reported by client.'
                    : null,
            ]);
    
    
            return response()->json([
                'message' => $hasMissingStock
                    ? 'Delivery confirmed with missing stock reported.'
                    : 'Delivery confirmed successfully.',
    
                'order_id' => $order->id,
    
                'status' => $order->status,
    
                'has_missing_stock' => $hasMissingStock,
    
                'issues' => $issues,
    
                'delivery_confirmed_at' =>
                    $order->delivery_confirmed_at,
            ], 200);
        });
    }
}
