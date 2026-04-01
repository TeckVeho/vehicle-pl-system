<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'author_id' => 'sometimes|integer|exists:quotation_staff,id',
            'tonnage_id' => 'sometimes|integer|exists:quotation_master_data,id',
            'departure_location' => 'sometimes|nullable|string|max:255',
            'delivery_locations' => 'sometimes|nullable|array',
            'delivery_locations.*' => 'nullable|string|max:255',
            'total_delivery_cost' => 'sometimes|numeric',
            'gross_profit' => 'sometimes|numeric',
            'monthly_total' => 'sometimes|numeric',
            'tow_way_highway' => 'sometimes|nullable|boolean',
        ];
    }
}