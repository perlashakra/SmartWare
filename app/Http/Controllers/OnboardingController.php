<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetOnboardingOptionsRequest;
use App\Http\Requests\SavePreferencesRequest;
use App\Models\Profile;
use App\Enums\BusinessTypeEnum;
use App\Enums\ProductType;
use App\Models\BusinessType;
use App\Models\Preference;

class OnboardingController extends Controller
{
    private function getAllowedProductsByBusiness(BusinessTypeEnum $businessType): array
    {
        $products = [];
        foreach($businessType->categories() as $category){
            $products = array_merge(
                $products,
                $category->productTypes()
            );
        }
        return $products;
    }

    //should also be modified if business type migration is deleted which i think it should. it serves no purpose


    public function savePreferences(SavePreferencesRequest $request)
    {
        $profile = $request->user()->profile();
        $role = $request->validated('role');
        $productTypes = $request->validated('product_types', []);

        // --- CRITICAL LEGAL VALIDATION FOR WAREHOUSE MANAGERS ---
        if ($role === 'warehouse_manager') {
            $hasMedical = in_array(ProductType::MEDICINE->value, $productTypes) ||
                in_array(ProductType::MEDICAL_EQUIPMENT->value, $productTypes);

            $hasNonMedical = count(array_diff($productTypes, [ProductType::MEDICINE->value, ProductType::MEDICAL_EQUIPMENT->value])) > 0;

            if ($hasMedical && $hasNonMedical) {
                return response()->json([
                    'error' => 'Legal compliance error: Under Syrian law, warehouses storing medical supplies/medicines are strictly prohibited from co-storing commercial or non-medical goods.'
                ], 422);
            }
        }

        // --- DEFENSIVE VALIDATION FOR CLIENTS (BYPASS PREVENTION) ---
        if ($role === 'client') {
            $businessType = $request->validated('business_type');

            // 1. Fetch the master-list of what this business type is actually allowed to have
            $allowedEnums = $this->getAllowedProductsByBusiness(BusinessTypeEnum::from($businessType));

            // Convert the array of Enum objects into raw string values for comparison
            $allowedStringValues = array_map(fn($enum) => $enum->value, $allowedEnums);

            // 2. Look for any product type the user sent that does NOT live in the allowed array
            $illegalChoices = array_diff($productTypes, $allowedStringValues);

            if (count($illegalChoices) > 0) {
                return response()->json([
                    'error' => 'Security Error: One or more selected product types are invalid for your chosen business category.'
                ], 422);
            }
        }

        // --- SAVE TO DATABASE ---
        BusinessType::updateOrCreate(
            ['profile_id' => $profile->id],
            ['business_type' => $request->validated('business_type')]
        );

        //needs correction because preference does not have a profile id
        Preference::where('profile_id', $profile->id)->delete();
        foreach ($productTypes as $type) {
            Preference::create([
                //this needs to be corrected
                'business_type_id' => $profile->id,
                'product_type' => $type
            ]);
        }

        return response()->json(['status' => 'Preferences successfully saved!']);
    }
}
