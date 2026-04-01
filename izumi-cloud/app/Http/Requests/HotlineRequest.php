<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-06-10
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class HotlineRequest extends FormRequest
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
                'username' => 'nullable|string',
                'phone' => 'nullable|string',
                'email' => 'nullable|string',
                'content' => 'nullable|string',
                'check_anonymous_flag' => 'nullable|boolean',
                'contact_flag' => 'nullable|boolean',
            ];
        }
        if(Route::getCurrentRoute()->getActionMethod() == 'store'){
            return  [
                'username' => 'nullable|string',
                'phone' => 'nullable|string',
                'email' => 'nullable|string',
                'content' => 'nullable|string',
                'check_anonymous_flag' => 'nullable|boolean',
                'contact_flag' => 'nullable|boolean',
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
