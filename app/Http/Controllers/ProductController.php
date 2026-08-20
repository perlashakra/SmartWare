<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\ProductFilterRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\VerifyCategoryRequest;
use App\Http\Resources\ProductResource;
use App\Jobs\TranslateProductJob;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class ProductController extends Controller
{
    private function uploadProductImage(UploadedFile $image){
        return $image->store('products', 'public');
    }

    //this is not working for now
    public function index(ProductFilterRequest $request){
        $query = Product::query()->with(['categories']);

    $filter = $request->validated();

    $query->when(!empty($filter['search']), function ($query) use ($filter) {
        $query->where(function ($q) use ($filter) {
            $q->where('name_en', 'like', "%{$filter['search']}%")
                ->orWhere('name_ar', 'like', "%{$filter['search']}%")
                ->orWhere('sku', 'like', "%{$filter['search']}%");
        });
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
        return new ProductResource($product->load(['categories', 'company']));
    }

    //warehouse_admin, super_admin only
    //review creation of a product when a manager does it 
    public function store(StoreProductRequest $request){
        $this->authorize('create', Product::class);
        
        $productValidated = $request->validated();
        $productValidated['sku'] = strtoupper($productValidated['sku']);
        
        $categories = $productValidated['categories'];
        
        unset($productValidated['categories']);

        if($request->hasFile('product_image')){
            $productValidated['product_image'] = $this->uploadProductImage($request->file('product_image'));
        }
        
        $product = Product::create($productValidated);
        $product->categories()->sync($categories);

        TranslateProductJob::dispatch($product->id);

        return response()->json(['message' => __('product.created'), 'data' => new ProductResource($product->load(['categories', 'company']))], 201);
    }
        
    //warehouse_admin, super_admin only
    public function update(UpdateProductRequest $request, Product $product){
        $this->authorize('update', $product);
                
        $productValidated = $request->validated();
        if (isset($productValidated['sku'])) {
            $productValidated['sku'] = strtoupper($productValidated['sku']);
        }

        if($request->hasFile('product_image')){
            if($product->product_image){
                Storage::disk('public')->delete($product->product_image);
            }
            $productValidated['product_image'] = $this->uploadProductImage($request->file('product_image'));
        }

        $product->update($productValidated);
        return response()->json(['message' => __('product.updated'), 'data' => new ProductResource($product->load(['categories', 'company']))], 200);
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

    // public function importProducts(Request $request, $file){
    //     $request->file($file)->store();
    //     Excel::import(new ProductsImport(), $file);
    // }
}
