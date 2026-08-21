<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\ProductFilterRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\VerifyCategoryRequest;
use App\Http\Resources\ProductResource;
use App\Jobs\TranslateProductJob;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Section;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class ProductController extends Controller
{
    public function __construct(public InventoryService $inventoryService) {}
    
    private function uploadProductImage(UploadedFile $image){
        return $image->store('products', 'public');
    }

    //this is not working for now
    public function index(ProductFilterRequest $request){
        $query = Product::query()->with(['categories']);

        $user = Auth::user();
        if($user->role === 'client'){
            $preferred_categories =
            Category::whereHas('facilities', function($query){
                    $query->where('category_id', );
                })->whereHas('products', function($query){
                    $query->where('category_id', );
                })->get();

            $query->whereIn($query->categories(), $preferred_categories);
        }

        $filter = $request->validated();

    $query->when(!empty($filter['search']), function ($query) use ($filter) {
        $query->where(function ($q) use ($filter) {
            $q->where('name_en', 'like', "%{$filter['search']}%")
                ->orWhere('name_ar', 'like', "%{$filter['search']}%")
                ->orWhere('sku', 'like', "%{$filter['search']}%");
            });
        });

        $query->when(isset($filter['min_price']), function($query) use ($filter){
            $query->where('price', '>=', $filter['min_price']);
        });

        $query->when(isset($filter['max_price']), function($query) use ($filter){
            $query->where('price', '<=', $filter['max_price']);
        });

    $query->when(!empty($filter['categories']), function ($query) use ($filter) {
        $query->whereHas('categories', function ($q) use ($filter) {
            $q->whereIn('categories.id', $filter['categories']);
        });
    });

    $query->when(!empty($filter['container_type']), function ($query) use ($filter) {
        $query->where('container_type', $filter['container_type']);
    });

        return ProductResource::collection($query->paginate(12));
    }

    public function show(Product $product){
        return new ProductResource($product->load(['categories']));
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $productValidated = $request->validated();
        $productValidated['sku'] = strtoupper($productValidated['sku']);

        $warehouse_id = $productValidated['warehouse_id'];
        $quantity = $productValidated['quantity'];
        $unit_price = $productValidated['unit_price'];
        $categories = $productValidated['categories'];

        unset($productValidated['categories'], $productValidated['warehouse_id'], $productValidated['quantity'], $productValidated['unit_price']);

        if ($request->hasFile('product_image')) {
            $productValidated['product_image'] = $this->uploadProductImage($request->file('product_image'));
        }

        $section = Section::where('warehouse_id', $warehouse_id)->where('name', 'Main Storage')->first();
        if (!$section) {
            return response()->json(['message' => 'Main storage does not exist. Please import the inventory Excel file first.'], 404);
        }

        if (Auth::user()->role !== 'warehouse_admin' && !Auth::user()->canManageSection($section)) {
            return response()->json(['message' => 'You are not authorized to add products to this warehouse.'], 403);
        }

        DB::beginTransaction();

        try {
            $product = Product::create($productValidated);

            $product->categories()->sync($categories);

            $this->inventoryService->setImportedStock($section, $product, $quantity, $unit_price);

            TranslateProductJob::dispatch($product->id);

            DB::commit();

            return response()->json(['message' => __('product.created'), 'data' => new ProductResource($product->load(['categories']))], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    //warehouse_admin, super_admin only
    public function update(UpdateProductRequest $request, Product $product){
        $this->authorize('update', $product);

        $validated = $request->validated();
\Log::info('UPDATE PRODUCT REQUEST', [
    'product_id' => $product->id,
    'request' => $request->all(),
    'validated' => $validated,
]);
        DB::beginTransaction();

        try{
            $inventory = Inventory::with('section')
            ->where('product_id', $product->id)
            ->whereHas('section', function($query){
                $query->where('name', 'Main Storage');
            })->first();

            if(!$inventory){
                DB::rollBack();
                return response()->json(['message' => 'You are not authorized to update this product.'], 403);
            }

            $productData = $validated;

            if(isset($productData['sku'])){
                $productData['sku'] = strtoupper($productData['sku']);
            }

            $quantity = $productData['quantity'] ?? null;
            $unit_price = $productData['unit_price'] ?? null;

            unset($productData['quantity'], $productData['unit_price'], $productData['categories']);

            if ($request->hasFile('product_image')) {
                if ($product->product_image) {
                    Storage::disk('public')->delete($product->product_image);
                }
                $productData['product_image'] = $this->uploadProductImage($request->file('product_image'));
            }

            if(!empty($productData)){
                $product->update($productData);
            }

            if($quantity !== null || $unit_price !== null){
                $newQuantity = $quantity ?? $inventory->quantity;
                $newUnitPrice = $unit_price ?? $inventory->unit_price;
                $this->inventoryService->updateInventoryDetails($inventory, $newQuantity, $newUnitPrice);
            }

            DB::commit();
            return response()->json(['message' => __('product.updated'), 'product' => new ProductResource($product->load(['categories', 'inventories']))], 200);
        } catch(\Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Product $product){
        $this->authorize('delete', $product);

        if($product->product_image){
            Storage::disk('public')->delete($product->product_image);
        }

        $product->delete();
        return response()->json(['message' => __('product.deleted')], 200);
    }

    //(product - category) relationship

    //add categories
    public function addCategories(VerifyCategoryRequest $request, Product $product){
        $this->authorize('update', $product);
        $product->categories()->syncWithoutDetaching($request->validated()['categories']);
        return new ProductResource($product->load('categories'));
    }

    //replace all
    public function syncCategories(VerifyCategoryRequest $request, Product $product){
        $this->authorize('update', $product);
        $product->categories()->sync($request->validated()['categories']);
        return new ProductResource($product->load('categories'));
    }

    //remove categories
    public function removeCategories(VerifyCategoryRequest $request, Product $product){
        $this->authorize('update', $product);
        $product->categories()->detach($request->validated()['categories']);
        return new ProductResource($product->load('categories'));
    }
}
