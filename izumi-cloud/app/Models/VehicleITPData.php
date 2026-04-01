<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleITPData extends Model
{
    use HasFactory;
    protected $table = 'vehicle_itp_data';

    protected $fillable = [
        'type',
        'vehicle_id',
        'department_id',
        'year',
        'month',
        'cost',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
