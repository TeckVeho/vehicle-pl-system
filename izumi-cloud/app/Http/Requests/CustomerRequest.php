<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class CustomerRequest extends FormRequest
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
        $id = $this->route('customer');
        if (Route::getCurrentRoute()->getActionMethod() == 'update') {
            return [
                'customer_name' => "required|max:20|unique:customers,customer_name,$id,id,deleted_at,NULL",
            ];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'store') {
            return [
                'customer_name' => 'required|max:20|unique:customers,customer_name,null,id,deleted_at,NULL',
            ];
        }
    }

    public function messages()
    {
        return [
            'required' => ':attributeは必須です',
        ];
    }

    public function attributes()
    {
        return [
            'customer_name' => '荷主名',
        ];
    }
}
