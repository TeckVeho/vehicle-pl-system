<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleCost extends Model
{
    use HasFactory;
    protected $table = 'vehicle_costs';

    protected $fillable = [
        'vehicle_id',
        'lease_depreciation',
        'car_tax',
        'maintenance_lease',
        'date',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
