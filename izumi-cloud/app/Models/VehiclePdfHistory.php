<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePdfHistory extends Model
{
    use HasFactory;
    protected $table = 'vehicle_pdf_history';

    protected $fillable = [
        'vehicle_id',
        'file_id',
        'date_pdf',
        'date_json',
        'car_no'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function file()
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }
}
