<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(){
        return CategoryResource::collection(Category::paginate(20));
    }

    public function store(StoreCategoryRequest $request){
        $this->authorize('create', Category::class);

        $category = Category::create($request->validated());

        return response()->json(['message' => __('category.created'), 'data' => new CategoryResource($category)], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category){
        $this->authorize('update', $category);

        $category->update($request->validated());
        return response()->json(['message' => __('category.updated'), 'data' => new CategoryResource($category)], 200);
    }

    public function destroy(Category $category){
        $this->authorize('delete', $category);

        $category->delete();
        return response()->json(['message' => __('category.deleted')], 200);
    }
}
