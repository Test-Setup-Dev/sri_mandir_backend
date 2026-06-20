<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MediaItem;
use Illuminate\Support\Facades\Storage;
use App\Support\PublicFileUrl;

class AdminMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MediaItem::with('category');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $media = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $media
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title'        => 'required|string|max:255',
            'artist'       => 'nullable|string|max:255',
            'thumbnail'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'media_file'   => 'nullable|file|max:51200', // 50MB
            'media_url'    => 'nullable|string',
            'content'      => 'nullable|string',
            'type'         => 'required|in:audio,video,text',
            'duration'     => 'nullable|string',
            'categorie_id' => 'required|integer',
            'isFeatured'   => 'nullable',
        ]);

        $validator->after(function ($validator) use ($request) {
            $type = $request->input('type');
            $hasMediaFile = $request->hasFile('media_file');
            $hasMediaUrl = filled($request->input('media_url'));
            $hasContent = filled($request->input('content'));

            if (in_array($type, ['audio', 'video'], true) && !$hasMediaFile && !$hasMediaUrl) {
                $validator->errors()->add('media_file', 'Please upload a media file or provide a media URL.');
            }

            if ($type === 'text' && !$hasContent) {
                $validator->errors()->add('content', 'Text media items require content to read.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $thumbnailUrl = null;
            $mediaUrl = $request->media_url;

            if ($request->hasFile('thumbnail')) {
                $thumbnailRelativePath = $request->file('thumbnail')->store('thumbnails', 'public');
                $thumbnailUrl = PublicFileUrl::toUrl($thumbnailRelativePath);
            }

            if ($request->hasFile('media_file')) {
                $folder = $request->type === 'audio' ? 'audios' : ($request->type === 'video' ? 'videos' : 'files');
                $mediaRelativePath = $request->file('media_file')->store($folder, 'public');
                $mediaUrl = PublicFileUrl::toUrl($mediaRelativePath);
            } elseif (!empty($mediaUrl)) {
                // If a relative path is provided instead of a full URL, convert it.
                $mediaUrl = PublicFileUrl::toUrl($mediaUrl);
            }

            // Handle boolean isFeatured from FormData (often comes as '1', '0', 'true', 'false')
            $isFeatured = $request->isFeatured;
            if ($isFeatured === 'true' || $isFeatured === '1' || $isFeatured === 1) {
                $isFeatured = true;
            } else {
                $isFeatured = false;
            }

            $media = MediaItem::create([
                'title'        => $request->title,
                'artist'       => $request->artist,
                'thumbnailUrl' => $thumbnailUrl,
                'mediaUrl'     => $mediaUrl,
                'content'      => $request->type === 'text' ? $request->input('content') : null,
                'type'         => $request->type,
                'duration'     => $request->duration,
                'categorie_id' => $request->categorie_id,
                'isFeatured'   => $isFeatured,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Media item created successfully',
                'data' => $media
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $media = MediaItem::with('category')->find($id);

        if (!$media) {
            return response()->json(['status' => false, 'message' => 'Media not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $media]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $media = MediaItem::find($id);

        if (!$media) {
            return response()->json(['status' => false, 'message' => 'Media not found'], 404);
        }

        $validator = \Validator::make($request->all(), [
            'title'        => 'sometimes|string|max:255',
            'artist'       => 'nullable|string|max:255',
            'thumbnail'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'media_file'   => 'nullable|file|max:51200',
            'media_url'    => 'nullable|string',
            'content'      => 'nullable|string',
            'type'         => 'sometimes|in:audio,video,text',
            'duration'     => 'nullable|string',
            'categorie_id' => 'sometimes|integer',
            'isFeatured'   => 'nullable',
        ]);

        $validator->after(function ($validator) use ($request, $media) {
            $type = $request->input('type', $media->type);
            $hasIncomingMediaFile = $request->hasFile('media_file');
            $hasIncomingMediaUrl = filled($request->input('media_url'));
            $hasExistingMediaUrl = filled($media->mediaUrl);
            $hasContent = $request->has('content')
                ? filled($request->input('content'))
                : filled($media->content);

            if (in_array($type, ['audio', 'video'], true) && !$hasIncomingMediaFile && !$hasIncomingMediaUrl && !$hasExistingMediaUrl) {
                $validator->errors()->add('media_file', 'Please upload a media file or provide a media URL.');
            }

            if ($type === 'text' && !$hasContent) {
                $validator->errors()->add('content', 'Text media items require content to read.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'title', 'artist', 'type', 'duration', 'categorie_id', 'media_url'
            ]);

            if ($request->has('content')) {
                $data['content'] = ($request->input('type', $media->type) === 'text')
                    ? $request->input('content')
                    : null;
            } elseif (($request->input('type', $media->type) !== 'text') && $media->content) {
                $data['content'] = null;
            }

            if ($request->hasFile('thumbnail')) {
                if ($media->thumbnailUrl) {
                    $oldThumbnailRelative = PublicFileUrl::toRelativePathForPublicDisk($media->thumbnailUrl);
                    if ($oldThumbnailRelative) {
                        Storage::disk('public')->delete($oldThumbnailRelative);
                    }
                }
                $thumbnailRelativePath = $request->file('thumbnail')->store('thumbnails', 'public');
                $data['thumbnailUrl'] = PublicFileUrl::toUrl($thumbnailRelativePath);
            }

            if ($request->hasFile('media_file')) {
                $oldMediaRelative = PublicFileUrl::toRelativePathForPublicDisk($media->mediaUrl);
                if ($oldMediaRelative) {
                    Storage::disk('public')->delete($oldMediaRelative);
                }
                $folder = $request->type ?? $media->type;
                $folder = $folder === 'audio' ? 'audios' : ($folder === 'video' ? 'videos' : 'files');
                $mediaRelativePath = $request->file('media_file')->store($folder, 'public');
                $data['mediaUrl'] = PublicFileUrl::toUrl($mediaRelativePath);
            } else if ($request->has('media_url') && !empty($request->media_url)) {
                // If providing a URL/relative path, delete the old file if it was a local file
                $oldMediaRelative = PublicFileUrl::toRelativePathForPublicDisk($media->mediaUrl);
                if ($oldMediaRelative) {
                    Storage::disk('public')->delete($oldMediaRelative);
                }
                $data['mediaUrl'] = PublicFileUrl::toUrl($request->media_url);
            }

            if ($request->has('isFeatured')) {
                $isFeatured = $request->isFeatured;
                if ($isFeatured === 'true' || $isFeatured === '1' || $isFeatured === 1 || $isFeatured === true) {
                    $data['isFeatured'] = true;
                } else {
                    $data['isFeatured'] = false;
                }
            }

            $media->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Media item updated successfully',
                'data' => $media
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $media = MediaItem::find($id);

            if (!$media) {
                return response()->json(['status' => false, 'message' => 'Media not found'], 404);
            }

            if ($media->thumbnailUrl) {
                $thumbnailRelative = PublicFileUrl::toRelativePathForPublicDisk($media->thumbnailUrl);
                if ($thumbnailRelative) {
                    Storage::disk('public')->delete($thumbnailRelative);
                }
            }
            $mediaRelative = PublicFileUrl::toRelativePathForPublicDisk($media->mediaUrl);
            if ($mediaRelative) {
                Storage::disk('public')->delete($mediaRelative);
            }

            $media->delete();

            return response()->json([
                'status' => true,
                'message' => 'Media item deleted successfully'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
