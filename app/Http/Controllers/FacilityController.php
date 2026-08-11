<?php

namespace App\Http\Controllers;

use App\Enums\FacilityType;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use Illuminate\Support\Facades\Auth;

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
}
