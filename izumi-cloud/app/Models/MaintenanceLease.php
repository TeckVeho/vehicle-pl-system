<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceLease extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'maintenance_leases';

    protected $fillable = [
        'vehicle_id',
        'no_number_plate',
        'department_id',
        'start_of_leasing',
        'end_of_leasing',
        'leasing_period',
        'leasing_company',
        'garage',
        'tel'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function getFillableFields() {
        return (new static())->getFillable();
    }
}
