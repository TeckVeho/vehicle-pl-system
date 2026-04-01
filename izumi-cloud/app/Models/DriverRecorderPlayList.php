<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-12-27
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverRecorderPlayList extends Model
{
    use HasFactory;

    protected $table = 'driver_recorder_play_list';

    protected $fillable = [
        'driver_play_list_id',
        'driver_recorder_id',
        'position'
    ];

    protected $casts = [
        'data' => 'array'
    ];

}
