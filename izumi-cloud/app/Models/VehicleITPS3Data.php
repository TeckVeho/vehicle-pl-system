<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleITPS3Data extends Model
{
    use HasFactory;
    protected $table = 'vehicle_itp_s3_data';

    protected $fillable = [
        's3_files_id',
        'vehicle_identification_number',
        'vehicle_id',
        'no_number_plate',
        'start_date_time',
        'end_date_time',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
