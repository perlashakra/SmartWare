<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustInventoryRequest;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Section;
use App\Services\Inventory\InventoryService as InventoryInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class InventoryController extends Controller
{
    public function __construct(
        private InventoryInventoryService $inventoryService
    ) {}

    //inventory of the warehouse admin's warehouses.
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Inventory::query()
            ->with([
                'product',
                'section.warehouse',
            ]);

        if ($request->filled('warehouse_id')) {
            $query->whereHas('section', function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse_id);
            });
        }

        if ($request->filled('section_id')) {
            $query->where( 'section_id', $request->section_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $inventory = $query->latest()->paginate(20);

        return response()->json(['data' => $inventory], 200);
    }

    public function show(Inventory $inventory)
    {
        $this->authorize('view', $inventory);

        $inventory->load(['product', 'section.warehouse']);

        return response()->json(['data' => $inventory], 200);
    }

    public function adjust(AdjustInventoryRequest $request, Inventory $inventory) {
        $this->authorize('update', $inventory);

        try {
            $inventory = $this->inventoryService->adjustStock($inventory, $request->validated('quantity'));
            return response()->json(['message' => 'Inventory adjusted successfully.', 'data' => $inventory], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function storedProducts()
    {
        $products = Product::query()->whereHas('inventories')->with(['categories', 'inventories', 'inventories.discounts' => function ($query) {
            $query->where('is_active', true)->where('starts_at', '<=', now())->where('ends_at', '>=', now());            
        }])->get();

        return response()->json(['data' => $products], 200);
    }

    public function productWarehouses(Product $product)
    {
        $inventories = $product->inventories()->with(['section.warehouse', 'discounts'])->get();
        $warehouses = $inventories
            ->pluck('section.warehouse')
            ->unique('id')
            ->values();
        $discounts = $inventories->pluck('discounts')->flatten()->values(); 

        return response()->json([
            'product' => $product,
            'warehouses' => $warehouses,
            'inventories' => $inventories,
        ], 200);
    }

    //view inventory in one section
    public function sectionInventory(Section $section){
        $user = Auth::user();

        if(!$user->warehouses()->whereKey($section->warehouse_id)->exists()){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $inventory = Inventory::query()->with(['product', 'section.warehouse'])->where('section_id', $section->id)->paginate(20);

        return response()->json(['data' => $inventory], 200);
    }

    //view inventory in one warehouse
    public function warehouseInventory(Request $request, int $warehouse_id){
        $user = Auth::user();

        if(!$user->warehouses()->whereKey($warehouse_id)->exists()){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $inventory = Inventory::with('product')->whereHas('section', function($query) use ($warehouse_id){
            $query->where('warehouse_id', $warehouse_id);
        })->latest('id')->paginate(20);

        return response()->json(['data' => $inventory], 200);
    }
}