<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnboardingCompleteMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user exists and has a profile with onboarding completed
        if (!$user || !$user->profile || !$user->profile->onboarding_complete) {
            return response()->json([
                'message' => 'Onboarding process must be completed first.'
            ], 403);
        }

        return $next($request);
    }
}
