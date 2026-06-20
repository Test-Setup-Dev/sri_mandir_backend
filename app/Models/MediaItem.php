<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\PublicFileUrl;

class MediaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'artist', 'thumbnailUrl', 'mediaUrl', 'type',
        'duration', 'categorie_id', 'isFeatured', 'content'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'categorie_id', 'id');
    }

    public function ratings()
    {
        return $this->hasMany(MediaRating::class, 'media_id', 'id');
    }

    public function favorites()
    {
        return $this->hasMany(MediaFavorite::class, 'media_id', 'id');
    }

    public function setMediaUrlAttribute($value): void
    {
        $this->attributes['mediaUrl'] = PublicFileUrl::toUrl($value);
    }

    public function setThumbnailUrlAttribute($value): void
    {
        $this->attributes['thumbnailUrl'] = PublicFileUrl::toUrl($value);
    }
}
