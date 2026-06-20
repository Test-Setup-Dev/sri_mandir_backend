<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $notifications = UserNotification::query()
            ->where('user_id', $user->id)
            ->latest('sent_at')
            ->latest('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched successfully',
            'data' => $notifications,
        ]);
    }

    public function destroy(Request $request, string $notificationId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $notification = UserNotification::query()
            ->where('user_id', $user->id)
            ->find($notificationId);

        if (!$notification) {
            return response()->json([
                'status' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'status' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }
}
