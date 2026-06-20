<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AboutUs;

class AboutUsController extends Controller
{

 public function showAboutUsPage()
    {
        $about = AboutUs::first(); // first row

        return view('about-us', compact('about'));
    }


    // ✅ Get About Us content
    public function getAboutUs()
    {
        $about = AboutUs::first(); // first row
        if (!$about) {
            return response()->json([
                'status' => false,
                'message' => 'About Us content not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $about
        ]);
    }

    // ✅ Add / Update About Us (optional admin)
    public function saveAboutUs(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $about = AboutUs::first();
        if ($about) {
            $about->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);
        } else {
            $about = AboutUs::create([
                'title' => $request->title,
                'description' => $request->description,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'About Us content saved successfully',
            'data' => $about
        ]);
    }
}
