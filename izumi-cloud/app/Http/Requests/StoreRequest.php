<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Http\Requests;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use App\Rules\CheckNumericStoreRule;
class StoreRequest extends FormRequest
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
            case 'updateStore':
                return $this->getCustomRule();
            case 'store':
                return $this->getCustomRule();
            default:
                return [];
        }
    }

    public function getCustomRule()
    {
        
        if (Route::getCurrentRoute()->getActionMethod() == 'updateStore') {
            $store = Store::where('id', Route::getCurrentRoute()->parameter('id'))->first();
            if($store) {
                if($store->store_name !== $this->input(Store::STORE_NAME)) {
                    $storeName = 'required|max:20|unique:stores,store_name,null,id,deleted_at,NULL';
                } else {
                    $storeName = 'required|max:20';
                }
            }
            

            return [
                Store::STORE_NAME => $storeName,
                Store::BUSSINESS_CLASSIFICATION => 'string|min:1|max:30|nullable',
                Store::DELIVERY_DESTINATION_CODE => "nullable|numeric|max:999999999",
                Store::DESTINATION_NAME_KANA => "nullable|max:100",
                Store::DESTINATION_NAME => "nullable|max:50",
                Store::POST_CODE => "nullable|string",
                Store::TEL_NUMBER => "nullable|string",
                Store::ADDRESS_1 => "nullable|max:20",
                Store::ADDRESS_2 => "nullable|max:50",
                Store::PASS_CODE => "nullable|string",
                Store::DELIVERY_FREQUENCY => 'string|max:20|nullable',
                Store::QUANTITY_DELIVERY => 'string|nullable',
                // Store::SPECIFY_DELIVERY_TIME => 'string|max:60|nullable',
                Store::FIRST_SD_TIME => ['nullable', 'string',new CheckNumericStoreRule(Store::FIRST_SD_TIME)],
                Store::FIRST_SD_SUB_MIN_ONE => 'string|max:60|nullable',
                Store::FIRST_SD_SUB_MIN_SECOND => 'string|max:60|nullable',

                Store::SECOND_SD_TIME => ['nullable', 'string', new CheckNumericStoreRule(Store::SECOND_SD_TIME)],
                Store::SECOND_SUB_MIN_ONE => 'string|max:60|nullable',
                Store::SECOND_SUB_MIN_SECOND => 'string|max:60|nullable',

                Store::SCHEDULED_TIME_FIRST => 'string|max:60|nullable',
                Store::SCHEDULED_TIME_SECOND => 'string|max:60|nullable',
                Store::VEHICLE_HEIGHT_WIDTH => $this->checkValidate(Store::VEHICLE_HEIGHT_WIDTH, VEHICLE_HEIGHT_WIDTH),
                Store::HEIGHT => $this->getValidationRule('height'),
                Store::WIDTH => $this->getValidationRule('width'),
                Store::PARKING_PLACE => $this->checkValidate(Store::PARKING_PLACE, PARKING_PLACE_VALUE),
                Store::NOTE_1 => 'string|max:30|nullable',
                Store::DELIVERY_SLIP => $this->checkValidate(Store::DELIVERY_SLIP, DELIVERY_SLIP_VALUE),
                Store::SECURITY => $this->checkValidate(Store::SECURITY, SECURITY_VALUE),
                Store::DAISHA => 'nullable',
                Store::NOTE_2 => 'string|max:50|nullable',
                Store::PLACE => 'string|max:50|nullable',
                Store::NOTE_3 => 'string|max:50|nullable',
                Store::EMPTY_RECOVERY => 'string|max:50|nullable',
                Store::KEY => $this->checkValidate(Store::KEY, KEY_VALUE),
                Store::NOTE_4 => 'string|max:50|nullable',
                Store::CANCEL_METHOD => 'string|max:50|nullable',
                Store::GRACE_TIME => 'string|max:50|nullable',
                Store::COMPANY_NAME => 'string|max:50|nullable',
                Store::TEL_NUMBER => 'string|max:13|nullable',
                Store::TEL_NUMBER_2 => 'string|max:13|nullable',
                Store::INSIDE_RULE => 'nullable',
                Store::LICENSE => 'string|max:50|nullable',
                Store::RECEPTION_OR_ENTRY => 'string|max:50|nullable',
                Store::CERFT_REQUIRED => 'integer|max:50|nullable',
                Store::NOTE_5 => 'string|max:50|nullable',
                Store::ELEVATOR => $this->checkValidate(Store::ELEVATOR, ELEVATOR_VALUE),
                Store::NOTE_6 => 'string|max:50|nullable',
                Store::WAITING_PLACE => $this->checkValidate(Store::WAITING_PLACE, WAITING_PLACE_VALUE),
                Store::NOTE_7 => 'string|max:50|nullable',
                Store::NOTE_8 => 'string|max:50|nullable',
                // Store::DELIVERY_ROUTE_MAP_PATH => [
                //     'mimes:jpg,png,jpeg',
                //     'max:3072',
                //     'nullable'
                // ],
                Store::DELIVERY_ROUTE_MAP_OTHER_REMARK => 'string|max:100|nullable',

                // Store::PARKING_POSITION_1_FILE_PATH => [
                //     'mimes:jpg,png,jpeg',
                //     'max:3072',
                //     'nullable'
                // ],
                Store::PARKING_POSITION_1_OTHER_REMARK => 'string|max:100|nullable',

                // Store::PARKING_POSITION_2_FILE_PATH => [
                //     'mimes:jpg,png,jpeg',
                //     'max:3072',
                //     'nullable',
                //     'file'
                // ],
                Store::PARKING_POSITION_2_OTHER_REMARK => 'string|max:100|nullable',

                Store::DELIVERY_MANUAL => 'min:0|max:20',
                // Store::DELIVERY_MANUAL . ".*" => 'string|max:100'
            ];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'store') {
            return  [
                Store::BUSSINESS_CLASSIFICATION => 'string|min:1|max:30|nullable',
                Store::STORE_NAME => 'required|max:20|unique:stores,store_name,null,id,deleted_at,NULL',
                Store::DELIVERY_DESTINATION_CODE => "nullable|numeric|max:999999999",
                Store::DESTINATION_NAME_KANA => "nullable|max:100",
                Store::DESTINATION_NAME => "nullable|max:50",
                Store::POST_CODE => "nullable|string|max:10",
                Store::TEL_NUMBER => "nullable|string",
                Store::ADDRESS_1 => "nullable|max:20",
                Store::ADDRESS_2 => "nullable|max:50",
                Store::PASS_CODE => "nullable|string",
                Store::DELIVERY_FREQUENCY => 'string|max:20|nullable',
                Store::QUANTITY_DELIVERY => 'string|nullable',
                // Store::SPECIFY_DELIVERY_TIME => 'string|max:60|nullable',
                Store::FIRST_SD_TIME => ['nullable', 'string', new CheckNumericStoreRule(Store::FIRST_SD_TIME)],
                Store::FIRST_SD_SUB_MIN_ONE => 'string|max:60|nullable',
                Store::FIRST_SD_SUB_MIN_SECOND => 'string|max:60|nullable',

                Store::SECOND_SD_TIME => ['nullable', 'string', new CheckNumericStoreRule(Store::SECOND_SD_TIME)],
                Store::SECOND_SUB_MIN_ONE => 'string|max:60|nullable',
                Store::SECOND_SUB_MIN_SECOND => 'string|max:60|nullable',

                Store::SCHEDULED_TIME_FIRST => 'string|max:60|nullable',
                Store::SCHEDULED_TIME_SECOND => 'string|max:60|nullable',
                Store::VEHICLE_HEIGHT_WIDTH => $this->checkValidate(Store::VEHICLE_HEIGHT_WIDTH, VEHICLE_HEIGHT_WIDTH),
                Store::HEIGHT => $this->getValidationRule('height'),
                Store::WIDTH => $this->getValidationRule('width'),
                Store::PARKING_PLACE => $this->checkValidate(Store::PARKING_PLACE, PARKING_PLACE_VALUE),
                Store::NOTE_1 => 'string|max:30|nullable',
                Store::DELIVERY_SLIP => $this->checkValidate(Store::DELIVERY_SLIP, DELIVERY_SLIP_VALUE),
                Store::SECURITY => $this->checkValidate(Store::SECURITY, SECURITY_VALUE),
                Store::DAISHA => 'nullable',
                Store::NOTE_2 => 'string|max:50|nullable',
                Store::PLACE => 'string|max:50|nullable',
                Store::NOTE_3 => 'string|max:50|nullable',
                Store::EMPTY_RECOVERY => 'string|max:50|nullable',
                Store::KEY => $this->checkValidate(Store::KEY, KEY_VALUE),
                Store::NOTE_4 => 'string|max:50|nullable',
                Store::CANCEL_METHOD => 'string|max:50|nullable',
                Store::GRACE_TIME => 'string|max:50|nullable',
                Store::COMPANY_NAME => 'string|max:50|nullable',
                Store::TEL_NUMBER => 'string|max:13|nullable',
                Store::TEL_NUMBER_2 => 'string|max:13|nullable',
                Store::INSIDE_RULE => 'nullable',
                Store::LICENSE => 'string|max:50|nullable',
                Store::RECEPTION_OR_ENTRY => 'string|max:50|nullable',
                Store::CERFT_REQUIRED => 'integer|max:50|nullable',
                Store::NOTE_5 => 'string|max:50|nullable',
                Store::ELEVATOR => $this->checkValidate(Store::ELEVATOR, ELEVATOR_VALUE),
                Store::NOTE_6 => 'string|max:50|nullable',
                Store::WAITING_PLACE => $this->checkValidate(Store::WAITING_PLACE, WAITING_PLACE_VALUE),
                Store::NOTE_7 => 'string|max:50|nullable',
                Store::NOTE_8 => 'string|max:50|nullable',
                // Store::DELIVERY_ROUTE_MAP_PATH => [
                //     'mimes:jpg,png',
                //     'max:3072',
                //     'nullable'
                // ],
                Store::DELIVERY_ROUTE_MAP_OTHER_REMARK => 'string|max:100|nullable',

                // Store::PARKING_POSITION_1_FILE_PATH => [
                //     'mimes:jpg,png',
                //     'max:3072',
                //     'nullable'
                // ],
                Store::PARKING_POSITION_1_OTHER_REMARK => 'string|max:100|nullable',

                // Store::PARKING_POSITION_2_FILE_PATH => [
                //     'mimes:jpg,png',
                //     'max:3072',
                //     'nullable'
                // ],
                Store::PARKING_POSITION_2_OTHER_REMARK => 'string|max:100|nullable',

                Store::DELIVERY_MANUAL => 'min:0|max:20',
                // Store::DELIVERY_MANUAL . ".*" => 'string|max:100'
            ];
        }
    }

    protected function getValidationRule($type)
    {
        if($type === 'with') {
            $value = $this->input('with');
            if ($value === 'null' || $value === null) {
                return 'nullable';
            } elseif (is_numeric($value)) {
                return 'integer';
            }
        } else {
            $value = $this->input('height');
            if ($value === 'null' || $value === null) {
                return 'nullable';
            } elseif (is_numeric($value)) {
                return 'integer';
            }
        }
    }

    public function checkValidate($type, $array)
    {
        $value = $this->input($type);
        if ($value === '-1' || $value === null) {
            return 'nullable';
        } else{
            return 'in:' . implode(',', $array);
        }
    }

    public function messages()
    {
        return [
            'required' => ':attributeは必須です',
            'store_name.unique' => '指定の店舗名は既に作成されています。'
        ];
    }
    public function attributes()
    {
        return [
            Store::STORE_NAME => '店舗名',
            Store::DELIVERY_DESTINATION_CODE => "納品先コード",
            Store::DESTINATION_NAME_KANA => "納品先名(カナ)",
            Store::DESTINATION_NAME => "納品先名",
            Store::POST_CODE => "郵便番号",
            Store::TEL_NUMBER => "TEL",
            Store::ADDRESS_1 => "大住所",
            Store::ADDRESS_2 => "小住所",
            Store::PASS_CODE => "表示パスコード",
            Store::BUSSINESS_CLASSIFICATION => '事業分類',
            Store::FIRST_SD_TIME => '1便',
            Store::SECOND_SD_TIME => '2便',
            Store::WIDTH => '幅',
            Store::HEIGHT => '高さ'
        ];
    }
}
