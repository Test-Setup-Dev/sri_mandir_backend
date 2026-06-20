<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require_once storage_path('../app/Helpers/FirebaseHelper.php'); 

class FirebaseController extends Controller
{
    // Send notification to all users
    public function sendNotificationToAllUsers(Request $request)
{
    $request->validate([
        'title' => 'required|string',
        'body'  => 'required|string',
    ]);

    $title = $request->title;
    $body  = $request->body;

    // Fetch all FCM tokens
    $tokens = DB::table('users')->whereNotNull('fcm_token')->pluck('fcm_token');

    $results = [];
    $successCount = 0;

    foreach ($tokens as $token) {
        $response = sendFirebaseNotification($title, $body, $token);
        $results[] = $response;

        $resp = json_decode($response, true);
        if(isset($resp['name'])){
            $successCount++; // count successful notifications
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Notifications sent!',
        'success_count' => $successCount,
        'total_users' => count($tokens),
        'firebase_responses' => $results
    ]);
}

}
