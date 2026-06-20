<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;    
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MediaItem;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;


require_once storage_path('../app/Helpers/FirebaseHelper.php'); // helper include


class AdminAuthController extends Controller
{

     protected $assetUrl;
    public function __construct(){
        $this->assetUrl = env('ASSET_URL', '');
    }

    // Register admin
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admin,email',
            'password' => 'required|string|min:6',
        ]);

        $admin = Admin::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Admin registered successfully',
            'admin' => $admin
        ]);
    }

    // Login admin
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if(!$admin || !Hash::check($request->password, $admin->password)){
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'admin' => $admin,
            'token' => $token
        ]);
    }

    





    // Add new media item and notify all users
public function addMediaItem(Request $request)
{
    $request->validate([
        'title'        => 'required|string',
        'artist'       => 'nullable|string',
        'thumbnailUrl' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'mediaFile'    => 'nullable|file|mimes:mp3,wav,mp4,mov,avi|max:51200', // 50MB
        'mediaUrl'     => 'nullable|url',
        'content'      => 'nullable|string',
        'type'         => 'required|in:audio,video,text',
        'duration'     => 'nullable|string',
        'categorie_id' => 'required|integer',
        'isFeatured'   => 'nullable|boolean',
    ]);

    try {
        $thumbnailFullUrl = null;
        $mediaFullUrl = null;

        // ✅ Upload thumbnail
        if ($request->hasFile('thumbnailUrl')) {
            $thumbnail = $request->file('thumbnailUrl');
            $thumbName = time() . '_thumb.' . $thumbnail->getClientOriginalExtension();
            $year = now()->year;
            $month = now()->format('M');
            $thumbFolder = public_path("uploads/thumbnails/{$year}/{$month}");
            if (!file_exists($thumbFolder)) {
                mkdir($thumbFolder, 0777, true);
            }
            $thumbnail->move($thumbFolder, $thumbName);
            $thumbnailPath = "uploads/thumbnails/{$year}/{$month}/" . $thumbName;
            $thumbnailFullUrl = $this->assetUrl . '/' . $thumbnailPath;
        }

        // ✅ Handle media file (audio/video)
        if ($request->hasFile('mediaFile')) {
            $file = $request->file('mediaFile');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $year = now()->year;
            $month = now()->format('M');

            $folderType = $request->type;
            $folderPath = public_path("uploads/{$folderType}/{$year}/{$month}");
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            $file->move($folderPath, $filename);
            $mediaPath = "uploads/{$folderType}/{$year}/{$month}/" . $filename;
            $mediaFullUrl = $this->assetUrl . '/' . $mediaPath;
        }

        // ✅ Handle text content (save as .txt if provided)
        if ($request->type === 'text' && !$mediaFullUrl && !empty($request->content)) {
            $year = now()->year;
            $month = now()->format('M');
            $folderPath = public_path("uploads/text/{$year}/{$month}");
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            $filename = time() . '.txt';
            file_put_contents($folderPath . '/' . $filename, $request->content);
            $mediaPath = "uploads/text/{$year}/{$month}/" . $filename;
            $mediaFullUrl = $this->assetUrl . '/' . $mediaPath;
        }

        // ✅ Create record with full URLs
        $media = MediaItem::create([
            'title'        => $request->title,
            'artist'       => $request->artist,
            'thumbnailUrl' => $thumbnailFullUrl,
            'mediaUrl'     => $mediaFullUrl ?? $request->mediaUrl,
            'content'      => $request->type === 'text' ? $request->content : null,
            'type'         => $request->type,
            'duration'     => $request->duration,
            'categorie_id' => $request->categorie_id,
            'isFeatured'   => $request->isFeatured ?? 0,
        ]);

        // ✅ Notification
        $artistName = $media->artist ?? 'Unknown Artist';
        $mediaType  = ucfirst($media->type);
        $title = "🎵 New $mediaType Added!";
        $body  = "{$media->title} by {$artistName} ({$mediaType})";

        // ✅ Send notifications
        $tokens = DB::table('users')->whereNotNull('fcm_token')->pluck('fcm_token');
        $successCount = 0;

        foreach ($tokens as $token) {
            $response = sendFirebaseNotification($title, $body, $token);
            $resp = json_decode($response, true);
            if (isset($resp['name'])) {
                $successCount++;
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Media item added successfully and notification sent!',
            'media' => $media,
            'total_users' => count($tokens),
            'success_count' => $successCount,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ], 500);
    }
}



// public function addMediaItem(Request $request)
// {
//     $request->validate([
//         'title'        => 'required|string',
//         'artist'       => 'nullable|string',
//         'thumbnailUrl' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
//         'mediaFile'    => 'nullable|file|mimes:mp3,wav,mp4,mov,avi|max:51200', // 50MB max
//         'mediaUrl'     => 'nullable|url',
//         'content'      => 'nullable|string',
//         'type'         => 'required|in:audio,video,text',
//         'duration'     => 'nullable|string',
//         'categorie_id' => 'required|integer',
//         'isFeatured'   => 'nullable|boolean',
//     ]);

//     try {
//         $thumbnailPath = null;
//         $mediaFile = null;

//         // ✅ Upload thumbnail if provided
//         if ($request->hasFile('thumbnailUrl')) {
//             $thumbnail = $request->file('thumbnailUrl');
//             $thumbName = time() . '_thumb.' . $thumbnail->getClientOriginalExtension();
//             $year = now()->year;
//             $month = now()->format('M');
//             $thumbFolder = public_path("uploads/thumbnails/{$year}/{$month}");
//             if (!file_exists($thumbFolder)) {
//                 mkdir($thumbFolder, 0777, true);
//             }
//             $thumbnail->move($thumbFolder, $thumbName);
//             $thumbnailPath = "uploads/thumbnails/{$year}/{$month}/" . $thumbName;
//         }

//         // ✅ Handle media file upload (audio/video)
//         if ($request->hasFile('mediaFile')) {
//             $file = $request->file('mediaFile');
//             $filename = time() . '.' . $file->getClientOriginalExtension();
//             $year = now()->year;
//             $month = now()->format('M');

//             // 📂 Folder based on type
//             if ($request->type === 'audio') {
//                 $folderPath = public_path("uploads/audio/{$year}/{$month}");
//             } elseif ($request->type === 'video') {
//                 $folderPath = public_path("uploads/video/{$year}/{$month}");
//             } else {
//                 $folderPath = public_path("uploads/text/{$year}/{$month}");
//             }

//             if (!file_exists($folderPath)) {
//                 mkdir($folderPath, 0777, true);
//             }

//             $file->move($folderPath, $filename);
//             $mediaFile = "uploads/{$request->type}/{$year}/{$month}/" . $filename;
//         }

//         // ✅ For text type: if content provided, save as .txt file
//         if ($request->type === 'text' && !$mediaFile && !empty($request->content)) {
//             $year = now()->year;
//             $month = now()->format('M');
//             $folderPath = public_path("uploads/text/{$year}/{$month}");
//             if (!file_exists($folderPath)) {
//                 mkdir($folderPath, 0777, true);
//             }
//             $filename = time() . '.txt';
//             file_put_contents($folderPath . '/' . $filename, $request->content);
//             $mediaFile = "uploads/text/{$year}/{$month}/" . $filename;
//         }

//         // ✅ Create media record
//         $media = MediaItem::create([
//             'title'        => $request->title,
//             'artist'       => $request->artist,
//             'thumbnailUrl' => $thumbnailPath,
//             'mediaUrl'     => $mediaFile ?? $request->mediaUrl,
//             'content'      => $request->type === 'text' ? $request->content : null,
//             'type'         => $request->type,
//             'duration'     => $request->duration,
//             'categorie_id' => $request->categorie_id,
//             'isFeatured'   => $request->isFeatured ?? 0,
//         ]);

//         // ✅ Notification
//         $artistName = $media->artist ?? 'Unknown Artist';
//         $mediaType  = ucfirst($media->type);
//         $title = "🎵 New $mediaType Added!";
//         $body  = "{$media->title} by {$artistName} ({$mediaType})";

//         // ✅ Send notifications
//         $tokens = DB::table('users')->whereNotNull('fcm_token')->pluck('fcm_token');
//         $successCount = 0;

//         foreach ($tokens as $token) {
//             $response = sendFirebaseNotification($title, $body, $token);
//             $resp = json_decode($response, true);
//             if (isset($resp['name'])) {
//                 $successCount++;
//             }
//         }

//         // ✅ Prepare response with asset URL
//         $mediaResponse = $media->toArray();

//         if (!empty($mediaResponse['mediaUrl'])) {
//             $mediaResponse['mediaUrl'] = $this->assetUrl . '/' . $mediaResponse['mediaUrl'];
//         }
//         if (!empty($mediaResponse['thumbnailUrl'])) {
//             $mediaResponse['thumbnailUrl'] = $this->assetUrl . '/' . $mediaResponse['thumbnailUrl'];
//         }

//         return response()->json([
//             'status' => true,
//             'message' => 'Media item added successfully and notification sent!',
//             'media' => $mediaResponse,
//             'total_users' => count($tokens),
//             'success_count' => $successCount,
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Something went wrong: ' . $e->getMessage(),
//         ], 500);
//     }
// }




public function dashboard()
    {
        return view('admin.dashboard');
    }

}