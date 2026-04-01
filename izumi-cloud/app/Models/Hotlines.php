<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotlines extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hotlines';

    protected $fillable = [
        'username',
        'phone',
        'email',
        'content',
        'check_anonymous_flag',
        'contact_flag'
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
