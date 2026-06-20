<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories'; 
    protected $fillable = ['name', 'icon', 'description'];

      public function mediaItems()
    {
        return $this->hasMany(MediaItem::class, 'categorie_id', 'id');
    }
}
