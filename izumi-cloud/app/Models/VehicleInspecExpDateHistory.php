<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleInspecExpDateHistory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'vehicle_inspec_exp_date_history';

    protected $fillable = [
        'id',
        'vehicle_id',
        'inspection_expiration_date'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_id');
    }
}
