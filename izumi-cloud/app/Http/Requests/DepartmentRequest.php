<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-07-19
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class DepartmentRequest extends FormRequest
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
            case 'changeOrder':
                return $this->getCustomRule();
            default:
                return [];
        }
    }

    public function getCustomRule()
    {
        if (Route::getCurrentRoute()->getActionMethod() == 'update') {
            return [
                'interview_address' => 'nullable|string|max:1000',
                'path_for_interview_address' => 'nullable|string|max:1000',
                'post_code' => 'nullable|',
                'tel' => 'nullable|',
                'office_name' => 'nullable|string',
                'office_location' => 'nullable|string',
                'office_area' => 'nullable|string',
                'rest_room_area' => 'nullable|string',
                'garage_location_1' => 'nullable|string',
                'garage_area_1' => 'nullable|string',
                'garage_location_2' => 'nullable|string',
                'garage_area_2' => 'nullable|string',
                'operations_manager_appointment' => 'nullable|array',
                'operations_manager_assistant' => 'nullable|array',
                'maintenance_manager_appointment' => 'nullable|array',
                'maintenance_manager_assistant' => 'nullable|array',
                'maintenance_manager_phone_number' => 'nullable|string',
                'maintenance_manager_fax_number' => 'nullable|string',
                'truck_association_membership_number' => 'nullable|string',
                'g_mark_number' => 'nullable|string',
                'g_mark_expiration_date' => 'nullable|string',
                'it_roll_call' => 'nullable|integer',
                'g_mark_action_radio' => 'nullable|array',
                'chief_operations_manager' => 'nullable|string',
            ];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'store') {
            return [

            ];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'changeOrder') {
            return [
                'list_department' => 'required|array',
                'list_department.*' => 'required|exists:departments,id',
            ];
        }
    }

    public function messages()
    {
        return [
            'required' => ':attribute not null'
        ];
    }

    public function attributes()
    {
        return [
            "post_code" => "郵便番号",
            'tel' => '電話番号',
        ];
    }
}
