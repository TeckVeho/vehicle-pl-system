<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationRouteLocation extends Model
{
    protected $table = 'quotation_route_locations';

    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'sequence_order',
        'location_type',
        'location_name',
        'address',
        'prefecture',
        'city',
        'latitude',
        'longitude',
        'arrival_time',
        'departure_time',
        'stay_duration_minutes',
        'distance_from_previous_km',
        'travel_time_from_previous_min',
        'contact_name',
        'contact_phone',
        'notes',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'stay_duration_minutes' => 'integer',
        'distance_from_previous_km' => 'decimal:2',
        'travel_time_from_previous_min' => 'integer',
    ];

    public function route()
    {
        return $this->belongsTo(QuotationRoute::class, 'route_id');
    }

    public function segmentsFrom()
    {
        return $this->hasMany(QuotationRouteSegment::class, 'from_location_id');
    }

    public function segmentsTo()
    {
        return $this->hasMany(QuotationRouteSegment::class, 'to_location_id');
    }
}
