<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePreferencesRequest;
use App\Enums\BusinessTypeEnum;
use App\Enums\CategoryEnum;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Profile;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    private function getAllowedCategoriesByBusiness(BusinessTypeEnum $businessType): array
    {
        return array_map(fn($enum) => $enum->value, $businessType->categories());
    }

    /**
     * Step 1: Save Category Preferences & Initialize Profile/Facility
     */
    public function savePreferences(SavePreferencesRequest $request): JsonResponse
    {
        $user = $request->user();
        $role = $request->validated('role');
        $selectedCategories = $request->validated('categories', []);

        // --- 1. DETERMINE BUSINESS TYPE BASED ON ROLE ---
        if ($role === 'warehouse_manager' || $role === 'warehouse_admin') {
            $businessType = 'warehouse';
        } else {
            $businessType = $request->validated('business_type');
        }

        // --- 2. LEGAL COMPLIANCE VALIDATION (WAREHOUSE MANAGERS) ---
        if ($role === 'warehouse_manager' || $role === 'warehouse_admin') {
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

        // --- 4. DATABASE TRANSACTION (PROFILE + FACILITY CREATION + PIVOT SYNC) ---
        $facility = DB::transaction(function () use ($user, $businessType, $role, $selectedCategories) {

            // Ensure Profile instance exists
            Profile::firstOrCreate(['user_id' => $user->id]);

            // Create or update draft facility for the onboarding user
            $facility = Facility::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'facility_type' => in_array($role, ['warehouse_manager', 'warehouse_admin']) ? 'warehouse' : 'store',
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

    /**
     * Step 2: Upload Personal ID Verification Documents
     */
    public function uploadIdentityDocument(Request $request): JsonResponse
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'document_type' => ['required', 'string', 'in:national_id,passport,driver_license'],
        ]);

        $user = $request->user();
        $path = $request->file('document')->store("documents/users/{$user->id}", 'public');

        $document = Document::create([
            'user_id' => $user->id,
            'facility_id' => null,
            'document_file' => $path,
            'document_type' => $request->input('document_type'),
            'status' => 'pending',
        ]);

        $this->checkAndFinalizeOnboarding($user);

        return response()->json([
            'message' => 'Identity document uploaded successfully.',
            'document' => $document,
        ], 201);
    }

    /**
     * Step 3: Upload Facility Legal Documents (Ownership, Lease, Contracts)
     */
    public function uploadFacilityDocument(Request $request): JsonResponse
    {
        $request->validate([
            'facility_id' => ['required', 'exists:facilities,id'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'document_type' => ['required', 'string', 'in:ownership_deed,lease_contract,commercial_register,authorization_letter'],
        ]);

        $user = $request->user();

        // Verify facility belongs to user
        $facility = Facility::where('id', $request->input('facility_id'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        $path = $request->file('document')->store("documents/facilities/{$facility->id}", 'public');

        $document = Document::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'document_file' => $path,
            'document_type' => $request->input('document_type'),
            'status' => 'pending',
        ]);

        $this->checkAndFinalizeOnboarding($user);

        return response()->json([
            'message' => 'Facility legal document uploaded successfully.',
            'document' => $document,
        ], 201);
    }

    /**
     * Helper to verify all mandatory document requirements and update onboarding status.
     */
    private function checkAndFinalizeOnboarding($user): void
    {
        $hasIdentityDoc = Document::where('user_id', $user->id)
            ->whereNull('facility_id')
            ->exists();

        $hasFacilityDoc = Document::where('user_id', $user->id)
            ->whereNotNull('facility_id')
            ->exists();

        if ($hasIdentityDoc && $hasFacilityDoc) {
            Profile::where('user_id', $user->id)->update([
                'onboarding_complete' => true
            ]);

            $user->update([
                'identity_status' => 'submitted'
            ]);
        }
    }
}
