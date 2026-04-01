<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2024-05-08
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movies extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'movies';

    protected $fillable = [
        'id',
        'file_id',
        'thumbnail_file_id',
        'title',
        'content',
        'position',
        'tag',
        'file_length',
        'is_loop_enabled'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array',
        'is_loop_enabled' => 'boolean'
    ];

    public function movieFile()
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    public function thumbnail()
    {
        return $this->hasOne(File::class, 'id', 'thumbnail_file_id');
    }

    public function movieUserLike()
    {
        return $this->hasOne(MovieUserLike::class, 'movie_id', 'id');
    }

    public function movieWatching()
    {
        return $this->hasMany(MovieWatching::class, 'movie_id', 'id');
    }
}
