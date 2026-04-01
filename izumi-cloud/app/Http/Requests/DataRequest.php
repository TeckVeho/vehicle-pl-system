<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-21
 */

namespace App\Http\Requests;

use App\Models\Data;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class DataRequest extends FormRequest
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
                default:
                    return [];
          }
    }

     public function getCustomRule(){
        if(Route::getCurrentRoute()->getActionMethod() == 'update'){
            return [
                Data::NAME => "required|string",
                Data::FROM => "required|integer",
                Data::TO => "required|integer",
                Data::REMARK => "string"
            ];
        }
        if(Route::getCurrentRoute()->getActionMethod() == 'store'){
            return  [
                Data::NAME => "required|string",
                Data::FROM => "required|integer",
                Data::TO => "required|integer",
                Data::REMARK => "string"
            ];
        }
     }

    public function messages()
    {
        return [
            'required' => ':attribute not null'
        ];
    }
}
