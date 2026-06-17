<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetOnboardingOptionsRequest;
use App\Http\Requests\SavePreferencesRequest;
use Illuminate\Http\Request;
use App\Enums\BusinessType;
use App\Enums\ProductType;
use App\Models\UserPreference;
use App\Models\UserProductType;

class OnboardingController extends Controller
{
    private function getAllowedProductsByBusiness(string $businessType): array
    {
        return match($businessType) {
            BusinessType::PHARMACY->value => [ProductType::MEDICINE, ProductType::MEDICAL_SUPPLIES, ProductType::COSMETICS],
            BusinessType::RESTAURANT->value => [ProductType::CANNED_FOODS, ProductType::REFRIGERATED_FOODS, ProductType::FRESH_FOODS, ProductType::BEVERAGES],
            BusinessType::SUPERMARKET->value => [ProductType::CANNED_FOODS, ProductType::REFRIGERATED_FOODS, ProductType::FRESH_FOODS, ProductType::BEVERAGES, ProductType::COSMETICS],
            BusinessType::CLOTHING_STORE->value => [ProductType::CLOTHING],
            BusinessType::ELECTRONICS_STORE->value => [ProductType::ELECTRONICS],
            BusinessType::MAKEUP_STORE->value => [ProductType::COSMETICS],
            BusinessType::FURNITURE_STORE->value => [ProductType::FURNITURE],
            default => []
        };
    }

    public function getOnboardingOptions(GetOnboardingOptionsRequest $request)
    {
        $role = $request->validated('role');
        $businessType = $request->validated('business_type');

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
                'options' => BusinessType::cases()
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
        $user = $request->user();
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
        UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            ['role' => $role, 'business_type' => $request->validated('business_type')]
        );

        UserProductType::where('user_id', $user->id)->delete();
        foreach ($productTypes as $type) {
            UserProductType::create([
                'user_id' => $user->id,
                'product_type' => $type
            ]);
        }

        return response()->json(['status' => 'Preferences successfully saved!']);
    }
}
