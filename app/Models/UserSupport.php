<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSupport extends Model
{
    use HasFactory;

    protected $table = 'user_support';

    protected $fillable = [
        'name',
        'email',
        'phone_number',
    ];
}
