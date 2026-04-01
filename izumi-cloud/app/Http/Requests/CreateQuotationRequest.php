<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuotationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'author_id' => 'required|integer|exists:quotation_staff,id',
            'tonnage_id' => 'required|integer|exists:quotation_master_data,id',
            'departure_location' => 'nullable|string|max:255',
            'delivery_locations' => 'nullable|array',
            'delivery_locations.*' => 'nullable|string|max:255',
            'total_delivery_cost' => 'required|numeric',
            'gross_profit' => 'required|numeric',
            'monthly_total' => 'required|numeric',
            'tow_way_highway' => 'nullable|boolean',
        ];
    }
}