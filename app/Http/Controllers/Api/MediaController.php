<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // ✅ Correct import
use Illuminate\Http\Request;
use App\Models\MediaItem;
use App\Models\MediaFavorite;
use App\Models\MediaRating;

class MediaController extends Controller
{


    
public function getHomeData()
{
    try {
        $categories = \App\Models\Category::with(['mediaItems' => function ($query) {
            $query->select('id', 'title', 'artist', 'thumbnailUrl', 'mediaUrl', 'type', 'duration', 'categorie_id', 'isFeatured', 'content');
        }])->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No categories found',
                'data' => [],
            ], 404);
        }

        foreach ($categories as $category) {
            foreach ($category->mediaItems as $item) {

                // ✅ Average rating
                $item->average_rating = \App\Models\MediaRating::where('media_id', $item->id)->avg('rating') ?? 0;
                $item->average_rating = round($item->average_rating, 1);

                // ✅ Convert text content into line array (only for text type)
                if ($item->type === 'text' && !empty($item->content)) {

                    // Break content by punctuation or newlines
                    $lines = preg_split('/(?<=[.?!])\s+|\n+/', $item->content, -1, PREG_SPLIT_NO_EMPTY);

                    // Clean up lines
                    $lines = array_map('trim', $lines);

                    $item->content = $lines; // replace string with array
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Home data fetched successfully',
            'data' => $categories,
        ]);

    } catch (\Illuminate\Database\QueryException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage(),
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ], 500);
    }
}




public function toggleFavorite(Request $request)
{
    try {
        $user = $request->user(); // ✅ Authenticated user via Sanctum

        $request->validate([
            'media_id' => 'required|exists:media_items,id',
        ]);

        $existing = MediaFavorite::where('user_id', $user->id)
            ->where('media_id', $request->media_id)
            ->first();

        if ($existing) {
            // ✅ Unfavorite (remove record)
            $existing->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Removed from favorites successfully',
                'is_favorite' => false,
            ]);
        }

        // ✅ Add to favorites
        MediaFavorite::create([
            'user_id'  => $user->id,
            'media_id' => $request->media_id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Added to favorites successfully',
            'is_favorite' => true,
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->validator->errors()->first(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ], 500);
    }
}


// get  my favorite media items
public function myFavorite(Request $request)
{
    try {
        $user = $request->user(); // ✅ Authenticated user via Sanctum

        // ✅ Fetch favorite media items with their details
        $favorites = MediaFavorite::where('user_id', $user->id)
            ->with(['mediaItem' => function ($query) {
                $query->select('id', 'title', 'artist', 'thumbnailUrl', 'mediaUrl', 'type', 'duration', 'categorie_id', 'isFeatured');
            }])
            ->get();

        // ✅ If no favorites found
        if ($favorites->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No favorite media items found',
                'data' => [],
            ], 404);
        }

        // ✅ Append average rating for each favorite media item
        foreach ($favorites as $favorite) {
            if ($favorite->mediaItem) {
                $favorite->mediaItem->average_rating = \App\Models\MediaRating::where('media_id', $favorite->mediaItem->id)->avg('rating') ?? 0;
                $favorite->mediaItem->average_rating = round($favorite->mediaItem->average_rating, 1);
            }
        }

        // ✅ Success response
        return response()->json([
            'status' => 'success',
            'message' => 'Favorite media items fetched successfully',
            'data' => $favorites->pluck('mediaItem'), // Return only media item details
        ]);

    } catch (\Exception $e) {
        // ✅ General unexpected errors
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ], 500);
    }

}

public function getItems(Request $request)
{
    try {
        $query = MediaItem::with('category');

        if ($request->has('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No items found',
                'data' => [],
            ], 404);
        }

        foreach ($items as $item) {
            $item->average_rating = round(MediaRating::where('media_id', $item->id)->avg('rating') ?? 0, 1);

            if ($item->type === 'text' && !empty($item->content)) {
                $lines = preg_split('/(?<=[.?!])\s+|\n+/', $item->content, -1, PREG_SPLIT_NO_EMPTY);
                $item->content = array_map('trim', $lines);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Items fetched successfully',
            'data' => $items,
        ]);

    } catch (\Illuminate\Database\QueryException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage(),
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ], 500);
    }
}

public function index(){
        return view('admin.media');
    }
}