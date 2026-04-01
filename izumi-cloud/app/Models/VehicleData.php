<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleData extends Model
{
    use HasFactory;
    protected $table = 'vehicles_datas';

    protected $fillable = [
        'vehicle_id',
        'tire_replacement_date',
        'starter_motor',
        'starter_motor_date',
        'alternator',
        'alternator_date',
        'glass',
        'glass_date',
        'body_id',
        'body_id_date',
        'camera_monitor',
        'camera_monitor_date',
        'gate',
        'gate_date',
        'other',
        'other_date',
        'remark_01',
        'remark_02',
        'created_by',
        'updated_by'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
