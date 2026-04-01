<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class DataConnectionPLRequest extends FormRequest
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
            case 'execQueueByPl':
                return $this->getCustomRuleByPl();
            default:
                return [];
        }
    }

    public function getCustomRuleByPl()
    {
        return [
            'date' => 'required|date_format:Y-m-d',
            'department_name' => 'required',
            'url_api_callback' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute not null'
        ];
    }
}
