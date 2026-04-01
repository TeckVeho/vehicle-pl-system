<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleDataORCAI extends Model
{
    use HasFactory;
    protected $table = 'vehicle_data_orc_ai';

    protected $fillable = [
        'vehicle_id',
        'certificate_number',
        'issue_date',
        'vehicle_identification_number',
        'insurance_period_1',
        'insurance_period_2',
        'address',
        'policyholder',
        'change_item',
        'jurisdiction_store_name_and_location',
        'vehicle_type',
        'location',
        'insurance_fee',
        'financial_institution_name',
        'seal',
        'file_path',
        'file_name',
        'created_at'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
