<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DiscountController extends Controller
{
    public function index(){
        return DiscountResource::collection(Discount::all());
    }

    public function show(Discount $discount){
        return new DiscountResource($discount);
    }

    public function store(StoreDiscountRequest $request){
        
        $inventory = Inventory::with('section')->findOrFail($request->inventory_id);

        if (!$inventory->section) {
            return response()->json(['message' => 'The inventory does not belong to a valid section.'], 422);
        }

        $this->authorize('create', [Discount::class, $inventory]);

        $starts_at = Carbon::createFromFormat('d-m-Y', $request->starts_at)->startOfDay();
        $ends_at = Carbon::createFromFormat('d-m-Y', $request->ends_at)->startOfDay();

        $overlappingDiscount = Discount::where('inventory_id', $inventory->id)
            ->where('is_active', true)
            ->where('starts_at', '<', $ends_at)
            ->where('ends_at', '>', $starts_at)
            ->exists(); 

        if($overlappingDiscount){
            return response()->json(['message' => 'This inventory already has an active discount during the selected period.'], 422);
        }

        $discount = Discount::create([
            'inventory_id' => $inventory->id,
            'created_by' => Auth::id(),
            'percentage' => $request->percentage,
            'starts_at' => $starts_at,
            'ends_at' => $ends_at,
            'is_active' => true,
        ]);

        

        return response()->json(['message' => 'Discount created successfully!', 'data' => new DiscountResource($discount)], 201);
    }

    public function update(UpdateDiscountRequest $request, Discount $discount)
{
    $this->authorize('update', $discount);

    $starts_at = $discount->starts_at;
    $ends_at = $discount->ends_at;

    if ($request->filled('starts_at')) {
        $starts_at = \Carbon\Carbon::createFromFormat(
            'd-m-Y',
            $request->starts_at
        )->startOfDay();
    }

    if ($request->filled('ends_at')) {
        $ends_at = \Carbon\Carbon::createFromFormat(
            'd-m-Y',
            $request->ends_at
        )->startOfDay();
    }

    // Make sure the final date range is valid
    if ($ends_at->timestamp <= $starts_at->timestamp) {
        return response()->json([
            'message' => 'The discount end date must be after the start date.'
        ], 422);
    }

    // Check for overlap with OTHER active discounts
    $overlappingDiscount = Discount::where('inventory_id', $discount->inventory_id)
        ->where('is_active', true)
        ->where('id', '!=', $discount->id)
        ->where('starts_at', '<', $ends_at)
        ->where('ends_at', '>', $starts_at)
        ->exists();

    if ($overlappingDiscount) {
        return response()->json([
            'message' => 'This inventory already has an active discount during the selected period.'
        ], 422);
    }

    $data = $request->validated();

    if ($request->filled('starts_at')) {
        $data['starts_at'] = $starts_at;
    }

    if ($request->filled('ends_at')) {
        $data['ends_at'] = $ends_at;
    }

    $discount->update($data);

    // Load the relationship required by DiscountResource
    $discount->load('inventory');

    return response()->json([
        'message' => 'Discount updated successfully!',
        'data' => new DiscountResource($discount)
    ], 200);
}

    public function delete(Discount $discount){
        $this->authorize('delete', $discount);
        $discount->delete();
        return response()->json(['message' => 'Discount deleted successfully!'], 200);
    }
}
