<?php

namespace App\Http\Controllers;

use App\Enums\FacilityType;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use App\Models\Inbook;
use App\Models\InBookProduct;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Section;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FacilityController extends Controller
{
    public function index(){
        return FacilityResource::collection(Facility::with(['owner', 'address'])->paginate(12));
    }

    public function show(Facility $facility){
        $facility->load(['owner', 'address']);
        return new FacilityResource($facility);
    }

    public function store(StoreFacilityRequest $request){
        $this->authorize('create', Facility::class);

        if(Auth::user()->role === 'client' && $request->facility_type !== FacilityType::Business->value){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if(Auth::user()->role === 'warehouse_admin' && $request->facility_type !== FacilityType::Warehouse->value){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();
        $validated['user_id'] = Auth::user()->id;
        $facility = Facility::create($validated);
        $facility->refresh();
        return response()->json(['message' => 'Facility Created Successfully!', 'data' => new FacilityResource($facility)], 201);
    }

    public function update(UpdateFacilityRequest $request, Facility $facility){
        $this->authorize('update', $facility);
        $facility->update($request->validated());
        return response()->json(['message' => 'Facility Updated Successfully!', 'data' => new FacilityResource($facility)], 2014);
    }

    public function destroy(Facility $facility){
        $this->authorize('delete', $facility);
        $facility->delete();
        return response()->json(['message' => 'Facility Deleted Successfully!'], 200);
    }

    public function getWarehouses(){
        return FacilityResource::collection(Facility::warehouses()->with(['owner', 'address'])->paginate(12));
    }

    public function getBusinesses(){
        return FacilityResource::collection(Facility::businesses()->with(['owner', 'address'])->paginate(12));
    }

    public function getOwnedFacilities(){

        $user = Auth::user();

        return response()->json($user->owns, 200);

    }

    public function getFacilityInfo($id){
        $Facility = Auth::user()
        ->owns()
        ->where('id', $id)
        ->firstOrFail();

        $Facility->load('sections');
        return response()->json($Facility, 200);
    }

    public function getSectionInfo($facility_id,$section_id){
        $Facility =  Auth::user()
                    ->owns()
                    ->where('id',$facility_id)
                    ->firstOrFail();
        $section = $Facility->sections()
                    ->where('id',$section_id)
                    ->firstOrFail();
        $section->load('inventories.product');
        return response()->json(['Section_info'=>$section], 200);
    }

    public function topMovingProduct($facility_id)
    { 
        $facility = Auth::user()
            ->owns()
            ->where('id', $facility_id)
            ->firstOrFail();

        $products = Product::whereHas('orderItems.order', function ($query) use ($facility) {
                $query->where('src_facility_id', $facility->id)
                    ->whereIn('status', [
                        'approved',
                        'preparing',
                        'shipping',
                        'delivered',
                    ]);
            })
            ->withSum([
                'orderItems as total_sold' => function ($query) use ($facility) {
                    $query->whereHas('order', function ($orderQuery) use ($facility) {
                        $orderQuery->where('src_facility_id', $facility->id)
                            ->whereIn('status', [
                                'approved',
                                'preparing',
                                'shipping',
                                'delivered',
                            ]);
                    });
                }
            ], 'quantity')
            ->orderByDesc('total_sold')
            ->paginate(12);

        return response()->json($products, 200);
    }

    public function slowMovingProduct($facility_id)
    {
        $facility = Auth::user()
            ->owns()
            ->where('id', $facility_id)
            ->firstOrFail();

        $products = Product::whereHas('inventories.section', function ($query) use ($facility) {
                $query->where('warehouse_id', $facility->id);
            })
            ->withSum([
                'orderItems as total_sold' => function ($query) use ($facility) {
                    $query->whereHas('order', function ($orderQuery) use ($facility) {
                        $orderQuery->where('src_facility_id', $facility->id)
                            ->whereIn('status', [
                                'approved',
                                'preparing',
                                'shipping',
                                'delivered',
                            ]);
                    });
                }
            ], 'quantity')
            ->orderBy('total_sold')
            ->paginate(12);

        return response()->json($products, 200);
    }

    public function stockOutRisk($facility_id)
    {
        $facility = Auth::user()
            ->owns()
            ->where('id', $facility_id)
            ->firstOrFail();
    
        $products = Product::whereHas('inventories.section', function ($query) use ($facility) {
                $query->where('warehouse_id', $facility->id);
            })
            ->withSum([
                'inventories as warehouse_quantity' => function ($query) use ($facility) {
                    $query->whereHas('section', function ($sectionQuery) use ($facility) {
                        $sectionQuery->where('warehouse_id', $facility->id);
                    });
                }
            ], 'quantity')
            ->having('warehouse_quantity', '<=', 10)
            ->orderBy('warehouse_quantity')
            ->paginate(12);
    
        return response()->json($products, 200);
    }
    public function showInventoryByCategory($facility_id)
    {
        $facility = Auth::user()
            ->owns()
            ->where('id', $facility_id)
            ->firstOrFail();

        $products = Product::with([
                'categories',
                'inventories.section'
            ])
            ->whereHas('inventories.section', function ($query) use ($facility) {
                $query->where('warehouse_id', $facility->id);
            })
            ->get();

        $categories = [];

        foreach ($products as $product) {

            $warehouseQuantity = $product->inventories
                ->filter(function ($inventory) use ($facility) {
                    return $inventory->section->warehouse_id == $facility->id;
                })
                ->sum('quantity');

            foreach ($product->categories as $category) {

                if (!isset($categories[$category->id])) {
                    $categories[$category->id] = [
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                        'total_quantity' => 0,
                        'products' => [],
                    ];
                }

                $categories[$category->id]['total_quantity'] += $warehouseQuantity;

                $categories[$category->id]['products'][] = [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name_en' => $product->name_en,
                    'name_ar' => $product->name_ar,
                    'quantity' => $warehouseQuantity,
                ];
            }
        }

        return response()->json(array_values($categories), 200);
    }
    public function stockMovement($facility_id)
    {
        // Make sure the authenticated user owns this warehouse
        $facility = Auth::user()
            ->owns()
            ->where('id', $facility_id)
            ->firstOrFail();
    
    
      
        $incoming = OrderItem::join(
                'orders',
                'order_items.order_id',
                '=',
                'orders.id'
            )
            ->join(
                'products',
                'order_items.product_id',
                '=',
                'products.id'
            )
            ->leftJoin(
                'facilities',
                'orders.dest_facility_id',
                '=',
                'facilities.id'
            )
            ->where('orders.dest_facility_id', $facility->id)
            ->whereIn('orders.status', [
                'approved',
                'preparing',
                'shipping',
            ])
            ->select([
                'orders.order_date as date',
                'products.id as product_id',
                'products.name_en as product_name',
                'order_items.quantity',
                'facilities.id as destination_id',
                'facilities.facility_name_en as destination_name',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'type' => 'incoming',
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'destination' => null,
                ];
            });
    
    
        
        $outgoing = OrderItem::join(
                'orders',
                'order_items.order_id',
                '=',
                'orders.id'
            )
            ->join(
                'products',
                'order_items.product_id',
                '=',
                'products.id'
            )
            ->leftJoin(
                'facilities',
                'orders.dest_facility_id',
                '=',
                'facilities.id'
            )
            ->where('orders.src_facility_id', $facility->id)
            ->whereIn('orders.status', [
                'approved',
                'preparing',
                'shipping',
                'delivered',
            ])
            ->select([
                'orders.order_date as date',
                'products.id as product_id',
                'products.name_en as product_name',
                'order_items.quantity',
                'facilities.id as destination_id',
                'facilities.facility_name_en as destination_name',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'type' => 'outgoing',
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'destination' => $item->destination_name,
                    'destination_id' => $item->destination_id,
                ];
            });
    
        $movement = $incoming
            ->concat($outgoing)
            ->sortBy('date')
            ->values();
    
        return response()->json($movement, 200);
    }

    public function warehouseDashboard($facility_id)
    {
        $facility = $this->getWorkerFacility($facility_id);
    
    
        /*
        |--------------------------------------------------------------------------
        | Current stock
        |--------------------------------------------------------------------------
        */
    
        $inventory = Inventory::with('product')
            ->whereHas('section', function ($query) use ($facility) {
                $query->where('warehouse_id', $facility->id);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name_en,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => $item->unit_price,
                    'section_id' => $item->section_id,
                ];
            });
    
    
        /*
        |--------------------------------------------------------------------------
        | Incoming
        |--------------------------------------------------------------------------
        |
        | Orders where this warehouse is the destination.
        |
        */
    
        $incoming = Order::with([
                'products.product',
            ])
            ->where('dest_facility_id', $facility->id)
            ->whereIn('status', [
                'pending',
                'partially_approved',
                'approved',
                'preparing',
                'shipping',
            ])
            ->orderBy('order_date')
            ->get()
            ->map(function ($order) {
    
                return [
                    'order_id' => $order->id,
                    'order_type' => $order->order_type,
                    'status' => $order->status,
                    'order_date' => $order->order_date,
                    'departed_at' => $order->departed_at,
                    'arrived_at' => $order->arrived_at,
    
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
    
    
        /*
        |--------------------------------------------------------------------------
        | Outgoing
        |--------------------------------------------------------------------------
        */
    
        $outgoing = Order::with([
                'products.product',
                'destination',
            ])
            ->where('src_facility_id', $facility->id)
            ->whereIn('status', [
                'pending',
                'partially_approved',
                'approved',
                'preparing',
                'shipping',
            ])
            ->orderBy('order_date')
            ->get()
            ->map(function ($order) {
    
                return [
                    'order_id' => $order->id,
                    'order_type' => $order->order_type,
                    'status' => $order->status,
                    'order_date' => $order->order_date,
    
                    'departed_at' => $order->departed_at,
                    'arrived_at' => $order->arrived_at,
    
                    'destination' => $order->destination
                        ? [
                            'id' => $order->destination->id,
                            'name' => $order->destination->facility_name_en,
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
            'facility_id' => $facility->id,
    
            'inventory' => $inventory,
    
            'incoming' => $incoming,
    
            'outgoing' => $outgoing,
        ], 200);
    }

    private function getWorkerFacility($facility_id)
    {
        $user = Auth::user();
    
        if ($user->role !== 'worker') {
            abort(403, 'Only warehouse workers can access this endpoint.');
        }
    
        return Facility::where('id', $facility_id)
            ->where('user_id', $user->manager_id)
            ->where('facility_type', 'warehouse')
            ->firstOrFail();
    }

    public function recordDeparture($facility_id, $order_id)
    {
        $facility = $this->getWorkerFacility($facility_id);
    
        $order = Order::where('id', $order_id)
            ->where('src_facility_id', $facility->id)
            ->firstOrFail();
    
    
        if (!in_array($order->status, ['approved','preparing',])) {
            return response()->json([
                'message' => 'This order cannot be dispatched in its current status.',
                'status' => $order->status,
            ], 422);
        }
    
    
        if ($order->departed_at) {
            return response()->json([
                'message' => 'Departure has already been recorded.',
                'departed_at' => $order->departed_at,
            ], 422);
        }

        if($order->has_shipment){
            $order->update([
                'departed_at' => now(),
                'status' => 'shipping',
            ]);
            $order->refresh();

            return response()->json([
                'message' => 'Shipment departure recorded successfully.',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'departed_at' => $order->departed_at,
                    'tot_price'=>$order->expected_price,
                ],
            ], 200);
        }
        else{
            return response()->json([
                'message'=>'the order does not have any shipment to be departured'
            ],422);
        }
    }

    //here You must add the inbook product functionality 
    public function recordArrival($facility_id, $order_id)
    {
        $facility = $this->getWorkerFacility($facility_id);

        $order = Order::where('id', $order_id)
            ->where('dest_facility_id', $facility->id)
            ->firstOrFail();


        if ($order->status !== 'shipping') {
            return response()->json([
                'message' => 'This order is not currently shipping.',
                'status' => $order->status,
            ], 422);
        }


        if (!$order->departed_at) {
            return response()->json([
                'message' => 'Departure must be recorded before arrival.'
            ], 422);
        }


        if ($order->arrived_at) {
            return response()->json([
                'message' => 'Arrival has already been recorded.',
                'arrived_at' => $order->arrived_at,
            ], 422);
        }

        DB::transaction(function () use ($order) {

            // Create one inbook for the whole shipment
            $inbook = Inbook::create([
                'user_id' => Auth::id(),
                'storage_date' => now()->toDateString(),
            ]);

            foreach ($order->products as $orderProduct) {

                $product = $orderProduct->product;

                // Find a section for this product's company
                $section = Section::where('warehouse_id', $order->dest_facility_id)
                    ->where('company_id', $product->company_id)
                    ->first();

                if (!$section) {
                    return response()->json([
                    'message' => 'No section available for company {$product->company_id}',
                    ], 422);
                }

                // Record the incoming product
                InbookProduct::create([
                    'inbook_id' => $inbook->id,
                    'product_id' => $product->id,
                    'section_id' => $section->id,
                    'quantity' => $orderProduct->quantity,
                ]);

                // Update inventory
                $inventory = Inventory::where('section_id', $section->id)
                    ->where('product_id', $product->id)
                    ->first();
                
                if ($inventory) {
                    Inventory::where('section_id', $section->id)
                        ->where('product_id', $product->id)
                        ->increment('quantity', $orderProduct->quantity);
                } else {
                    Inventory::create([
                        'section_id' => $section->id,
                        'product_id' => $product->id,
                        'quantity' => $orderProduct->quantity,
                        'unit_price' => $orderProduct->unit_price ?? 0,
                    ]);
                }
            }

            // Finally mark the order as arrived
            $order->update([
                'arrived_at' => now(),
                'status' => 'delivered',
            ]);
        });


        return response()->json([
            'message' => 'Shipment arrival recorded successfully.',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->status,
                'departed_at' => $order->departed_at,
                'arrived_at' => $order->arrived_at,
            ],
        ], 200);
    }

}
