<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\ProductFilterRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\VerifyCategoryRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    //everyone can view products but according to their preferences
    public function index(ProductFilterRequest $request){
        $filter = $request->validated();

        $query = Product::query()->with(['company', 'categories']);

        $query->when(!empty($filter['search']), function($query) use ($filter){
            $query->where(function($q) use ($filter){
                $q->where('name', 'like', "%{$filter['search']}%")
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

        $query->when(!empty($filter['container_type']), function($query) use ($filter){
            $query->where('container_type', $filter['container_type']);
        });

        return ProductResource::collection($query->paginate(12));
        // $products = Product::whereHas('company.business_type', function ($query){
        //                 $query->where('id', $user->profile->business_type);
        //             })->get();

        // $query->whereHas('company', function($q) use ($user){
        //     $q->whereIn('business_type_id', $userPreferenceIds);
        // });
        // $query->whereHas('company.businessTypes', function ($q) use ($userPreferenceIds) {
        //     $q->whereIn('business_types.id', $userPreferenceIds);
        // });
    }
        
    public function show(Product $product){
        return new ProductResource($product->load(['categories', 'company']));
    }

    //warehouse_admin, super_admin only
    public function store(CreateProductRequest $request){
        $this->authorize('create', Product::class);

        $productValidated = $request->validated();
        $productValidated['sku'] = strtoupper($productValidated['sku']);
        $product = Product::create($productValidated);
        $product->categories()->sync($request->categories);
        return response()->json(['message' => 'Product Created Successfully!/n', 'data' => new ProductResource($product->load(['categories', 'company']))], 201);
    }
        
    //warehouse_admin, super_admin only
    public function update(UpdateProductRequest $request, Product $product){
        $this->authorize('update', $product);
                
        $productValidated = $request->validated();
        if (isset($productValidated['sku'])) {
            $productValidated['sku'] = strtoupper($productValidated['sku']);
        }
        $product->update($productValidated);
        
        return response()->json(['message' => 'Product Updated Successfully!', 'data' => new ProductResource($product->load(['categories', 'company']))], 200);
    }

    public function destroy(Product $product){
        $this->authorize('delete', $product);

        $product->delete();
        return response()->json(['message' => 'Product Deleted Successfully!'], 200);
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
