<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\UserNotificationService;

class AdminNotificationController extends Controller
{
    private string $templatesFile = 'notification_templates.json';

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

    public function listTemplates()
    {
        return response()->json([
            'status' => true,
            'data' => $this->getTemplates(),
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        $templates = $this->getTemplates();
        $now = now()->toDateTimeString();

        $templates[] = [
            'id' => (string) Str::uuid(),
            'name' => $request->name,
            'title' => $request->title,
            'body' => $request->body,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->persistTemplates($templates);

        return response()->json([
            'status' => true,
            'message' => 'Notification template created successfully.',
            'data' => end($templates),
        ], 201);
    }

    public function updateTemplate(Request $request, string $templateId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        $templates = $this->getTemplates();
        $found = false;

        foreach ($templates as &$template) {
            if (($template['id'] ?? null) !== $templateId) {
                continue;
            }

            $template['name'] = $request->name;
            $template['title'] = $request->title;
            $template['body'] = $request->body;
            $template['updated_at'] = now()->toDateTimeString();
            $found = true;
            break;
        }
        unset($template);

        if (!$found) {
            return response()->json([
                'status' => false,
                'message' => 'Notification template not found.',
            ], 404);
        }

        $this->persistTemplates($templates);

        return response()->json([
            'status' => true,
            'message' => 'Notification template updated successfully.',
        ]);
    }

    public function destroyTemplate(string $templateId)
    {
        $templates = $this->getTemplates();
        $filtered = array_values(array_filter($templates, fn ($template) => ($template['id'] ?? null) !== $templateId));

        if (count($filtered) === count($templates)) {
            return response()->json([
                'status' => false,
                'message' => 'Notification template not found.',
            ], 404);
        }

        $this->persistTemplates($filtered);

        return response()->json([
            'status' => true,
            'message' => 'Notification template deleted successfully.',
        ]);
    }
    private function getTemplates(): array
    {
        if (!Storage::disk('local')->exists($this->templatesFile)) {
            return [];
        }

        $decoded = json_decode(Storage::disk('local')->get($this->templatesFile), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function persistTemplates(array $templates): void
    {
        Storage::disk('local')->put(
            $this->templatesFile,
            json_encode($templates, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
