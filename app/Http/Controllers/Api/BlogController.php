<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    // ✅ Add Blog
    public function addBlog(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'content' => 'required|string',
                'category' => 'nullable|string|max:100',
                'images' => 'nullable', // array of URLs or uploaded files
                'authorName' => 'nullable|string|max:100',
                'authorImage' => 'nullable', // Can be URL or uploaded file
                'publishDate' => 'nullable|date',
            ]);

            $user = $request->user();
            $imageUrls = [];
            $authorImageUrl = null;

            // ✅ Handle multiple uploaded blog images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('blogimage'), $filename);
                    $imageUrls[] = url('blogimage/' . $filename);
                }
            }

            // ✅ Handle image URLs if provided
            if ($request->filled('images') && is_array($request->images)) {
                foreach ($request->images as $img) {
                    if (filter_var($img, FILTER_VALIDATE_URL)) {
                        $imageUrls[] = $img;
                    }
                }
            }

            // ✅ Handle Author Image (File or URL)
            if ($request->hasFile('authorImage')) {
                $file = $request->file('authorImage');
                $filename = 'author_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('blogimage'), $filename);
                $authorImageUrl = url('blogimage/' . $filename);
            } elseif ($request->filled('authorImage') && filter_var($request->authorImage, FILTER_VALIDATE_URL)) {
                $authorImageUrl = $request->authorImage;
            }

            // ✅ Create the blog record
            $blog = Blog::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'content' => $request->content,
                'category' => $request->category,
                'images' => json_encode($imageUrls),
                'author_name' => $request->authorName,
                'author_image' => $authorImageUrl,
                'publish_date' => $request->publishDate ?? now(),
                'status' => 'active',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Blog added successfully',
                'data' => $blog
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }





    // ✅ Public Blog List
    public function getBlogs()
    {
        try {
            $blogs = Blog::with('user:id,name')
                ->where('status', 'active')
                ->orderBy('id', 'DESC')
                ->get()
                ->map(function ($blog) {
                    return [
                        'id' => $blog->id,
                        'title' => $blog->title,
                        'subtitle' => $blog->subtitle,
                        'content' => $blog->content,
                        'category' => $blog->category,
                        // 'images' => json_decode($blog->images) ?? [],
                        'images' => $blog->images ?? [],
                        'author_name' => $blog->author_name,
                        'author_image' => $blog->author_image,
                        'publish_date' => $blog->publish_date ? $blog->publish_date->format('Y-m-d H:i:s') : null,
                        'user' => $blog->user,
                        'status' => $blog->status,
                        'created_at' => $blog->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'All active blogs fetched successfully',
                'data' => $blogs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }


    // ✅ Blog Detail
    public function getBlogDetail($id)
    {
        try {
            $blog = Blog::with('user:id,name')->find($id);

            if (!$blog) {
                return response()->json([
                    'status' => false,
                    'message' => 'Blog not found'
                ], 404);
            }

            $data = [
                'id' => $blog->id,
                'title' => $blog->title,
                'subtitle' => $blog->subtitle,
                'content' => $blog->content,
                'category' => $blog->category,
                'images' => json_decode($blog->images) ?? [],
                'author_name' => $blog->author_name,
                'author_image' => $blog->author_image,
                'publish_date' => $blog->publish_date ? $blog->publish_date->format('Y-m-d H:i:s') : null,
                'user' => $blog->user,
                'status' => $blog->status,
                'created_at' => $blog->created_at ? $blog->created_at->format('Y-m-d H:i:s') : null,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Blog details fetched successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }


    // ✅ My Blogs
    public function myBlogs(Request $request)
    {
        try {
            $user = $request->user();

            $blogs = Blog::with('user:id,name')
                ->where('user_id', $user->id)
                ->orderBy('id', 'DESC')
                ->get()
                ->map(function ($blog) {
                    return [
                        'id' => $blog->id,
                        'title' => $blog->title,
                        'subtitle' => $blog->subtitle,
                        'content' => $blog->content,
                        'category' => $blog->category,
                        'images' => json_decode($blog->images) ?? [],
                        'author_name' => $blog->author_name,
                        'author_image' => $blog->author_image,
                        'publish_date' => $blog->publish_date ? $blog->publish_date->format('Y-m-d H:i:s') : null,
                        'user' => $blog->user,
                        'status' => $blog->status,
                        'created_at' => $blog->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Your blogs fetched successfully',
                'data' => $blogs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }


    // ✅ Update Blog
    public function updateBlog(Request $request, $id)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return response()->json(['status' => false, 'message' => 'Blog not found'], 404);
            }

            $user = $request->user();
            if ($blog->user_id != $user->id) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
            }

            // ✅ Validation (all fields optional)
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'subtitle' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'category' => 'sometimes|string|max:100',
                'images' => 'nullable', // can be array or file uploads
                'authorName' => 'sometimes|string|max:100',
                'authorImage' => 'nullable', // file or URL
                'publishDate' => 'nullable|date',
            ]);

            $data = [];
            $imageUrls = $blog->images ? json_decode($blog->images, true) : [];
            $authorImageUrl = $blog->author_image;

            // ✅ Handle new blog images (upload)
            if ($request->hasFile('images')) {
                // delete old uploaded images
                foreach ($imageUrls as $img) {
                    $path = public_path('blogimage/' . basename($img));
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
                $imageUrls = [];
                foreach ($request->file('images') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('blogimage'), $filename);
                    $imageUrls[] = url('blogimage/' . $filename);
                }
            }

            // ✅ Handle image URLs (append new URLs if given)
            if ($request->filled('images') && is_array($request->images)) {
                foreach ($request->images as $img) {
                    if (filter_var($img, FILTER_VALIDATE_URL)) {
                        $imageUrls[] = $img;
                    }
                }
            }

            // ✅ Handle author image (file or URL)
            if ($request->hasFile('authorImage')) {
                // delete old author image if exists
                $oldPath = public_path('blogimage/' . basename($authorImageUrl));
                if ($authorImageUrl && file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $file = $request->file('authorImage');
                $filename = 'author_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('blogimage'), $filename);
                $authorImageUrl = url('blogimage/' . $filename);
            } elseif ($request->filled('authorImage') && filter_var($request->authorImage, FILTER_VALIDATE_URL)) {
                $authorImageUrl = $request->authorImage;
            }

            // ✅ Update all provided fields
            $data = array_merge($data, [
                'title' => $request->title ?? $blog->title,
                'subtitle' => $request->subtitle ?? $blog->subtitle,
                'content' => $request->content ?? $blog->content,
                'category' => $request->category ?? $blog->category,
                'images' => json_encode($imageUrls),
                'author_name' => $request->authorName ?? $blog->author_name,
                'author_image' => $authorImageUrl,
                'publish_date' => $request->publishDate ?? $blog->publish_date,
            ]);

            $blog->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Blog updated successfully',
                'data' => $blog
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }



    // ✅ Delete Blog
    public function deleteBlog(Request $request, $id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['status' => false, 'message' => 'Blog not found'], 404);
        }

        $user = $request->user();
        if ($blog->user_id != $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Delete image if exists
        if ($blog->image) {
            Storage::disk('public')->delete($blog->image);
        }

        $blog->delete();

        return response()->json(['status' => true, 'message' => 'Blog deleted successfully']);
    }
}
