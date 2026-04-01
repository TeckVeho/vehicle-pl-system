<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleDepartmentHistory extends Model
{
    use HasFactory;

    protected $table = 'vehicle_department_history';

    protected $fillable = [
        'id',
        'vehicle_id',
        'date',
        'department_id'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }
}
