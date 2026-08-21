<?php

namespace App\Http\Controllers;

use App\Http\Resources\FacilityResource;
use App\Models\EmployeeAnnouncement;
use App\Models\User;
use App\Models\Document;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /**
     * Create a new administrator account.
     */
    public function createAdmin(Request $request): JsonResponse
    {
        $verifiedAt = Carbon::now();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'email_verified_at'=> $verifiedAt,
            'phone_number' => ['required', 'digits:10', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'language_preference' => ['required', 'string', 'in:ar,en'],
        ]);

        $admin = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'role' => 'super_admin', // Mapped to migration enum
            'account_status' => 'approved',
            'language_preference' => $validated['language_preference'],
        ]);

        return response()->json([
            'message' => 'Admin created successfully.',
            'admin' => $admin
        ], 201);
    }

    /**
     * List all accounts that signed up but HAVE NOT completed onboarding.
     */
    public function pendingRequests(): JsonResponse
    {
        $incompleteUsers = User::where('account_status', 'pending')
            ->where('onboarding_complete', false)
            ->whereNotNull('email_verified_at')
            ->select('id', 'first_name', 'last_name', 'email', 'phone_number', 'role', 'created_at')
            ->latest()
            ->paginate(15);

        return response()->json($incompleteUsers);
    }

    /**
     * List accounts that completed onboarding and are ready for Admin review.
     */
    public function completePendingRequests(): JsonResponse
    {
        $readyUsers = User::where('account_status', 'pending')
            ->where('onboarding_complete', true)
            ->whereNotNull('email_verified_at')
            ->with(['facilities:id,user_id,facility_type,business_type,facility_status'])
            ->select('id', 'first_name', 'last_name', 'email', 'phone_number', 'role', 'created_at')
            ->latest()
            ->paginate(15);

        return response()->json($readyUsers);
    }

    public function approvedAccounts(): JsonResponse
    {
        $completeUsers = User::where('account_status', 'approved')
            ->select('id', 'first_name', 'last_name', 'email', 'phone_number', 'role', 'created_at')
            ->latest()
            ->paginate(15);
        return response()->json($completeUsers);
    }
    /**
     * Stream a private document file to authenticated admins.
     */
    public function downloadDocument(int $documentId)
    {
        $document = Document::findOrFail($documentId);

        if (!Storage::disk('local')->exists($document->document_file)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        return Storage::disk('local')->response($document->document_file);
    }

    /**
     * Get complete information for a specific user undergoing review.
     */
    public function showRequest(int $id): JsonResponse
    {
        $user = User::where('account_status', 'pending')
            ->where('onboarding_complete', true)
            ->whereNotNull('email_verified_at')
            ->with([
                'document',             // Personal Identity Document (facility_id = null)
                'facilities' => function ($query) {
                    $query->with(['document', 'categories']);
                },
            ])
            ->findOrFail($id);

        // 1. Personal Identity Document URL
        if ($user->document) {
            $user->document->file_url = route('admin.documents.download', [
                'documentId' => $user->document->id,
            ]);
        }

        // 2. Primary Facility Document URL
        $primaryFacility = $user->facilities->first();
        if ($primaryFacility && $primaryFacility->document) {
            $primaryFacility->document->file_url = route('admin.documents.download', [
                'documentId' => $primaryFacility->document->id,
            ]);
        }

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Approve or reject a user's registration and optionally review documents with expiry dates.
     */
    public function reviewRequest(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'rejection_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:1000'],
            'documents' => ['nullable', 'array'],
            'documents.*.id' => ['required_with:documents', 'exists:documents,id'],
            'documents.*.start_date' => ['nullable', 'date'],
            'documents.*.expiration_date' => ['nullable', 'date', 'after_or_equal:documents.*.start_date'],
            'documents.*.status' => ['required_with:documents', 'in:approved,rejected'],
        ]);

        $user = User::findOrFail($id);

        DB::transaction(function () use ($validated, $user) {

            // Optionally evaluate and set expiration metrics for individual uploaded documents
            if (!empty($validated['documents'])) {
                foreach ($validated['documents'] as $docData) {
                    Document::where('id', $docData['id'])
                        ->where('user_id', $user->id)
                        ->update([
                            'start_date' => $docData['start_date'] ?? null,
                            'expiration_date' => $docData['expiration_date'] ?? null,
                            'status' => $docData['status'],
                        ]);
                }
            }

            if ($validated['action'] === 'approve') {

                if($user->role === 'worker')
                {
                    EmployeeAnnouncement::where('employee_id', $user->id)->update(['status' => 'active']);
                }

                $user->update([
                    'account_status' => 'approved',
                ]);

                // Update associated user facilities status
                $user->facilities()->update(['facility_status' => 'approved']);

                // TODO: Dispatch custom Welcome/Approval Notification email here
            } else {

                $user->update([
                    'account_status' => 'deleted',
                ]);

                // TODO: Dispatch custom Rejection Notification email with $validated['rejection_reason']
            }
        });

        return response()->json([
            'message' => $validated['action'] === 'approve' ? 'User registration approved successfully.' : 'User registration rejected.',
            'account_status' => $user->fresh()->account_status,
        ]);
    }

    //pending facilities
    public function pendingFacilities(){
        $facilities = Facility::where('facility_status', 'pending')
            ->with([
                'owner:id,first_name,last_name,email,phone_number',
                'address',
                'categories',
                'document',
            ])
            ->latest()
            ->paginate(15);
        return FacilityResource::collection($facilities);
    }

    //show facility for review
    public function showFacilityRequest(int $id): JsonResponse
    {
        $facility = Facility::where('facility_status', 'pending')
            ->with([
                'owner:id,first_name,last_name,email,phone_number',
                'address',
                'categories',
                'document',
            ])
            ->findOrFail($id);

        if ($facility->document) {
            $facility->document->file_url = route('admin.documents.download', ['documentId' => $facility->document->id]);
        }

        return response()->json([
            'facility_id' => $facility->id,
            'facility' => $facility,
        ]);
    }

    //approve/reject facility
    public function reviewFacility(Request $request, int $id){
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'rejection_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:1000'],
            'document' => ['required_if:action,approve', 'array'],
            'document.start_date' => ['nullable', 'date'],
            'document.expiration_date' => ['nullable', 'date', 'after_or_equal:document.start_date'],
            'document.document_type' => ['nullable', 'string', 'max:255'],
        ]);

        $facility = Facility::with('document')->findOrFail($id);

        if ($facility->facility_status !== 'pending') {
            return response()->json([
                'message' => 'This facility has already been reviewed.',
                'facility_status' => $facility->facility_status,
            ], 422);
        }

        DB::transaction(function () use ($validated, $facility) {

            if ($validated['action'] === 'approve') {

                /*
                 * The user-uploaded facility document.
                 */
                $submittedDocument = $facility->document;

                if (!$submittedDocument) {
                    throw new \RuntimeException(
                        'Cannot approve facility because no facility document was submitted.'
                    );
                }

                /*
                 * Create the admin-reviewed document record.
                 *
                 * We reuse the same physical file instead of
                 * uploading/copying the file again.
                 */
                Document::create([
                    'user_id' => $facility->user_id,
                    'facility_id' => $facility->id,
                    'document_file' => $submittedDocument->document_file,
                    'document_type' => $validated['document']['document_type'] ?? null,
                    'start_date' => $validated['document']['start_date'] ?? null,
                    'expiration_date' => $validated['document']['expiration_date'] ?? null,
                    'status' => 'approved',
                ]);

                /*
                 * Mark the originally submitted document as reviewed.
                 */
                $submittedDocument->update([
                    'status' => 'approved',
                ]);

                /*
                 * Finally approve the facility.
                 */
                $facility->update([
                    'facility_status' => 'approved',
                ]);

            } else {

                /*
                 * Reject the submitted document.
                 */
                if ($facility->document) {
                    $facility->document->update([
                        'status' => 'rejected',
                    ]);
                }

                /*
                 * Reject the facility.
                 */
                $facility->update([
                    'facility_status' => 'rejected',
                ]);

                /*
                 * IMPORTANT:
                 * rejection_reason currently has nowhere to be stored
                 * in your facilities table.
                 */
            }
        });

        return response()->json([
            'message' => $validated['action'] === 'approve'
                ? 'Facility approved successfully.'
                : 'Facility rejected successfully.',

            'facility' => $facility->fresh()->load([
                'owner',
                'address',
                'categories',
                'document',
            ]),
        ]);

    }
}
