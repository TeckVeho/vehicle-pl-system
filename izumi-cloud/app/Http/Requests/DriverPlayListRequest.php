<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-11-15
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class DriverPlayListRequest extends FormRequest
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
            case 'addOrUpdateDriverRecorder':
                return $this->getCustomRule();
            default:
                return [];
        }
    }

    public function getCustomRule()
    {
        if (Route::getCurrentRoute()->getActionMethod() == 'update') {
            return ['name' => 'required'];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'store') {
            return ['name' => 'required'];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'addOrUpdateDriverRecorder') {
            return [
                'driver_play_list_id' => 'required|exists:driver_play_lists,id',
                'driver_recorder_id' => 'required|exists:driver_recorders,id'
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
