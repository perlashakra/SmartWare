<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountApprovedMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->account_status !== 'approved') {
            return response()->json([
                'message' => 'Your account status is pending or not approved.'
            ], 403);
        }

        return $next($request);
    }
}
