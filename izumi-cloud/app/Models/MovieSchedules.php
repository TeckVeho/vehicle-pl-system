<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieSchedules extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'movie_schedules';

    protected $fillable = [
        'movie_id',
        'date',
        'time',
        'is_send_noti',
        'from',
        'to',
        'assign_type',
        'auto_flag'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function movie()
    {
        return $this->hasOne(Movies::class, 'id', 'movie_id');
    }
}
