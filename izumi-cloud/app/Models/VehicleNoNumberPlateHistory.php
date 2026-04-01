<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleNoNumberPlateHistory extends Model
{
    protected $table = 'vehicle_no_number_plate_history';

    protected $fillable = [
        'vehicle_id',
        'date',
        'no_number_plate'
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }
}

