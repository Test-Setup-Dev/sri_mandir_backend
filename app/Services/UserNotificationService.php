<?php

namespace App\Services;

use App\Models\UserNotification;
use Illuminate\Support\Collection;

require_once app_path('Helpers/FirebaseHelper.php');

class UserNotificationService
{
    public function sendToUsers(iterable $users, string $title, string $body, array $context = []): array
    {
        $results = [];
        $successCount = 0;
        $usersCollection = $users instanceof Collection ? $users : collect($users);

        foreach ($usersCollection as $user) {
            $response = sendFirebaseNotification($title, $body, $user->fcm_token);
            $decoded = json_decode($response, true);
            $success = is_array($decoded) && isset($decoded['name']);

            if ($success) {
                $successCount++;
            }

            $notification = UserNotification::create([
                'user_id' => $user->id,
                'title' => $title,
                'body' => $body,
                'delivery_type' => $context['delivery_type'] ?? 'direct',
                'sent_by_admin_id' => $context['sent_by_admin_id'] ?? null,
                'is_sent' => $success,
                'firebase_message_id' => $decoded['name'] ?? null,
                'firebase_response' => $decoded ?: ['raw' => $response],
                'sent_at' => now(),
            ]);

            $results[] = [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'success' => $success,
                'response' => $notification->firebase_response,
            ];
        }

        return [$successCount, $results];
    }
}
