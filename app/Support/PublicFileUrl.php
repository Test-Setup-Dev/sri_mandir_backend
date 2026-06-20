<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class PublicFileUrl
{
    /**
     * Convert a public-disk relative path (e.g. "audios/x.mp3") into an absolute URL.
     * If the value is already a URL, it is returned as-is.
     */
    public static function toUrl(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        return Storage::disk('public')->url(ltrim($value, '/'));
    }

    /**
     * Convert a stored URL (or relative path) back to a public-disk relative path for deletion.
     * Returns null for external URLs that don't belong to the public disk.
     */
    public static function toRelativePathForPublicDisk(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Already a relative path.
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return ltrim($value, '/');
        }

        $configuredBase = config('filesystems.disks.public.url');
        $configuredBase = is_string($configuredBase) ? rtrim($configuredBase, '/') : '';

        $diskBase = rtrim(Storage::disk('public')->url(''), '/');

        foreach (array_filter([$configuredBase, $diskBase]) as $base) {
            $prefix = $base . '/';
            if (str_starts_with($value, $prefix)) {
                return ltrim(substr($value, strlen($prefix)), '/');
            }
        }

        return null;
    }
}

