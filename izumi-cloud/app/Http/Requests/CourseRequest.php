<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class CourseRequest extends FormRequest
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
        if (Route::getCurrentRoute()->getActionMethod() == 'update') {
            return [
                'start_date'  => 'required',
                'end_date' => 'required',
                'course_type' => 'required|in:' . implode(',', COURSE_TYPE_VALUE),
                'bin_type' => 'required|in:' . implode(',', BIN_TYPE_VALUE),
                'delivery_type' => 'required|in:' . implode(',', DELIVERY_TYPE_VALUE),
                'start_time' => 'required',
                'gate' => 'required|in:' . implode(',', GATE_VALUE),
                'wing' => 'required|in:' . implode(',', WING_VALUE),
                'tonnage' => 'required|in:' . implode(',', TONNAGE),
                'quantity' => 'required|numeric|min:0',
                'allowance' => 'numeric|min:0',
                'department_id' => 'required|exists:departments,id',
                'routes' => 'required|array|min:1',
                'routes.*' => 'required|exists:routes,id',
                'address' => 'nullable|string|max:1000'
            ];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'store') {
            return  [
                'course_code' => 'required',
                'start_date'  => 'required',
                'end_date' => 'required',
                'course_type' => 'required|in:' . implode(',', COURSE_TYPE_VALUE),
                'bin_type' => 'required|in:' . implode(',', BIN_TYPE_VALUE),
                'delivery_type' => 'required|in:' . implode(',', DELIVERY_TYPE_VALUE),
                'start_time' => 'required',
                'gate' => 'required|in:' . implode(',', GATE_VALUE),
                'wing' => 'required|in:' . implode(',', WING_VALUE),
                'tonnage' => 'required|in:' . implode(',', TONNAGE),
                'quantity' => 'required|numeric|min:0',
                'allowance' => 'numeric|min:0',
                'department_id' => 'required|exists:departments,id',
                'routes' => 'required|array|min:1',
                'routes.*' => 'required|exists:routes,id',
                'address' => 'nullable|string|max:1000'
            ];
        }
    }

    public function messages()
    {
        return [
            'required' => ':attributeは必須です',
            'exists' => ':attributeは存在していません',
        ];
    }

    public function attributes()
    {
        return [
            'department_id' => '拠点',
            'course_code' => 'コースID',
            'start_date' => '配送開始日',
            'end_date' => '配送終了日',
            'course_type' => 'コース種別',
            'bin_type' => '便種別',
            'delivery_type' => '配送種別',
            'quantity' => '件数',
            'start_time' => 'コース開始時間',
            'allowance' => 'コース手当',
            'gate' => 'ゲート',
            'wing' => 'ウイング',
            'tonnage' => 'トン数',
            'routes' => 'ルート名',
        ];
    }
}
