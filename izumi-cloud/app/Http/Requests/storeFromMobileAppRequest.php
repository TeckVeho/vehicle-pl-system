<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use App\Rules\StorePassCodeRule;
use Illuminate\Validation\Rule;
use App\Models\Store;
use App\Rules\StoreImageRule;

class storeFromMobileAppRequest extends FormRequest
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
            case 'storeEditFromMobileApp':
                return $this->getCustomRule();
            case 'storeDetailFromMobileApp':
                return $this->getCustomRule();
            default:
                return [];
        }
    }

    public function getCustomRule()
    {
        $store = $this->route('id');
        $storeRule = new StorePassCodeRule($store);
        if (Route::getCurrentRoute()->getActionMethod() == 'storeEditFromMobileApp') {
            return [
                Store::PASS_CODE => [$storeRule->is_required(), "min:4", "max:4", $storeRule, "nullable"],
                Store::DELIVERY_FREQUENCY => 'string|max:20|nullable',
                Store::QUANTITY_DELIVERY => 'string|nullable',
                // Store::SPECIFY_DELIVERY_TIME => 'string|max:60|nullable',
                Store::FIRST_SD_TIME => 'string|max:60|nullable',
                Store::FIRST_SD_SUB_MIN_ONE => 'string|max:60|nullable',
                Store::FIRST_SD_SUB_MIN_SECOND => 'string|max:60|nullable',

                Store::SECOND_SD_TIME => 'string|max:60|nullable',
                Store::SECOND_SUB_MIN_ONE => 'string|max:60|nullable',
                Store::SECOND_SUB_MIN_SECOND => 'string|max:60|nullable',

                Store::SCHEDULED_TIME_FIRST => 'string|max:60|nullable',
                Store::SCHEDULED_TIME_SECOND => 'string|max:60|nullable',
                Store::VEHICLE_HEIGHT_WIDTH => 'in:' . implode(',', VEHICLE_HEIGHT_WIDTH),
                Store::HEIGHT => 'string|nullable',
                Store::WIDTH => 'string|nullable',
                Store::PARKING_PLACE => 'in:' . implode(',', PARKING_PLACE_VALUE),
                Store::NOTE_1 => 'string|max:30|nullable',
                Store::DELIVERY_SLIP => 'in:' . implode(',', DELIVERY_SLIP_VALUE),
                Store::SECURITY => 'in:' . implode(',', SECURITY_VALUE),
                Store::DAISHA => 'in:' . implode(',', DAISHA),
                Store::NOTE_2 => 'string|max:50|nullable',
                Store::PLACE => 'string|max:50|nullable',
                Store::NOTE_3 => 'string|max:50|nullable',
                Store::EMPTY_RECOVERY => 'string|max:50|nullable',
                Store::KEY => 'in:' . implode(',', KEY_VALUE),
                Store::NOTE_4 => 'string|max:50|nullable',
                Store::CANCEL_METHOD => 'string|max:50|nullable',
                Store::GRACE_TIME => 'string|max:50|nullable',
                Store::COMPANY_NAME => 'string|max:50|nullable',
                Store::TEL_NUMBER => 'string|max:13|nullable',
                Store::TEL_NUMBER_2 => 'string|max:13|nullable',
                Store::INSIDE_RULE => 'in:' . implode(',', INSIDE_RULE_VALUE),
                Store::LICENSE => 'string|max:50|nullable',
                Store::RECEPTION_OR_ENTRY => 'string|max:50|nullable',
                Store::CERFT_REQUIRED => 'string|max:50|nullable',
                Store::NOTE_5 => 'string|max:50|nullable',
                Store::ELEVATOR => 'in:' . implode(',', ELEVATOR_VALUE),
                Store::NOTE_6 => 'string|max:50|nullable',
                Store::WAITING_PLACE => 'in:' . implode(',', WAITING_PLACE_VALUE),
                Store::NOTE_7 => 'string|max:50|nullable',
                Store::NOTE_8 => 'string|max:50|nullable',
                Store::DELIVERY_ROUTE_MAP_PATH => [
                    'mimes:jpg,png,PNG',
                    'max:3072',
                    'nullable'
                ],
                Store::DELIVERY_ROUTE_MAP_OTHER_REMARK => 'string|max:100|nullable',

                Store::PARKING_POSITION_1_FILE_PATH => [
                    'mimes:jpg,png,PNG',
                    'max:3072',
                    'nullable'
                ],
                Store::PARKING_POSITION_1_OTHER_REMARK => 'string|max:100|nullable',

                Store::PARKING_POSITION_2_FILE_PATH => [
                    'mimes:jpg,png,PNG',
                    'max:3072',
                    'nullable'
                ],
                Store::PARKING_POSITION_2_OTHER_REMARK => 'string|max:100|nullable',

                Store::DELIVERY_MANUAL => 'min:0|max:20',
                Store::DELIVERY_MANUAL . ".*" => 'string|max:100'
            ];
        }

        if (Route::getCurrentRoute()->getActionMethod() == 'storeDetailFromMobileApp') {
            return [
                Store::PASS_CODE => [$storeRule->is_required(), "min:4", "max:4", $storeRule],
            ];
        }
    }

    public function messages()
    {
        return [
            'required' => ':attributeは必須です'
        ];
    }
}
