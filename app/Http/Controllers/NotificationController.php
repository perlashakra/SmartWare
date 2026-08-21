<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request){
        $notifications = $request->user()->notifications()->latest()->paginate(50);
        return response()->json(['data' => $notifications], 200);
    }

    public function markAsRead(Request $request, string $id){
        $notification = $request->user()->notifications()->whereKey($id)->first();
        if(!$notification){
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $notification->markAsRead();
        return response()->json(['success' => true, 'message' => 'Notification marked as read'], 200);
    }

    public function destroy(Request $request, string $id){
        $notification = $request->user()->notifications()->whereKey($id)->first();
        if(!$notification){
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $notification->delete();
        return response()->json(['success' => true, 'message' => 'Notification deleted successfully'], 200);
    }
}
