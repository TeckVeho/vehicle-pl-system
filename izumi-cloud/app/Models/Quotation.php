<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $table = 'quotations';

    protected $fillable = [
        'title',
        'author_id',
        'tonnage_id',
        'departure_location',
        'basic_hours',
        'night_hours',
        'overtime_hours',
        'calc_working_hours',
        'overtime_total',
        'hourly_wage',
        'working_days',
        'monthly_salary',
        'daily_rate',
        'night_total',
        'calc_benefits',
        'calc_total_personnel_cost',
        'loading_location',
        'delivery_location',
        'return_location',
        'start_time',
        'end_time',
        'loading_time',
        'unloading_time',
        'daily_distance',
        'working_hours',
        'break_hours',
        'calc_vehicle_depreciation',
        'vehicle_price',
        'lease_years',
        'residual_value_rate',
        'vehicle_lease',
        'total_vehicle_costs',
        'calc_acquisition_tax',
        'interest_rate',
        'installments',
        'vehicle_weight_tax',
        'automobile_tax',
        'insurance',
        'compulsory_insurance',
        'monthly_cargo_insurance',
        'cargo_insurance',
        'calc_total_taxes',
        'calc_inspection_fee',
        'calc_legal_inspection',
        'calc_tire_cost',
        'tire_replace_distance',
        'calc_oil_cost',
        'oil_replace_distance',
        'calc_fuel_cost',
        'fuel_efficiency',
        'other_repair_costs',
        'calc_total_variable_cost',
        'daily_highway_fee',
        'calc_monthly_highway_fee',
        'total_delivery_cost',
        'gross_profit',
        'calc_repair_cost',
        'monthly_total',
        'tow_way_highway',
    ];

    protected $casts = [
        'tow_way_highway' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(QuotationStaff::class, 'author_id');
    }

    public function quotationMasterData()
    {
        return $this->belongsTo(QuotationMasterData::class, 'tonnage_id');
    }

    public function deliveryLocations()
    {
        return $this->hasMany(QuotationDeliveryLocation::class, 'quotation_id')->orderBy('sequence_order');
    }
}