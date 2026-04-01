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

class Route extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'routes';

    protected $fillable = [
        'name',
        'department_id',
        'customer_id',
        'route_fare_type',
        'fare',
        'highway_fee',
        'highway_fee_holiday',
        'is_government_holiday',
        'remark'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function route_non_delivery()
    {
        return $this->hasMany(RouteNonDelivery::class, 'route_id', 'id');
    }

    public function routeNonDelivery()
    {
        return $this->hasMany(RouteNonDelivery::class, 'route_id', 'id');
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'route_store', 'route_id', 'store_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_route', 'route_id', 'course_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
