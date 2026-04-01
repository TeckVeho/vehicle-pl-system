<?php

namespace App\Http\Resources;

class QuotationResource extends BaseResource
{
    public function toArray($request)
    {
        $array = parent::toArray($request);
        
        if ($this->relationLoaded('deliveryLocations')) {
            $array['delivery_locations'] = $this->deliveryLocations->map(function($dl) {
                return [
                    'id' => $dl->id,
                    'location_name' => $dl->location_name,
                    'sequence_order' => $dl->sequence_order,
                ];
            })->toArray();
        }
        
        return $array;
    }
}
