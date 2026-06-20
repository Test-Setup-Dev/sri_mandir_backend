<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TermsCondition;

class TermsConditionController extends Controller
{
    // Web page
    public function showTermsPage()
    {
        $terms = TermsCondition::first();
        return view('terms-conditions', compact('terms'));
    }

    // API
    public function getTermsApi()
    {
        $terms = TermsCondition::first();
        return response()->json(['status' => true, 'data' => $terms]);
    }


    public function saveOrUpdateTerms(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

      
        $terms = TermsCondition::first();
        if ($terms) {
        
            $terms->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);
        } else {
        
            $terms = TermsCondition::create([
                'title' => $request->title,
                'description' => $request->description,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Terms & Conditions saved successfully',
            'data' => $terms
        ]);
    }

}
