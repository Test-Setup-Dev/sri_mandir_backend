<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Support\PublicFileUrl;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->latest()->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'pincode' => 'nullable|string|max:10',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'about' => 'nullable|string',
            'activated' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $relativePath = $request->file('image')->store('users', 'public');
                $imagePath = PublicFileUrl::toUrl($relativePath);
            }

            $activated = $request->input('activated');
            if ($activated === 'false' || $activated === '0' || $activated === 0) {
                $activated = false;
            } else {
                $activated = true;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'city' => $request->city,
                'address' => $request->address,
                'pincode' => $request->pincode,
                'state' => $request->state,
                'country' => $request->country,
                'gender' => $request->gender,
                'about' => $request->about,
                'activated' => $activated,
                'image' => $imagePath,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'pincode' => 'nullable|string|max:10',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'about' => 'nullable|string',
            'activated' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'name', 'email', 'phone', 'city', 'address', 
                'pincode', 'state', 'country', 'gender', 'about'
            ]);

            if ($request->has('activated')) {
                $activated = $request->input('activated');
                if ($activated === 'true' || $activated === '1' || $activated === 1 || $activated === true) {
                    $data['activated'] = true;
                } else {
                    $data['activated'] = false;
                }
            }

            if ($request->hasFile('image')) {
                if ($user->image) {
                    $relative = PublicFileUrl::toRelativePathForPublicDisk($user->image);
                    if ($relative) {
                        Storage::disk('public')->delete($relative);
                    }
                }
                $relativePath = $request->file('image')->store('users', 'public');
                $data['image'] = PublicFileUrl::toUrl($relativePath);
            }

            if ($request->has('password') && !empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            }
            
            $user->update($data);

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        try {
            if ($user->image) {
                $relative = PublicFileUrl::toRelativePathForPublicDisk($user->image);
                if ($relative) {
                    Storage::disk('public')->delete($relative);
                }
            }
            $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
