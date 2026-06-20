<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MediaRating;
use App\Models\MediaItem;

class RatingController extends Controller
{
    public function rateMedia(Request $request)
{
    try {
        $user = $request->user(); // ✅ Authenticated user via Sanctum

        // Validate input
        $request->validate([
            'media_id' => 'required|exists:media_items,id',
            'rating'   => 'required|integer|min:1|max:5',
        ]);

        // Check if user already rated this media
        $existing = MediaRating::where('user_id', $user->id)
            ->where('media_id', $request->media_id)
            ->first();

        if ($existing) {
            $existing->update(['rating' => $request->rating]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Rating updated successfully',
            ]);
        }

        // New rating
        MediaRating::create([
            'user_id'  => $user->id,
            'media_id' => $request->media_id,
            'rating'   => $request->rating,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Rating added successfully',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // ✅ Handle validation errors separately
        return response()->json([
            'status'  => 'error',
            'message' => $e->validator->errors()->first(),
        ], 422);
    } catch (\Exception $e) {
        // ✅ Handle any other unexpected error
        return response()->json([
            'status'  => 'error',
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ], 500);
    }
}

}
