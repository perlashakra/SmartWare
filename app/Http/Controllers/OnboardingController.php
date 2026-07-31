<?php

namespace App\Http\Controllers;

use App\Enums\CategoryEnum;
use App\Http\Requests\SavePreferencesRequest;
use App\Models\Category;
use App\Models\Facility;
use App\Enums\BusinessTypeEnum;
use Illuminate\Http\JsonResponse;

class OnboardingController extends Controller
{
    private function getAllowedCategoriesByBusiness(BusinessTypeEnum $businessType): array
    {
        return array_map(fn($enum) => $enum->value, $businessType->categories());
    }

    public function savePreferences(SavePreferencesRequest $request, Facility $facility): JsonResponse
    {
        $role = $request->validated('role');
        $selectedCategories = $request->validated('categories', []); // array of string values

        // --- 1. CRITICAL LEGAL VALIDATION FOR WAREHOUSE MANAGERS ---
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

        // --- 2. DEFENSIVE VALIDATION FOR CLIENTS ---
        if ($role === 'client') {
            $businessTypeEnum = BusinessTypeEnum::from($facility->business_type);
            $allowedCategories = $this->getAllowedCategoriesByBusiness($businessTypeEnum);

            $illegalChoices = array_diff($selectedCategories, $allowedCategories);

            if (count($illegalChoices) > 0) {
                return response()->json([
                    'error' => 'Security Error: One or more selected categories are invalid for your chosen business category.'
                ], 422);
            }
        }

        // --- 3. ATTACH CATEGORIES TO FACILITY ---
        // Fetch matching database IDs for the passed enum strings
        $categoryIds = Category::whereIn('name', $selectedCategories)->pluck('id');

        // Sync to pivot table
        $facility->categories()->sync($categoryIds);

        return response()->json(['status' => 'Preferences successfully saved!']);
    }
}
