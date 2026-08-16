<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request){
        return response()->json(['data' => $request->user()->notifications], 200);
    }

    public function markAsRead(string $id, /**NotificationService $service**/){
        //$service->markAsRead(Auth::user(), $id);
        return response()->json(['message' => 'Notification marked as read'], 200);
    }

    public function destroy(string $id, /**NotificationService $service**/){
        //$service->destroy(Auth::user(), $id);
        return response()->json(['message' => 'Notification deleted successfully'], 200);
    }
}
