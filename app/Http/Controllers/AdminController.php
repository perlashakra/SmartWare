<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /**
     * Create a new administrator account.
     */
    public function createAdmin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $admin = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        return response()->json([
            'message' => 'Admin created successfully.',
            'admin' => $admin
        ], 201);
    }

    /**
     * List all users with a pending registration status.
     */
    public function pendingRequests(): JsonResponse
    {
        // Eager load simple relations if needed for the list view
        $pendingUsers = User::where('account_status', 'pending')
            ->select('id', 'first_name', 'last_name', 'email', 'business_name', 'role', 'created_at')
            ->latest()
            ->paginate(15);

        return response()->json($pendingUsers);
    }

    /**
     * Get complete information for a specific pending user registration.
     */
    public function showRequest(int $id): JsonResponse
    {
        // Load the user alongside documents, business types, and product preferences
        $user = User::where('status', 'pending')
            ->with(['documents', 'businessTypes', 'productPreferences'])
            ->findOrFail($id);

        // Map document paths to absolute URLs for your partner's React frontend
        if ($user->relationLoaded('documents')) {
            $user->documents->map(function ($doc) {
                $doc->file_url = $doc->file_path ? asset('storage/' . $doc->file_path) : null;
                return $doc;
            });
        }

        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Approve or reject a user's registration request.
     */
    public function reviewRequest(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'rejection_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:1000'],
        ]);

        $user = User::findOrFail($id);

        if ($validated['action'] === 'approve') {
            $user->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // TODO: Dispatch custom Welcome/Approval Notification email here

            return response()->json([
                'message' => 'User registration approved successfully.',
                'user_status' => $user->status
            ]);
        }

        $user->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // TODO: Dispatch custom Rejection Notification email with the reason here

        return response()->json([
            'message' => 'User registration rejected.',
            'user_status' => $user->status
        ]);
    }
}
