<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class UserRequest extends FormRequest
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
            case 'updateone':
                return $this->getCustomRuleUpdateOne();
            case 'changePassword':
                return $this->getCustomRuleChangePassword();
            case 'store':
                return $this->getCustomRuleStore();
            case 'update':
                return $this->getCustomRuleUpdate();
            default:
                return [];
        }
    }

    public function getCustomRuleUpdateOne()
    {
        return [
            'email' => 'required_without:proxy_email|email|unique:users',
            'proxy_email' => 'required_without:email|email|unique:users',
            'password' => "required|min:8"
        ];
    }

    public function getCustomRuleChangePassword()
    {
        return [
            'password' => 'min:8|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:8'
        ];
    }

    public function getCustomRuleStore()
    {
        $id = $this->route('user');
        return [
            "role" => "required|exists:roles,id",
            "name" => "required|max:255",
            "id" => "required|unique:users,id,null,id,deleted_at,NULL",
            "password" => "required|min:8",
            "assign_vehicle_personnel" => "nullable"
        ];
    }

    public function getCustomRuleUpdate()
    {
        return [
            "role" => "required|exists:roles,id",
            "name" => "required|max:255",
            "password" => "nullable|min:8",
            "current_password" => "nullable|min:8",
            'email' => 'required|email|string|max:319',
            "assign_vehicle_personnel" => "nullable"
        ];
    }

    public function messages()
    {
        return [
            'required' => trans('validation.required'),
            'date_format' => trans('validation.date_format'),
            'regex' => trans('validation.regex'),
            'numeric' => trans('validation.numeric'),
            'digits_between' => trans('validation.digits_between'),
            'exists' => trans('validation.exists')
        ];
    }

    public function attributes()
    {
        return [
            'email' => 'メールアドレス',
        ];
    }
}
