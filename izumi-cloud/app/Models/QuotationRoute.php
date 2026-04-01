<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationRoute extends Model
{
    use HasFactory;

    protected $table = 'quotation_routes';

    protected $fillable = [
        'route_code',
        'user_id',
        'quotation_id',
        'title',
        'start_location',
        'pickup_location',
        'delivery_location',
        'delivery_locations',
        'return_location',
        'start_time',
        'vehicle_type',
        'loading_time_minutes',
        'unloading_time_minutes',
        'user_break_time_minutes',
        'total_distance_km',
        'estimated_end_time',
        'date_change',
        'total_duty_time_hours',
        'actual_working_hours',
        'total_driving_time_minutes',
        'total_handling_time_minutes',
        'total_break_time_minutes',
        'highway_fee',
        'fuel_cost',
        'estimated_total_cost',
        'is_compliant',
        'applied_rule',
        'compliance_note',
        'thinking_process',
        'ai_model_used',
        'calculation_duration_seconds',
        'status',
        'error_message',
        'notes',
    ];

    protected $casts = [
        'date_change' => 'boolean',
        'is_compliant' => 'boolean',
        'delivery_locations' => 'array',
        'total_distance_km' => 'decimal:2',
        'total_duty_time_hours' => 'decimal:2',
        'actual_working_hours' => 'decimal:2',
        'highway_fee' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
        'estimated_total_cost' => 'decimal:2',
        'loading_time_minutes' => 'integer',
        'unloading_time_minutes' => 'integer',
        'user_break_time_minutes' => 'integer',
        'total_driving_time_minutes' => 'integer',
        'total_handling_time_minutes' => 'integer',
        'total_break_time_minutes' => 'integer',
        'calculation_duration_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function locations()
    {
        return $this->hasMany(QuotationRouteLocation::class, 'route_id')->orderBy('sequence_order');
    }

    public function segments()
    {
        return $this->hasMany(QuotationRouteSegment::class, 'route_id')->orderBy('segment_order');
    }

    public function files()
    {
        return $this->hasMany(QuotationRouteFile::class, 'route_id');
    }
}
