<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index(){
        //Company::with('products')->get();
        return CompanyResource::collection(Company::paginate(12));
    }

    public function show(Company $company){
        if(!$company){
            return response()->json(['message' => 'Company Not Found'], 404);
        }
        return new CompanyResource($company);
    }

    public function store(StoreCompanyRequest $request){
        $this->authorize('create', Company::class);

        $company = Company::create($request->validated());
        
        return response()->json(['message' => 'Company Created Successfully!/n', 'data' => new CompanyResource($company)], 201);
    }

    public function update(UpdateCompanyRequest $request, Company $company){
        $this->authorize('update', $company);

        $company->update($request->validated());
        return response()->json(['message' => 'Company Updated Successfully!/n', 'data' => new CompanyResource($company)], 200);
    }

    public function destroy(Company $company){
        $this->authorize('delete', $company);
        $company->delete();
        return response()->json(['message' => 'Company Deleted Successfully.'], 200);
    }
}
