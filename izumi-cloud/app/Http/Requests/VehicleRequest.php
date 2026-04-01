<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-12-02
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class VehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch (Route::getCurrentRoute()->getActionMethod()) {
            case 'update':
                return $this->getCustomRule();
            case 'store':
                return $this->getCustomRule();
            default:
                return [];
        }
    }

    public function getCustomRule()
    {
        if (Route::getCurrentRoute()->getActionMethod() == 'update') {
            return [
                'department_id' => 'required',
                'truck_classification' => 'required',
                'first_registration' => 'required|date_format:Y-m',
                'inspection_expiration_date' => 'required|date_format:Y-m-d',
                'mileage' => 'required|integer|min:0',
                'maintenance_lease_fee' => 'nullable|numeric'
            ];
        }
        if(Route::getCurrentRoute()->getActionMethod() == 'store'){
            return  [
                'driving_classification' => 'nullable',
                'tonnage' => 'nullable',
                'truck_classification' => 'nullable',
                'truck_classification_number' => 'nullable',
                'truck_classification_2' => 'nullable',
                'manufactor' => 'nullable',
                'first_registration' => 'required|date_format:Y-m',
                'box_distinction' => 'nullable',
                'inspection_expiration_date' => 'date_format:Y-m-d',
                'scrap_date' => 'nullable',
//                'vehicle_identification_number' => 'required|string',
                'owner' => 'nullable',
                'etc_certification_number' => 'nullable',
                'etc_number' => 'nullable',
                'fuel_card_number_1' => 'nullable',
                'fuel_card_number_2' => 'nullable',
                'driving_recorder' => 'nullable',
                'box_shape' => 'nullable',
                'mount' => 'nullable',
                'refrigerator' => 'nullable',
                'eva_type' => 'nullable',
                'gate' => 'nullable',
                'humidifier' => 'nullable',
                'type' => 'nullable',
                'motor' => 'nullable',
                'displacement' => 'nullable',
                'length' => 'nullable',
                'width' => 'nullable',
                'height' => 'nullable',
                'maximum_loading_capacity'  => 'nullable',
                'vehicle_total_weight' => 'nullable',
                'in_box_length' => 'nullable',
                'in_box_width' => 'nullable',
                'in_box_height' => 'nullable',
                'voluntary_insurance' => 'nullable',
                'liability_insurance_period' => 'nullable',
                'insurance_company' => 'nullable',
                'agent' => 'nullable',
                'tire_size' => 'nullable',
                'battery_size' => 'nullable',
                'optional_detail' => 'nullable',
                'monthly_mileage' => 'nullable',
                'remark_old_car_1' => 'nullable',
                'remark_old_car_2' => 'nullable',
                'remark_old_car_3' => 'nullable',
                'remark_old_car_4' => 'nullable',
                'mileage' => 'required|integer|min:0',
                'vehicle_identification_number' => 'required|unique:vehicles,vehicle_identification_number',
                'maintenance_lease_fee' => 'nullable|numeric'
            ];
        }
     }

    public function messages()
    {
        return [
            'required' => ':attributeは必須です',
            'mileage.required' => '走行距離を入力してください。',
            'mileage.integer' => '走行距離は整数で入力してください。',
            'mileage.min' => '走行距離は整数で入力してください。',
            'vehicle_identification_number.unique' => '車体番号はすでに存在します。'
        ];
    }
}
