<?php

namespace App\Services;

use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

require_once app_path('Helpers/FirebaseHelper.php');

class UserNotificationService
{
    public function storeForUsers(iterable $users, string $title, string $body, array $context = []): array
    {
        $results = [];
        $usersCollection = $users instanceof Collection ? $users : collect($users);

        foreach ($usersCollection as $user) {
            $notification = UserNotification::create([
                'user_id' => (int) $user->id,
                'title' => $title,
                'body' => $body,
                'delivery_type' => $context['delivery_type'] ?? 'direct',
                'sent_by_admin_id' => $context['sent_by_admin_id'] ?? null,
                'is_sent' => $context['is_sent'] ?? false,
                'firebase_message_id' => $context['firebase_message_id'] ?? null,
                'firebase_response' => $context['firebase_response'] ?? null,
                'sent_at' => $context['sent_at'] ?? now(),
            ]);

            $results[] = [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'user_name' => $user->name ?? null,
                'success' => (bool) $notification->is_sent,
                'response' => $notification->firebase_response,
            ];
        }

        return [count($results), $results];
    }

    public function sendToUsers(iterable $users, string $title, string $body, array $context = []): array
    {
        $results = [];
        $successCount = 0;
        $usersCollection = $users instanceof Collection ? $users : collect($users);

        foreach ($usersCollection as $user) {
            $notification = UserNotification::create([
                'user_id' => (int) $user->id,
                'title' => $title,
                'body' => $body,
                'delivery_type' => $context['delivery_type'] ?? 'direct',
                'sent_by_admin_id' => $context['sent_by_admin_id'] ?? null,
                'is_sent' => false,
                'firebase_message_id' => null,
                'firebase_response' => null,
                'sent_at' => now(),
            ]);

            try {
                $response = sendFirebaseNotification($title, $body, $user->fcm_token);
                $decoded = json_decode($response, true);
                $success = is_array($decoded) && isset($decoded['name']);

                if ($success) {
                    $successCount++;
                }

                $notification->update([
                    'is_sent' => $success,
                    'firebase_message_id' => $decoded['name'] ?? null,
                    'firebase_response' => $decoded ?: ['raw' => $response],
                ]);
            } catch (\Throwable $exception) {
                Log::error('Notification send/store failed', [
                    'user_id' => $user->id ?? null,
                    'title' => $title,
                    'error' => $exception->getMessage(),
                ]);

                $success = false;
                $notification->update([
                    'is_sent' => false,
                    'firebase_response' => [
                        'exception' => $exception->getMessage(),
                    ],
                ]);
            }

            $results[] = [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'user_name' => $user->name ?? null,
                'success' => $success,
                'response' => $notification->firebase_response,
            ];
        }

        return [$successCount, $results];
    }
}
