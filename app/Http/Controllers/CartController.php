<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddItemCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Facility;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CartController extends Controller
{
    public function addItem(AddItemCartRequest $request){
        $validated = $request->validated();

        $user = Auth::user();

        $warehouse = Facility::where('id', $validated['warehouse_id'])
            ->where('facility_type', 'warehouse')
            ->firstOrFail();

        $cart = Cart::firstOrCreate(
            [
                'user_id' => $user->id,
                'status' => 'active',
            ],
            [
                'facility_id' => $validated['facility_id'],
            ]
        );


        $inventory = Inventory::where('product_id', $validated['product_id'])
            ->whereHas('section', function ($query) use ($warehouse) {
                $query->where('warehouse_id', $warehouse->id);
            })
            ->lockForUpdate()
            ->first();

        if (!$inventory) {
            return response()->json([
                'message' => __('cart.product_not_available')
            ], 422);
        }


        if ($inventory->quantity < $validated['quantity']) {
            return response()->json([
                'message' =>  __('cart.not_enough_stock'),
                'available_quantity' => $inventory->quantity,
            ], 422);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $validated['product_id'])
            ->where('warehouse_id', $warehouse->id)
            ->first();

        if ($cartItem) {

            $newQuantity = $cartItem->quantity + $validated['quantity'];

            if ($newQuantity > $inventory->quantity) {
                return response()->json([
                    'message' =>  __('cart.requested_quantity_exceeds_stock'),
                    'available_quantity' => $inventory->quantity,
                ], 422);
            }

            $cartItem->update([
                'quantity' => $newQuantity,
            ]);

        } else {

            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $validated['product_id'],
                'warehouse_id' => $warehouse->id,
                'quantity' => $validated['quantity'],
            ]);
        }


        return response()->json([
            'message' => __('cart.product_added'),
            'data' => $cartItem->load([
                'product',
                'warehouse',
            ]),
        ], 201);
    }

    public function show(){
        $user = Auth::user();

        $cart = Cart::with([
            'items.product',
            'items.warehouse',
        ])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json([
                'message' => __('cart.cart_empty'),
                'data' => [],
            ], 200);
        }

        return response()->json([
            'data' => $cart,
        ], 200);
    }

    public function updateItem(Request $request, $cartItemId){
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem = CartItem::whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id())
                  ->where('status', 'active');
        })
        ->findOrFail($cartItemId);


        $inventory = Inventory::where(
                'product_id',
                $cartItem->product_id
            )
            ->whereHas('section', function ($query) use ($cartItem) {
                $query->where('warehouse_id', $cartItem->warehouse_id);
            })
            ->first();

        if (!$inventory) {
            return response()->json([
                'message' => __('cart.product_no_longer_available')
            ], 422);
        }

        if ($validated['quantity'] > $inventory->quantity) {
            return response()->json([
                'message' => __('cart.not_enough_stock'),
                'available_quantity' => $inventory->quantity,
            ], 422);
        }

        $cartItem->update([
            'quantity' => $validated['quantity'],
        ]);

        return response()->json([
            'message' => __('cart.cart_updated'),
            'data' => $cartItem->load('product', 'warehouse'),
        ]);
    }
    public function removeItem($cartItemId){
        $cartItem = CartItem::whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id())
                  ->where('status', 'active');
        })
        ->findOrFail($cartItemId);

        $cartItem->delete();

        return response()->json([
            'message' => __('cart.item_removed'),
        ]);
    }
    
    public function submit(){
        $user = Auth::user();

        return DB::transaction(function () use ($user) {

            $cart = Cart::where('user_id', $user->id)
                ->where('status', 'active')
                ->with('items')
                ->lockForUpdate()
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'message' => __('cart.your_cart_empty')
                ], 422);
            }

            $clientFacility = Facility::where('id', $cart->facility_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $itemsByWarehouse = $cart->items
                ->groupBy('warehouse_id');


            $orders = [];


            foreach ($itemsByWarehouse as $warehouseId => $items) {

                $warehouse = Facility::where('id', $warehouseId)
                    ->where('facility_type', 'warehouse')
                    ->firstOrFail();


                $expectedPrice = 0;




                foreach ($items as $cartItem) {

                    $inventory = Inventory::where(
                            'product_id',
                            $cartItem->product_id
                        )
                        ->whereHas('section', function ($query) use ($warehouseId) {
                            $query->where('warehouse_id', $warehouseId);
                        })
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        return response()->json(__('cart.product_no_longer_available_in_warehouse',
                        [$cartItem->product_id]),404);
                    }


                    if ($inventory->quantity < $cartItem->quantity) {
                        return response()->json(__('cart.not_enough_stock_for_product',
                        [$cartItem->product_id]),404);
                    }


                    $expectedPrice +=
                        $inventory->unit_price * $cartItem->quantity;
                }

                $order = Order::create([
                    'src_facility_id' => $warehouseId,
                    'dest_facility_id' => $clientFacility->id,
                    'user_id' => $user->id,

                    'order_type' => 'business_purchase',

                    'expected_price' => $expectedPrice,

                    // IMPORTANT
                    'status' => 'pending',

                    'order_date' => now()->toDateString(),

                    'notes' => __('cart.order_created_from_cart'),
                ]);

                foreach ($items as $cartItem) {

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'unit_price'=>$inventory->unit_price
                    ]);
                }


                $orders[] = $order;
            }

            $cart->update([
                'status' => 'submitted',
            ]);

            return response()->json([
                'message' => __('cart.order_submitted'),
                'orders' => collect($orders)->map(function ($order) {
                    return [
                        'order_id' => $order->id,
                        'warehouse_id' => $order->src_facility_id,
                        'destination_id' => $order->dest_facility_id,
                        'status' => $order->status,
                    ];
                }),
            ], 201);
        });
    }
}