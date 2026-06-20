<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;

class PrivacyPolicyController extends Controller
{

 public function showPrivacyPolicyPage()
    {
        $policy = PrivacyPolicy::first(); // first row

        return view('privacy-policy', compact('policy'));
    }


    // ✅ Get Privacy Policy content
    public function getPrivacyPolicy()
    {
        $policy = PrivacyPolicy::first(); // first row
        if (!$policy) {
            return response()->json([
                'status' => false,
                'message' => 'Privacy Policy content not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $policy
        ]);
    }

    // ✅ Add / Update Privacy Policy (optional admin)
    public function savePrivacyPolicy(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $policy = PrivacyPolicy::first();
        if ($policy) {
            $policy->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);
        } else {
            $policy = PrivacyPolicy::create([
                'title' => $request->title,
                'description' => $request->description,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Privacy Policy content saved successfully',
            'data' => $policy
        ]);
    }
}
