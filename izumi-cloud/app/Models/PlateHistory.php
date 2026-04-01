<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlateHistory extends Model
{
    use HasFactory;

    protected $table = 'vehicle_no_number_plate_history';

    protected $fillable = [
        'vehicle_id',
        'date',
        'no_number_plate'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

}
