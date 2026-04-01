<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-11-10
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverRecorder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'driver_recorders';

    protected $fillable = [
        'department_id',
        'record_date',
        'title',
        'type',
        'remark',
        'excel_file_id',
        'type_one',
        'type_two',
        'shipper',
        'accident_classification',
        'place_of_occurrence',
        'is_draft',
        'flag_send_noti',
        'crew_member_id'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    public function file()
    {
        return $this->belongsToMany(File::class, 'driver_recorder_file', 'driver_recorder_id', 'file_id')
            ->withPivot('type', 'group_position', 'movie_title')->orderBy('group_position');

    }

    public function driverRecorderImages()
    {
        return $this->belongsToMany(File::class, 'driver_recorder_images', 'driver_recorder_id', 'file_id');
    }

    public function driverPlayList()
    {
        return $this->belongsToMany(DriverPlayList::class, 'driver_recorder_play_list', 'driver_recorder_id', 'driver_play_list_id')
            ->withPivot('driver_recorder_id', 'driver_play_list_id', 'position')->orderBy('position','asc');
    }

    public function excel()
    {
        return $this->hasOne(File::class, 'id', 'excel_file_id');
    }

    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }

    public function crewMember()
    {
        return $this->hasOne(User::class, 'id', 'crew_member_id');
    }
}
