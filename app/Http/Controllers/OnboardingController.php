<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\BusinessType;
use App\Enums\ProductType;
use App\Models\UserPreference;
use App\Models\UserProductType;

class OnboardingController extends Controller
{
    public function getOnboardingOptions(Request $request)
    {
        $role = $request->query('role'); // 'client' or 'warehouse_manager'
        $businessType = $request->query('business_type');

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
            $allowedProducts = match($businessType) {
                BusinessType::PHARMACY->value => [ProductType::MEDICINE, ProductType::MEDICAL_SUPPLIES, ProductType::COSMETICS],
                BusinessType::RESTAURANT->value => [ProductType::CANNED_FOODS, ProductType::REFRIGERATED_FOODS, ProductType::FRESH_FOODS, ProductType::BEVERAGES],
                BusinessType::SUPERMARKET->value => [ProductType::CANNED_FOODS, ProductType::REFRIGERATED_FOODS, ProductType::FRESH_FOODS, ProductType::BEVERAGES, ProductType::COSMETICS],
                BusinessType::CLOTHING_STORE->value => [ProductType::CLOTHING],
                BusinessType::ELECTRONICS_STORE->value => [ProductType::ELECTRONICS],
                BusinessType::MAKEUP_STORE->value => [ProductType::COSMETICS],
                BusinessType::FURNITURE_STORE->value => [ProductType::FURNITURE],
                default => []
            };

            return response()->json([
                'step' => 'choose_product_types',
                'options' => $allowedProducts
            ]);
        }

        // Fallback error if role is missing or invalid
        return response()->json(['error' => 'Invalid or missing role parameter.'], 400);
    }

    public function savePreferences(Request $request)
    {
        $user = $request->user();
        $role = $request->input('role');
        $productTypes = $request->input('product_types', []);

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

        // --- CLIENT VALIDATION ---
        if ($role === 'client') {
            $businessType = $request->input('business_type');

            if ($businessType === BusinessType::PHARMACY->value) {
                foreach ($productTypes as $type) {
                    if (!in_array($type, [ProductType::MEDICINE->value, ProductType::MEDICAL_SUPPLIES->value, ProductType::COSMETICS->value])) {
                        return response()->json(['error' => 'Pharmacies can only select medical or cosmetic product lines.'], 422);
                    }
                }
            }
        }

        // --- SAVE TO DATABASE ---
        UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            ['role' => $role, 'business_type' => $request->input('business_type')]
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
