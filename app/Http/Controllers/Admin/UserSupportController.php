<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserSupport;
use Illuminate\Support\Facades\Validator;

class UserSupportController extends Controller
{
    // ✅ POST API - Add new user support record
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone_number'  => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $support = UserSupport::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'phone_number' => $request->phone_number,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Support record created successfully.',
            'data'    => $support,
        ], 201);
    }

    // ✅ GET API - Fetch all user support records
    public function index()
    {
        $supports = UserSupport::orderBy('id', 'desc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Support records fetched successfully.',
            'data'    => $supports,
        ], 200);
    }

}
