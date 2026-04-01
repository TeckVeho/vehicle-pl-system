<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieUserLike extends Model
{
    use HasFactory;

    protected $table = 'movie_user_like';

    protected $fillable = [
        'movie_id',
        'user_id',
        'like_or_dislike',
        'date'
    ];


    protected $casts = [
        'data' => 'array'
    ];

    public function movie()
    {
        return $this->hasOne(Movies::class, 'id', 'movie_id');
    }
}
