<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationDeliveryLocation extends Model
{
    use HasFactory;

    protected $table = 'quotation_delivery_locations';

    protected $fillable = [
        'quotation_id',
        'location_name',
        'sequence_order',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }
}

