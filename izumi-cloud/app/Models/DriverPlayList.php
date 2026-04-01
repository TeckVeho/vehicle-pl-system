<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-11-15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverPlayList extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'driver_play_lists';

    protected $fillable = ['name', 'file_id'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    public function imageFile()
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    public function driverRecorder()
    {
        return $this->belongsToMany(DriverRecorder::class, 'driver_recorder_play_list', 'driver_play_list_id', 'driver_recorder_id')
            ->withPivot('driver_recorder_id', 'driver_play_list_id', 'position')->orderBy('position', 'asc');
    }
}
