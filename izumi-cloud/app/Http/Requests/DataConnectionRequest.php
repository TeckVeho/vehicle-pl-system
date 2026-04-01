<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class DataConnectionRequest extends FormRequest
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
            case 'getDataTimeSheetIndex':
                return $this->getDataTimeSheet();
            case 'execQueueByPl':
                return $this->getCustomRuleByPl();
            default:
                return [];
        }
    }

    public function getCustomRule()
    {
        if (Route::getCurrentRoute()->getActionMethod() == 'update') {
            return [

            ];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'store') {
            return [

            ];
        }
    }

    public function getDataTimeSheet()
    {
        return [
            'year_month' => 'required|date_format:Y-m',
        ];
    }

    public function getCustomRuleByPl()
    {
        return [
            'date' => 'required|date_format:Y-m-d',
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
