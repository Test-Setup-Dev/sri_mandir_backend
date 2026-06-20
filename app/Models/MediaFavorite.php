<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaFavorite extends Model
{
    use HasFactory;

    protected $table = 'media_favorites';
    protected $fillable = ['user_id', 'media_id'];


    public function mediaItem()
    {
        return $this->belongsTo(MediaItem::class, 'media_id');
    }
}
