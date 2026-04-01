<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Http\Requests;

use App\Models\UploadData;
use App\Rules\ExtensionRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class RouteRequest extends FormRequest
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
        return $this->getCustomRule(Route::getCurrentRoute()->getActionMethod());
    }

    public function getCustomRule($action = null)
    {
        if ($action == 'updateMany') {
            return [
                "*.name" => 'required',
                '*.customer_id' => 'required|exists:customers,id',
                '*.route_fare_type' => 'required|numeric|min:0',
                '*.fare' => 'required|numeric|min:0',
                '*.highway_fee' => 'required|numeric|min:0',
                '*.highway_fee_holiday' => 'required|numeric|min:0',
                '*.list_week.*' => 'nullable|numeric|between:1,7',
                '*.list_month.*' => 'nullable|numeric|between:1,31',
                '*.list_store' => 'required|array|min:1',
                '*.list_store.*' => 'required|min:1|exists:stores,id',
            ];
        }
        if ($action == 'store') {
            return [
                'department_id' => 'required|exists:departments,id',
                'customer_id' => 'required|exists:customers,id',
                "name" => 'required',
                'route_fare_type' => 'required|numeric|min:0',
                'fare' => 'required|numeric|min:0',
                'highway_fee' => 'required|numeric|min:0',
                'highway_fee_holiday' => 'required|numeric|min:0',
                'list_week.*' => 'nullable|numeric|between:1,7',
                'list_month.*' => 'nullable|numeric|between:1,31',
                'list_store' => 'required|array|min:1',
                'list_store.*' => 'required|exists:stores,id',
            ];
        }
        if ($action == 'import') {
            return [
                "file" => [
                    'required',
                    'mimes:csv,txt',
                    'mimetypes:text/csv,text/plain',
                    'max:61440',
                ],
            ];
        }
        return [];
    }

    public function messages()
    {
        return [
            'required' => ':attributeは必須です',
            'exists' => ':attributeは存在していません',
            '*.name.required' => 'ルート名を入力してください',
            'file.mimes'=> 'ファイル形式がCSVではありません。',
            'file.mimetypes'=> 'ファイル形式がCSVではありません。',
            'file.max'=> 'ファイルサイズが60MBを超過しています。',
        ];
    }

    public function attributes()
    {
        return [
            'department_id' => '拠点',
            'customer_id' => '荷主',
            'name' => '荷主名',
            'route_fare_type' => '運賃種別',
            'fare' => '運賃',
            'highway_fee' => '高速代',
            'highway_fee_holiday' => '高速代(休日)',
            'list_week' => '運休曜日',
            'list_month' => '運行表',
            'list_store' => '配送店舗',
            'list_store.*' => '配送店舗',
            '*.department_id' => '拠点',
            '*.customer_id' => '荷主',
            '*.name' => '荷主名',
            '*.route_fare_type' => '運賃種別',
            '*.fare' => '運賃',
            '*.highway_fee' => '高速代',
            '*.highway_fee_holiday' => '高速代(休日)',
            '*.list_week' => '運休曜日',
            '*.list_month' => '運行表',
            '*.list_store' => '配送店舗',
            '*.list_store.*' => '配送店舗',
        ];
    }
}
