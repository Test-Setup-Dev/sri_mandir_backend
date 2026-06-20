<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    { 
        $categories = Category::all();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No categories found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Categories fetched successfully',
            'data' => $categories
        ]);
    }



    // post category
    public function store(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|max:255',
                'icon' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            $category = Category::create([
                'name' => $request->name,
                'icon' => $request->icon,
                'description' => $request->description,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create category: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
}


// update category
public function update(Request $request, $id)
{
    try {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'icon' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found',
                'data' => []
            ], 404);
        }

        $category->update($request->only(['name', 'icon', 'description']));

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update category: ' . $e->getMessage(),
            'data' => []
        ], 500);
    }
}


// delete category
public function destroy($id)
{
    try {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found',
                'data' => []
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully',
            'data' => []
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to delete category: ' . $e->getMessage(),
            'data' => []
        ], 500);
    }
}
}