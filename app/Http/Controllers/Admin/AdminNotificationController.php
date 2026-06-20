<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserNotificationService;

class AdminNotificationController extends Controller
{
    public function __construct(private UserNotificationService $notificationService)
    {
    }

    public function sendToAllUsers(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
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
            [
                'delivery_type' => 'broadcast',
                'sent_by_admin_id' => optional($request->user())->id,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Notification broadcast completed.',
            'success_count' => $successCount,
            'total_recipients' => $users->count(),
            'results' => $results,
        ]);
    }

    public function sendToUser(Request $request, string $userId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        $user = User::query()->find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if (blank($user->fcm_token)) {
            return response()->json([
                'status' => false,
                'message' => 'This user does not have a registered FCM token.',
            ], 422);
        }

        [$successCount, $results] = $this->notificationService->sendToUsers(
            collect([$user]),
            $request->title,
            $request->body,
            [
                'delivery_type' => 'direct',
                'sent_by_admin_id' => optional($request->user())->id,
            ]
        );

        return response()->json([
            'status' => $successCount === 1,
            'message' => $successCount === 1
                ? 'Notification sent successfully.'
                : 'Firebase rejected the notification for this user.',
            'success_count' => $successCount,
            'total_recipients' => 1,
            'results' => $results,
        ], $successCount === 1 ? 200 : 422);
    }

    public function storeNotificationRecords(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'broadcast' => 'nullable|boolean',
        ]);

        $broadcast = (bool) $request->boolean('broadcast');
        $userIds = $request->input('user_ids', []);

        if (!$broadcast && empty($userIds)) {
            return response()->json([
                'status' => false,
                'message' => 'Please provide at least one user to store notifications for.',
            ], 422);
        }

        $users = User::query()
            ->when(
                $broadcast,
                fn ($query) => $query->whereNotNull('fcm_token')->where('fcm_token', '!=', ''),
                fn ($query) => $query->whereIn('id', $userIds)
            )
            ->get(['id', 'name', 'fcm_token']);

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No matching users were found for storing notifications.',
            ], 404);
        }

        [$storedCount, $results] = $this->notificationService->storeForUsers(
            $users,
            $request->title,
            $request->body,
            [
                'delivery_type' => $broadcast ? 'broadcast' : 'direct',
                'sent_by_admin_id' => optional($request->user())->id,
                'is_sent' => true,
                'firebase_response' => [
                    'source' => 'admin-panel-fallback-store',
                ],
                'sent_at' => now(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Notification records stored successfully.',
            'stored_count' => $storedCount,
            'results' => $results,
        ]);
    }

    public function listTemplates()
    {
        return response()->json([
            'status' => true,
            'data' => NotificationTemplate::query()->latest('updated_at')->latest('id')->get(),
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        $template = NotificationTemplate::create([
            'name' => $request->name,
            'title' => $request->title,
            'body' => $request->body,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notification template created successfully.',
            'data' => $template,
        ], 201);
    }

    public function updateTemplate(Request $request, string $templateId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        $template = NotificationTemplate::query()->find($templateId);

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Notification template not found.',
            ], 404);
        }

        $template->update([
            'name' => $request->name,
            'title' => $request->title,
            'body' => $request->body,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notification template updated successfully.',
            'data' => $template->fresh(),
        ]);
    }

    public function destroyTemplate(string $templateId)
    {
        $template = NotificationTemplate::query()->find($templateId);

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Notification template not found.',
            ], 404);
        }

        $template->delete();

        return response()->json([
            'status' => true,
            'message' => 'Notification template deleted successfully.',
        ]);
    }
}
