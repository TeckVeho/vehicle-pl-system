<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-04-19
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class MahoujinRequest extends FormRequest
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
          switch (Route::getCurrentRoute()->getActionMethod()){
                case 'update':
                    return $this->getCustomRule();
                case 'store':
                    return $this->getCustomRule();
                case 'index':
                    return $this->getMahoujin();
                default:
                    return [];
          }
    }

    public function getCustomRule(){
        if(Route::getCurrentRoute()->getActionMethod() == 'update'){
            return [

            ];
        }
        if(Route::getCurrentRoute()->getActionMethod() == 'store'){
            return  [

            ];
        }
    }

    public function getMahoujin() {
        return  [
            'year_month' => 'required|date_format:Y-m',
            'department_name'=> 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute not null'
        ];
    }
}
