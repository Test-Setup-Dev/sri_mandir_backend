<?php

if (!function_exists('getAccessToken')) {
    function getAccessToken($firebaseKeyPath)
    {
        $googleToken = json_decode(file_get_contents($firebaseKeyPath), true);

        $jwtHeader = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
        $now = time();
        $jwtClaim = rtrim(strtr(base64_encode(json_encode([
            'iss' => $googleToken['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ])), '+/', '-_'), '=');

        $jwtUnsigned = $jwtHeader . '.' . $jwtClaim;
        openssl_sign($jwtUnsigned, $signature, $googleToken['private_key'], 'sha256');
        $jwt = $jwtUnsigned . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }
}

if (!function_exists('sendFirebaseNotification')) {
    function sendFirebaseNotification($title, $body, $token)
    {
        $firebaseKeyPath = storage_path('app/firebase/mindir-8838c-firebase-adminsdk-fbsvc-30b7c7ddfb.json');
        $projectId = 'mindir-8838c';

        $accessToken = getAccessToken($firebaseKeyPath);

        if (!$accessToken) {
            return json_encode(['error' => 'Unable to get access token']);
        }

        $notification = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
                "android" => ["priority" => "high"]
            ]
        ];

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $accessToken,
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if(curl_errno($ch)){
            $error_msg = curl_error($ch);
        }
        curl_close($ch);

        if(isset($error_msg)){
            return json_encode(['curl_error' => $error_msg]);
        }

        if(empty($response)){
            return json_encode(['error' => 'Empty response from FCM']);
        }

        return $response;
    }
}

