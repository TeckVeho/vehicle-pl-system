<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationRouteSegment extends Model
{
    protected $table = 'quotation_route_segments';

    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'from_location_id',
        'to_location_id',
        'segment_order',
        'distance_km',
        'driving_time_minutes',
        'highway_fee',
        'fuel_cost',
        'road_type',
        'highway_name',
        'route_description',
        'traffic_condition',
        'weather_condition',
        'notes',
    ];

    protected $casts = [
        'segment_order' => 'integer',
        'distance_km' => 'decimal:2',
        'driving_time_minutes' => 'integer',
        'highway_fee' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
    ];

    public function route()
    {
        return $this->belongsTo(QuotationRoute::class, 'route_id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(QuotationRouteLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(QuotationRouteLocation::class, 'to_location_id');
    }
}