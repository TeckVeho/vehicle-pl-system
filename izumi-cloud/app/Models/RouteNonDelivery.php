<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteNonDelivery extends Model
{
    use HasFactory;

    protected $table = 'route_non_delivery';

    protected $fillable = [
        'route_id',
        'number_at',
        'is_week',
    ];

    protected $casts = [
        'data' => 'array'
    ];

}
