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
    private function getAllowedProductsByBusiness(string $businessType): array
    {
        return match($businessType) {
            BusinessTypeEnum::PHARMACY->value => [ProductType::MEDICINE, ProductType::MEDICAL_SUPPLIES, ProductType::COSMETICS],
            BusinessTypeEnum::RESTAURANT->value => [ProductType::CANNED_FOODS, ProductType::REFRIGERATED_FOODS, ProductType::FRESH_FOODS, ProductType::BEVERAGES],
            BusinessTypeEnum::SUPERMARKET->value => [ProductType::CANNED_FOODS, ProductType::REFRIGERATED_FOODS, ProductType::FRESH_FOODS, ProductType::BEVERAGES, ProductType::COSMETICS],
            BusinessTypeEnum::CLOTHING_STORE->value => [ProductType::CLOTHING],
            BusinessTypeEnum::ELECTRONICS_STORE->value => [ProductType::ELECTRONICS],
            BusinessTypeEnum::MAKEUP_STORE->value => [ProductType::COSMETICS],
            BusinessTypeEnum::FURNITURE_STORE->value => [ProductType::FURNITURE],
            default => []
        };
    }

    public function getOnboardingOptions(GetOnboardingOptionsRequest $request)
    {
        $user = $request->user();
        $role = $request->validated('role');
        $businessType = $request->validated('business_type');
        Profile::create([
            'user_id' => $user->id,
        ]);

        // Route 1: Warehouse Managers immediately get all product types
        if ($role === 'warehouse_manager') {
            return response()->json([
                'step' => 'choose_product_types',
                'options' => ProductType::cases()
            ]);
        }

        // Route 2: Clients who haven't picked a business type yet
        if ($role === 'client' && !$businessType) {
            return response()->json([
                'step' => 'choose_business_type',
                'options' => BusinessTypeEnum::cases()
            ]);
        }

        // Route 3: Clients who have picked a business type get filtered products
        if ($role === 'client' && $businessType) {
            $allowedProducts = $this->getAllowedProductsByBusiness($businessType);

            return response()->json([
                'step' => 'choose_product_types',
                'options' => $allowedProducts
            ]);
        }

        // Fallback error if role is missing or invalid
        return response()->json(['error' => 'Invalid or missing role parameter.'], 400);
    }

    public function savePreferences(SavePreferencesRequest $request)
    {
        $profile = $request->user()->profile();
        $role = $request->validated('role');
        $productTypes = $request->validated('product_types', []);

        // --- CRITICAL LEGAL VALIDATION FOR WAREHOUSE MANAGERS ---
        if ($role === 'warehouse_manager') {
            $hasMedical = in_array(ProductType::MEDICINE->value, $productTypes) ||
                in_array(ProductType::MEDICAL_SUPPLIES->value, $productTypes);

            $hasNonMedical = count(array_diff($productTypes, [ProductType::MEDICINE->value, ProductType::MEDICAL_SUPPLIES->value])) > 0;

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
            $allowedEnums = $this->getAllowedProductsByBusiness($businessType);

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

        Preference::where('profile_id', $profile->id)->delete();
        foreach ($productTypes as $type) {
            Preference::create([
                'business_type_id' => $profile->id,
                'name' => $type
            ]);
        }

        return response()->json(['status' => 'Preferences successfully saved!']);
    }
}
