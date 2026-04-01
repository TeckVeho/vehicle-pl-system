<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationMasterDataRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'carInspectionPrice' => 'sometimes|numeric|min:0',
            'regularInspectionPrice' => 'sometimes|numeric|min:0',
            'tirePrice' => 'sometimes|numeric|min:0',
            'oilChangePrice' => 'sometimes|numeric|min:0',
            'fuelUnitPrice' => 'sometimes|numeric|min:0',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'car_inspection_price' => $this->carInspectionPrice ?? null,
            'regular_inspection_price' => $this->regularInspectionPrice ?? null,
            'tire_price' => $this->tirePrice ?? null,
            'oil_change_price' => $this->oilChangePrice ?? null,
            'fuel_unit_price' => $this->fuelUnitPrice ?? null,
        ]);
    }
}
