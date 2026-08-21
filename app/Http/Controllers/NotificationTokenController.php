<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationTokenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
        ]);

        $token = $request->user()
            ->notificationTokens()
            ->updateOrCreate(
                [
                    'token' => $validated['token'],
                ],
                [
                    'platform' => $validated['platform'] ?? null,
                ]
            );

        return response()->json([
            'success' => true,
            'message' => 'Notification token registered successfully.',
            'data' => $token,
        ], 201);
    }

    public function destroy(Request $request, string $token)
    {
        $deleted = $request->user()
            ->notificationTokens()
            ->where('token', $token)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Notification token not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification token removed successfully.',
        ]);
    }
}
