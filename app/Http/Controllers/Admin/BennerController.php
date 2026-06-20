<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;  

class BennerController extends Controller
{
    // Add a new banner
public function addBanner(Request $request)
{
    try {
        // ✅ Validate request
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        // ✅ Define banner upload folder (inside /public/banners)
        $destinationPath = public_path('banners');

        // Create folder if not exists
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0775, true);
        }

        // ✅ Upload image
        $image = $request->file('image');
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move($destinationPath, $imageName);

        // ✅ Generate full public URL (accessible in browser)
        $baseUrl = rtrim(config('app.url'), '/'); // e.g. https://test.pearl-developer.com/anuweb
        $imageUrl = $baseUrl . '/public/banners/' . $imageName;

        // ✅ Save banner in database
        $banner = \App\Models\Banner::create([
            'title' => $request->title,
            'image' => $imageUrl,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Banner added successfully',
            'banner' => $banner
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}


// Get all banners
public function getBanners()
{
    try {
        // ✅ Fetch all banners (latest first)
        $banners = \App\Models\Banner::orderBy('id', 'DESC')->get();

        if ($banners->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No banners found',
                'banners' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Banners fetched successfully',
            'banners' => $banners
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}

// Delete a banner
public function deleteBanner($id)
{
    try {
        // ✅ Find banner by ID
        $banner = \App\Models\Banner::find($id);

        if (!$banner) {
            return response()->json([
                'status' => false,
                'message' => 'Banner not found'
            ], 404);
        }

        // ✅ Delete banner record
        $banner->delete();

        return response()->json([
            'status' => true,
            'message' => 'Banner deleted successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}

// Update a banner
public function updateBanner(Request $request, $id)
{
    try {
        // ✅ Find banner by ID
        $banner = \App\Models\Banner::find($id);

        if (!$banner) {
            return response()->json([
                'status' => false,
                'message' => 'Banner not found'
            ], 404);
        }

        // ✅ Validate request
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        // ✅ Update title if provided
        if ($request->has('title')) {
            $banner->title = $request->title;
        }

        // ✅ Update image if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/banners', $imageName);
            $imageUrl = asset('storage/banners/' . $imageName);
            $banner->image = $imageUrl;
        }

        // ✅ Save updates
        $banner->save();

        return response()->json([
            'status' => true,
            'message' => 'Banner updated successfully',
            'banner' => $banner
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}


}