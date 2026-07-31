<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePreferencesRequest;
use App\Enums\BusinessTypeEnum;
use App\Enums\CategoryEnum;
use App\Models\Category;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    private function getAllowedCategoriesByBusiness(BusinessTypeEnum $businessType): array
    {
        return array_map(fn($enum) => $enum->value, $businessType->categories());
    }

    public function savePreferences(SavePreferencesRequest $request): JsonResponse
    {
        $user = $request->user();
        $role = $request->validated('role');
        $selectedCategories = $request->validated('categories', []);

        // --- 1. DETERMINE BUSINESS TYPE BASED ON ROLE ---
        if ($role === 'warehouse_manager') {
            $businessType = 'warehouse';
        } else {
            // Client business type validated by SavePreferencesRequest
            $businessType = $request->validated('business_type');
        }

        // --- 2. LEGAL COMPLIANCE VALIDATION (WAREHOUSE MANAGERS) ---
        if ($role === 'warehouse_manager') {
            $medicalValues = [
                CategoryEnum::MEDICINE->value,
                CategoryEnum::PRESCRIPTION_MEDICINE->value,
                CategoryEnum::OVER_THE_COUNTER_MEDICINE->value,
                CategoryEnum::VITAMINS_SUPPLEMENTS->value,
                CategoryEnum::MEDICAL_EQUIPMENT->value,
                CategoryEnum::FIRST_AID_SUPPLIES->value,
                CategoryEnum::SURGICAL_SUPPLIES->value,
            ];

            $hasMedical = count(array_intersect($selectedCategories, $medicalValues)) > 0;
            $hasNonMedical = count(array_diff($selectedCategories, $medicalValues)) > 0;

            if ($hasMedical && $hasNonMedical) {
                return response()->json([
                    'error' => 'Legal compliance error: Under Syrian law, warehouses storing medical supplies/medicines are strictly prohibited from co-storing commercial or non-medical goods.'
                ], 422);
            }
        }

        // --- 3. SECURITY VALIDATION (CLIENTS) ---
        if ($role === 'client') {
            $businessTypeEnum = BusinessTypeEnum::from($businessType);
            $allowedCategories = $this->getAllowedCategoriesByBusiness($businessTypeEnum);

            $illegalChoices = array_diff($selectedCategories, $allowedCategories);

            if (count($illegalChoices) > 0) {
                return response()->json([
                    'error' => 'Security Error: One or more selected categories are invalid for your chosen business category.'
                ], 422);
            }
        }

        // --- 4. DATABASE TRANSACTION (FACILITY CREATION + PIVOT SYNC) ---
        $facility = DB::transaction(function () use ($user, $businessType, $role, $selectedCategories) {

            // Create or update draft facility for the onboarding user
            $facility = Facility::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'facility_type' => $role === 'warehouse_manager' ? 'warehouse' : 'store',
                    'business_type' => $businessType,
                    'facility_status' => 'pending',
                ]
            );

            // Translate category enum strings into DB Category IDs
            $categoryIds = Category::whereIn('name', $selectedCategories)->pluck('id');

            // Sync categories to pivot table (facility_category)
            $facility->categories()->sync($categoryIds);

            return $facility;
        });

        return response()->json([
            'status' => 'Preferences saved and facility draft created successfully!',
            'facility_id' => $facility->id,
        ], 200);
    }
}
