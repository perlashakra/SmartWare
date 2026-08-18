<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePreferencesRequest;
use App\Enums\BusinessTypeEnum;
use App\Enums\CategoryEnum;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    /**
     * Get all facilities belonging to the authenticated user.
     */
    public function getAllUserFacilities(Request $request): JsonResponse
    {
        $facilities = $request->user()
            ->facilities()
            ->with('categories')
            ->latest()
            ->get();

        return response()->json([
            'facilities' => $facilities,
        ], 200);
    }

    /**
     * Update the business name for a specific user facility.
     */
    public function editBusinessName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'   => ['required', 'integer', 'exists:facilities,id'],
            'business_name' => ['required', 'string', 'max:255'],
        ]);

        $facility = $request->user()
            ->facilities()
            ->find($validated['facility_id']);

        if (!$facility) {
            return response()->json([
                'error' => 'Facility not found or access denied.'
            ], 404);
        }

        $facility->update([
            'business_name' => $validated['business_name'],
        ]);

        return response()->json([
            'message'  => 'Business name updated successfully.',
            'facility' => $facility,
        ], 200);
    }

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
        $facilityName = $request->validated('facility_name');
        $facilityId = $request->validated('facility_id');

        // 1. RESOLVE EXISTING FACILITY & AUTHORIZE OWNERSHIP
        $existingFacility = null;
        if ($facilityId) {
            $existingFacility = Facility::where('id', $facilityId)
                ->where('user_id', $user->id)
                ->with('categories')
                ->first();

            if (!$existingFacility) {
                return response()->json([
                    'error' => 'Facility not found or access denied.'
                ], 404);
            }
        }

        // 2. DETERMINE PROPOSED BUSINESS TYPE
        if (in_array($role, ['warehouse_manager', 'warehouse_admin'])) {
            $proposedBusinessType = 'warehouse';
        } else {
            $proposedBusinessType = $request->validated('business_type');
        }

        // 3. GET PROPOSED CATEGORIES
        $proposedCategories = $request->validated('categories', []);

        // --- 4. VALIDATION CHECKS ---
        $isValid = true;
        $errorMessage = null;

        // A. Warehouse Legal Compliance
        if (in_array($role, ['warehouse_manager', 'warehouse_admin'])) {
            $medicalValues = [
                CategoryEnum::MEDICINE->value,
                CategoryEnum::PRESCRIPTION_MEDICINE->value,
                CategoryEnum::OVER_THE_COUNTER_MEDICINE->value,
                CategoryEnum::VITAMINS_SUPPLEMENTS->value,
                CategoryEnum::MEDICAL_EQUIPMENT->value,
                CategoryEnum::FIRST_AID_SUPPLIES->value,
                CategoryEnum::SURGICAL_SUPPLIES->value,
            ];

            $hasMedical = count(array_intersect($proposedCategories, $medicalValues)) > 0;
            $hasNonMedical = count(array_diff($proposedCategories, $medicalValues)) > 0;

            if ($hasMedical && $hasNonMedical) {
                $isValid = false;
                $errorMessage = 'Legal compliance error: Under Syrian law, warehouses storing medical supplies/medicines are strictly prohibited from co-storing commercial or non-medical goods.';
            }
        }

        // B. Client Business Category Rules
        if ($role === 'client') {
            $businessTypeEnum = BusinessTypeEnum::from($proposedBusinessType);
            $allowedCategories = $this->getAllowedCategoriesByBusiness($businessTypeEnum);

            $illegalChoices = array_diff($proposedCategories, $allowedCategories);

            if (count($illegalChoices) > 0) {
                $isValid = false;
                $errorMessage = 'Security Error: One or more selected categories are invalid for your chosen business category.';
            }
        }

        // --- 5. HANDLE OVERWRITE OR FALLBACK ---
        if ($isValid) {
            // Validation Passed -> Use new request inputs
            $targetBusinessType = $proposedBusinessType;
            $targetCategories = $proposedCategories;
        } else {
            // Validation Failed
            if ($existingFacility) {
                // Restore existing facility state & reject update
                return response()->json([
                    'error' => $errorMessage,
                    'facility' => $existingFacility,
                ], 422);
            }

            // New facility creation attempt failed -> reject request
            return response()->json(['error' => $errorMessage], 422);
        }

        // --- 6. SAVE OR UPDATE DATABASE RECORD ---
        $facility = DB::transaction(function () use ($user, $role, $existingFacility, $facilityName, $targetBusinessType, $targetCategories) {

            // Target either existing facility ID or create a new row
            $facility = Facility::updateOrCreate(
                ['id' => $existingFacility?->id],
                [
                    'user_id' => $user->id,
                    'facility_type' => in_array($role, ['warehouse_manager', 'warehouse_admin']) ? 'warehouse' : 'business',
                    'facility_name' => $facilityName,
                    'business_type' => $targetBusinessType,
                    'facility_status' => 'pending',
                ]
            );

            $categoryIds = Category::whereIn('name', $targetCategories)->pluck('id');
            $facility->categories()->sync($categoryIds);

            return $facility;
        });

        return response()->json([
            'message' => 'Preferences saved successfully.',
            'facility' => $facility->load('categories'),
            'facility_name' => $facilityName,
        ], 200);
    }

    public function getFacilityPreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'exists:facilities,id']
        ]);

        $user = $request->user();
        $facility = Facility::findOrFail($validated['facility_id']);
        if($facility->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $preferences = $facility->categories;

        return response()->json(['preferences' => $preferences]);
    }

    /**
     * Step 2: Upload Personal ID Verification Documents
     */
    /**
     * Single Combined Upload for Onboarding
     */
    public function uploadOnboardingDocuments(Request $request): JsonResponse
    {
        $request->validate([
            'facility_id' => ['required', 'exists:facilities,id'],
            'identity_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'facility_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $user = $request->user();

        $facility = Facility::where('id', $request->input('facility_id'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        $createdDocuments = DB::transaction(function () use ($request, $user, $facility) {

            // 1. Private storage for Identity Document
            $idPath = $request->file('identity_document')->store("documents/users/{$user->id}", 'local');
            $identityDoc = Document::create([
                'user_id' => $user->id,
                'facility_id' => null,
                'document_file' => $idPath,
                'document_type' => null,
                'status' => 'pending',
            ]);

            // 2. Private storage for Facility Document
            $facilityPath = $request->file('facility_document')->store("documents/facilities/{$facility->id}", 'local');
            $facilityDoc = Document::create([
                'user_id' => $user->id,
                'facility_id' => $facility->id,
                'document_file' => $facilityPath,
                'document_type' => null,
                'status' => 'pending',
            ]);

            return [
                'identity_document' => $identityDoc,
                'facility_document' => $facilityDoc,
            ];
        });

        $this->checkAndFinalizeOnboarding($user);

        return response()->json([
            'message' => 'Onboarding documents uploaded and submitted successfully.',
            'documents' => $createdDocuments,
        ], 201);
    }

    /**
     * Individual Upload: Identity Document
     */
    public function uploadIdentityDocument(Request $request): JsonResponse
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $user = $request->user();
        $path = $request->file('document')->store("documents/users/{$user->id}", 'local');

        $document = Document::create([
            'user_id' => $user->id,
            'facility_id' => null,
            'document_file' => $path,
            'document_type' => null,
            'status' => 'pending',
        ]);

        $this->checkAndFinalizeOnboarding($user);

        return response()->json([
            'message' => 'Identity document uploaded successfully.',
            'document' => $document,
        ], 201);
    }

    /**
     * Individual Upload: Facility Document
     */
    public function uploadFacilityDocument(Request $request): JsonResponse
    {
        $request->validate([
            'facility_id' => ['required', 'exists:facilities,id'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $user = $request->user();

        $facility = Facility::where('id', $request->input('facility_id'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        $path = $request->file('document')->store("documents/facilities/{$facility->id}", 'local');

        $document = Document::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'document_file' => $path,
            'document_type' => null,
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
            $user->update([
                'onboarding_complete' => true,
            ]);
        }
    }
}
