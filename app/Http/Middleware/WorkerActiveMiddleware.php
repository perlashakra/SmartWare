<?php

namespace App\Http\Middleware;

use App\Models\EmployeeAnnouncement;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkerActiveMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated and is a worker
        if (!$user || $user->role !== 'worker') {
            return response()->json([
                'message' => 'Access denied. Worker role required.'
            ], 403);
        }

        // Check active status on the linked announcement
        $announcement = EmployeeAnnouncement::where('worker_id', $user->id)->first();

        if (!$announcement || $announcement->status !== 'active') {
            return response()->json([
                'message' => 'Your worker account is not active yet or has been terminated.'
            ], 403);
        }

        return $next($request);
    }
}
