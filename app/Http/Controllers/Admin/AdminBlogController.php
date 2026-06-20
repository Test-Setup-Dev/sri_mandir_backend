<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Storage;
use App\Support\PublicFileUrl;

class AdminBlogController extends Controller
{
    private function normalizeImages($images): array
    {
        if (is_array($images)) {
            return array_values(array_filter($images));
        }

        if (is_string($images) && $images !== '') {
            $decoded = json_decode($images, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded));
            }

            return [$images];
        }

        return [];
    }

    private function transformBlog(Blog $blog): array
    {
        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'subtitle' => $blog->subtitle,
            'content' => $blog->content,
            'category' => $blog->category,
            'images' => $this->normalizeImages($blog->images),
            'author_name' => $blog->author_name,
            'author_image' => $blog->author_image,
            'publish_date' => optional($blog->publish_date)->format('Y-m-d H:i:s'),
            'status' => $blog->status,
            'created_at' => optional($blog->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($blog->updated_at)->format('Y-m-d H:i:s'),
            'user' => $blog->relationLoaded('user') ? $blog->user : null,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Blog::with('user:id,name,email');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('subtitle', 'like', '%' . $request->search . '%');
        }

        $blogs = $query->orderBy('created_at', 'desc')->paginate(15);
        $blogs->setCollection(
            $blogs->getCollection()->map(fn (Blog $blog) => $this->transformBlog($blog))
        );

        return response()->json([
            'status' => true,
            'data' => $blogs
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string',
            'subtitle'     => 'nullable|string',
            'content'      => 'required|string',
            'category'     => 'required|string',
            'author_name'  => 'required|string',
            'publish_date' => 'nullable|date',
            'status'       => 'required|in:published,draft,active,inactive',
            'blog_images'  => 'nullable|array',
            'blog_images.*'=> 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'author_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        try {            // Handle multiple blog images
            $imagePaths = [];
            if ($request->hasFile('blog_images')) {
                $files = $request->file('blog_images');
                if (is_array($files)) {
                    foreach ($files as $image) {
                        $relativePath = $image->store('blogs', 'public');
                        $imagePaths[] = PublicFileUrl::toUrl($relativePath);
                    }
                } else {
                    $relativePath = $files->store('blogs', 'public');
                    $imagePaths[] = PublicFileUrl::toUrl($relativePath);
                }
            }

            $authorImagePath = null;
            if ($request->hasFile('author_image')) {
                $relativePath = $request->file('author_image')->store('authors', 'public');
                $authorImagePath = PublicFileUrl::toUrl($relativePath);
            }

            $status = $request->input('status');
            if ($status === 'published') $status = 'active';
            if ($status === 'draft') $status = 'inactive';

            $blog = Blog::create([
                'user_id'      => $request->user() ? $request->user()->id : null,
                'title'        => $request->input('title'),
                'subtitle'     => $request->input('subtitle'),
                'content'      => $request->input('content'),
                'images'       => $imagePaths,
                'category'     => $request->input('category'),
                'author_name'  => $request->input('author_name'),
                'author_image' => $authorImagePath,
                'publish_date' => $request->input('publish_date') ?: now(),
                'status'       => $status,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Blog created successfully!',
                'data' => $blog
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error saving blog: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['status' => false, 'message' => 'Blog not found'], 404);
        }

        $blog->loadMissing('user:id,name,email');

        return response()->json(['status' => true, 'data' => $this->transformBlog($blog)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return response()->json(['status' => false, 'message' => 'Blog not found'], 404);
            }

            $data = $request->except(['blog_images', 'author_image', '_method']);

            // Handle multiple images if new ones are uploaded
            if ($request->hasFile('blog_images')) {
                // Delete old images
                if (is_array($blog->images)) {
                    foreach ($blog->images as $oldImage) {
                        $relative = PublicFileUrl::toRelativePathForPublicDisk($oldImage);
                        if ($relative) {
                            Storage::disk('public')->delete($relative);
                        }
                    }
                }

                $imagePaths = [];
                $files = $request->file('blog_images');
                if (is_array($files)) {
                    foreach ($files as $image) {
                        $relativePath = $image->store('blogs', 'public');
                        $imagePaths[] = PublicFileUrl::toUrl($relativePath);
                    }
                } else {
                    $relativePath = $files->store('blogs', 'public');
                    $imagePaths[] = PublicFileUrl::toUrl($relativePath);
                }
                $data['images'] = $imagePaths;
            }

            if ($request->hasFile('author_image')) {
                // Delete old author image
                if ($blog->author_image) {
                    $relative = PublicFileUrl::toRelativePathForPublicDisk($blog->author_image);
                    if ($relative) {
                        Storage::disk('public')->delete($relative);
                    }
                }
                $relativePath = $request->file('author_image')->store('authors', 'public');
                $data['author_image'] = PublicFileUrl::toUrl($relativePath);
            }

            if (isset($data['status'])) {
                if ($data['status'] === 'published') $data['status'] = 'active';
                if ($data['status'] === 'draft') $data['status'] = 'inactive';
            }

            $blog->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Blog updated successfully!',
                'data' => $blog
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating blog: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return response()->json(['status' => false, 'message' => 'Blog not found'], 404);
            }

            // Delete associated images
            if (is_array($blog->images)) {
                foreach ($blog->images as $image) {
                    $relative = PublicFileUrl::toRelativePathForPublicDisk($image);
                    if ($relative) {
                        Storage::disk('public')->delete($relative);
                    }
                }
            } elseif (is_string($blog->images) && $blog->images) {
                $relative = PublicFileUrl::toRelativePathForPublicDisk($blog->images);
                if ($relative) {
                    Storage::disk('public')->delete($relative);
                }
            }

            if ($blog->author_image) {
                $relative = PublicFileUrl::toRelativePathForPublicDisk($blog->author_image);
                if ($relative) {
                    Storage::disk('public')->delete($relative);
                }
            }

            $blog->delete();

            return response()->json([
                'status' => true,
                'message' => 'Blog deleted successfully!'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting blog: ' . $e->getMessage()
            ], 500);
        }
    }
}
