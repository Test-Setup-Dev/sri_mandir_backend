<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'payment_id',
        'order_id',
        'amount',
        'currency',
		'status',
        'created_at',
        'updated_at',
    ];

    // ✅ Define the user relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    
}
