<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateRouteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'nullable|string|max:500',
            'start_location' => 'nullable|string|max:500',
            'pickup_location' => 'required|string|max:500',
            'delivery_location' => 'nullable|string|max:500',
            'delivery_locations' => 'nullable|array',
            'delivery_locations.*' => 'nullable|string|max:500',
            'return_location' => 'required|string|max:500',
            'start_time' => 'required|date_format:H:i',
            'vehicle_type' => 'nullable|string|max:50',
            'loading_time' => 'nullable|integer|min:0|max:480',
            'unloading_time' => 'nullable|integer|min:0|max:480',
            'break_time' => 'nullable|integer|min:0|max:480',
        ];
    }

    public function messages()
    {
        return [
            'start_location.string' => '出発地は文字列で入力してください',
            'start_location.max' => '出発地は500文字以内で入力してください',
            'pickup_location.required' => '積地を入力してください',
            'delivery_location.string' => '届け地は文字列で入力してください',
            'delivery_locations.array' => '届け地は配列形式で入力してください',
            'delivery_locations.*.string' => '届け地の各項目は文字列で入力してください',
            'delivery_locations.*.max' => '届け地の各項目は500文字以内で入力してください',
            'return_location.required' => '帰社地を入力してください',
            'start_time.required' => '運行開始時間を入力してください',
            'start_time.date_format' => '時間形式が正しくありません（HH:MM）',
            'loading_time.integer' => '積み込み時間は整数で入力してください',
            'loading_time.max' => '積み込み時間は480分以下で入力してください',
            'unloading_time.integer' => '荷下ろし時間は整数で入力してください',
            'unloading_time.max' => '荷下ろし時間は480分以下で入力してください',
        ];
    }
}

