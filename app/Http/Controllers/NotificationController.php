<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request){
        $notifications = $request->user()->notifications()->latest()->get();
        return response()->json(['data' => $notifications], 200);
    }

    public function markAsRead(Request $request, string $id){
        $notification = $request->user()->notifications()->find($id);
        if(!$notification){
            return response()->json(['message' => 'Notification not found'], 404);
        }
        $notification->markAsRead();
        return response()->json(['message' => 'Notification marked as read'], 200);
    }

    public function destroy(string $id, /**NotificationService $service**/){
        //$service->destroy(Auth::user(), $id);
        return response()->json(['message' => 'Notification deleted successfully'], 200);
    }
}
