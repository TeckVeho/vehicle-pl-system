<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2024-05-08
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class MoviesRequest extends FormRequest
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
                case 'updateLoopEnabled':
                    return [
                        'is_loop_enabled' => 'required|boolean'
                    ];
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

    public function messages()
    {
        return [
            'required' => ':attribute not null'
        ];
    }
}
