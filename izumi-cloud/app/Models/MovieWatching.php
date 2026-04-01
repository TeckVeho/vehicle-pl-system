<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieWatching extends Model
{
    use HasFactory;

    protected $table = 'movie_watching';

    protected $fillable = [
        'movie_id',
        'user_id',
        'is_watching',
        'date',
        'is_like_app',
        'is_like_list',
        'time',
        'export_flag'
    ];


    protected $casts = [
        'data' => 'array'
    ];

    public function movie()
    {
        return $this->hasOne(Movies::class, 'id', 'movie_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
