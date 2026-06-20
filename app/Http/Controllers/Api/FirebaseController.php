<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\UserNotificationService;

class FirebaseController extends Controller
{
    public function __construct(private UserNotificationService $notificationService)
    {
    }

    // Send notification to all users
    public function sendNotificationToAllUsers(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body'  => 'required|string',
        ]);

        $users = User::query()
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get(['id', 'name', 'fcm_token']);

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No users with FCM tokens were found.',
            ], 404);
        }

        [$successCount, $results] = $this->notificationService->sendToUsers(
            $users,
            $request->title,
            $request->body,
            ['delivery_type' => 'broadcast']
        );

        return response()->json([
            'status' => true,
            'message' => 'Notifications sent!',
            'success_count' => $successCount,
            'total_users' => $users->count(),
            'results' => $results,
        ]);
    }

}
