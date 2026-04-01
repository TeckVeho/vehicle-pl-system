<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieViewer extends Model
{
    use HasFactory;
    protected $table = 'movie_viewers';

    protected $fillable = [
        'movie_id',
        'date',
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function movie()
    {
        return $this->hasOne(Movies::class, 'id', 'movie_id');
    }
}
