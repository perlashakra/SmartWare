<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class OrderController extends Controller
{
    protected OrderService $orderService;

    // Inject OrderService into the controller
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders (for client or warehouse admin).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Query orders created by the client or destined for their warehouse
        $orders = Order::with(['products.product', 'warehouseOfTheOrder'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);

                // If user is affiliated with a facility/warehouse, include incoming orders
                if ($user->facility_id) {
                    $query->orWhere('src_facility_id', $user->facility_id);
                }
            })
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $orders
        ]);
    }

    /**
     * Store (Checkout Cart): Confirms cart and creates orders grouped by warehouse.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dest_facility_id'            => 'nullable|exists:facilities,id',
            'notes'                       => 'nullable|string|max:500',
            'items'                       => 'required|array|min:1',
            'items.*.product_id'          => 'required|exists:products,id',
            'items.*.warehouse_id'        => 'required|exists:facilities,id',
            'items.*.quantity'            => 'required|integer|min:1',
        ]);

        try {
            $orders = $this->orderService->createOrdersFromCart(
                userId: $request->user()->id,
                destFacilityId: $validated['dest_facility_id'] ?? null,
                cartItems: $validated['items'],
                notes: $validated['notes'] ?? null
            );

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
     * Display a specific order with its products.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['products.product', 'warehouseOfTheOrder', 'userWhoMadeTheOrder']);

        return response()->json([
            'success' => true,
            'data'    => $order
        ]);
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
            'decisions'                   => 'required|array|min:1',
            'decisions.*.item_id'         => 'required|exists:order_items,id',
            'decisions.*.status'          => 'required|in:approved,rejected',
            'decisions.*.reason'          => 'nullable|required_if:decisions.*.status,rejected|string|max:255',
        ]);

        // Optional authorization guard for warehouse admins
        if ($request->user()->facility_id && $order->src_facility_id !== $request->user()->facility_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to process decisions for this warehouse.'
            ], 403);
        }

        try {
            $updatedOrder = $this->orderService->processWarehouseDecision(
                order: $order,
                decisions: $validated['decisions']
            );

            return response()->json([
                'success' => true,
                'message' => 'Order item statuses updated successfully.',
                'data'    => $updatedOrder
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
