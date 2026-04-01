<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationMasterData extends Model
{
    use HasFactory;

    protected $table = 'quotation_master_data';

    protected $fillable = [
        'tonnage',
        'car_inspection_price',
        'regular_inspection_price',
        'tire_price',
        'oil_change_price',
        'fuel_unit_price',
    ];

    protected $casts = [
        'car_inspection_price' => 'decimal:2',
        'regular_inspection_price' => 'decimal:2',
        'tire_price' => 'decimal:2',
        'oil_change_price' => 'decimal:2',
        'fuel_unit_price' => 'decimal:2',
    ];
}
