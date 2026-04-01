<?php
/**
 * Created by VeHo.
 * Year: 2023-03-22
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
class InsuranceRateRequest extends FormRequest
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
            default:
                return [];
        }
    }

    public function getCustomRule()
    {
        if (Route::getCurrentRoute()->getActionMethod() == 'update') {
            return [
                'change_rate' => 'required|numeric|between:0,1|regex:/^\d+(\.\d{1,5})?$/',
                'applicable_date' => 'required|date|after:today'
            ];
        }
    }

    public function messages()
    {
        return [
            'required' => ':attribute not null',
        ];
    }
}
