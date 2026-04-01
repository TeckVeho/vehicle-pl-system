<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Route;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'courses';

    protected $fillable = [
        'course_code',
        'start_date',
        'end_date',
        'course_type',
        'bin_type',
        'delivery_type',
        'start_time',
        'gate',
        'wing',
        'tonnage',
        'quantity',
        'allowance',
        'department_id',
        'course_flag',
        'address',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function routes()
    {
        return $this->belongsToMany('App\Models\Route', 'course_route', 'course_id', 'route_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
