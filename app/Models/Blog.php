<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'subtitle',
        'content',
        'images',
        'category',
        'author_name',
        'author_image',
        'publish_date',
        'status',
    ];

    protected $casts = [
        'images' => 'array',       // ✅ Automatically decode JSON into array
        'publish_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
