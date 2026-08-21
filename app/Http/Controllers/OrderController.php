<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders.
     */
    /**
     * Display a simple listing of orders (without pagination).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $ownedFacilityIds = $user->owns()->pluck('id')->toArray();

        $orders = Order::with(['products.product', 'warehouseOfTheOrder'])
            ->where(function ($query) use ($user, $ownedFacilityIds) {
                $query->where('user_id', $user->id);

                if (!empty($ownedFacilityIds)) {
                    $query->orWhereIn('src_facility_id', $ownedFacilityIds)
                        ->orWhereIn('dest_facility_id', $ownedFacilityIds);
                }
            })
            ->latest()
            ->get(); // Fetch simple array instead of paginating

        return response()->json([
            'success' => true,
            'data'    => $orders
        ]);
    }

    /**
     * Display a specific order with its products (handles missing IDs gracefully).
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::with(['products.product', 'warehouseOfTheOrder', 'userWhoMadeTheOrder'])
            ->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => "Order with ID {$id} not found."
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $order
        ]);
    }

    /**
     * Store (Checkout Cart): Confirms cart and creates orders grouped by warehouse.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_id'              => 'nullable|exists:carts,id',
            'dest_facility_id'     => 'nullable|exists:facilities,id',
            'notes'                => 'nullable|string|max:500',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.warehouse_id' => 'required|exists:facilities,id',
            'items.*.quantity'     => 'required|integer|min:1',
        ]);

        $user = $request->user();

        // Ensure user owns the destination facility if provided
        if (!empty($validated['dest_facility_id'])) {
            $ownedFacilityIds = $user->owns()->pluck('id')->toArray();

            if (!in_array($validated['dest_facility_id'], $ownedFacilityIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Destination facility does not belong to you.'
                ], 403);
            }
        }

        try {
            $orders = $this->orderService->createOrdersFromCart(
                userId: $user->id,
                destFacilityId: $validated['dest_facility_id'] ?? null,
                cartItems: $validated['items'],
                notes: $validated['notes'] ?? null,
                cartId: $validated['cart_id'] ?? null
            );

            foreach($orders as $order){
                OrderCreated::dispatch($order);
            }

            return response()->json([
                'success' => true,
                'message' => 'Orders created successfully and split by warehouse.',
                'data'    => $orders
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process order checkout.',
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Warehouse Manager Action: Create a stock transfer order between two warehouses.
     */
    public function storeTransfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'src_facility_id'    => 'required|exists:facilities,id',
            'dest_facility_id'   => 'required|exists:facilities,id|different:src_facility_id',
            'notes'              => 'nullable|string|max:500',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $ownedFacilityIds = $user->owns()->pluck('id')->toArray();

        // Ensure the manager owns both the source and destination facilities
        if (!in_array($validated['src_facility_id'], $ownedFacilityIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not own the source facility.'
            ], 403);
        }

        if (!in_array($validated['dest_facility_id'], $ownedFacilityIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not own the destination facility.'
            ], 403);
        }

        try {
            $order = $this->orderService->createWarehouseTransfer(
                userId: $user->id,
                srcFacilityId: $validated['src_facility_id'],
                destFacilityId: $validated['dest_facility_id'],
                items: $validated['items'],
                notes: $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Warehouse transfer request created and approved successfully.',
                'data'    => $order
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create warehouse transfer.',
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancel an order (Client Action).
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        try {
            $this->orderService->cancelOrderByClient(
                order: $order,
                userId: $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Order successfully cancelled.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Process Warehouse Decisions (Admin Action: Approve/Reject line items).
     */
    public function processDecision(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'nullable|required_if:status,rejected|string|max:255',
        ]);

        $user = $request->user();
        $ownedFacilityIds = $user->owns()->pluck('id')->toArray();

        if (!in_array($order->src_facility_id, $ownedFacilityIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to process decisions for this warehouse.'
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order decision has already been processed.'
            ], 422);
        }

        try {
            $order->update([
                'status' => $validated['status'],
                'rejection_reason' => $validated['status'] === 'rejected' ? $validated['reason'] : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Order successfully {$validated['status']}.",
                'data'    => $order->fresh()
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process warehouse decision.',
                'error'   => $e->getMessage()
            ], 400);
        }
    }
}
