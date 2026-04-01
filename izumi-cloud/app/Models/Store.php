<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory;
    use SoftDeletes;

    const STORE_NAME = 'store_name';
    const BUSSINESS_CLASSIFICATION = 'bussiness_classification';
    const DELIVERY_DESTINATION_CODE = 'delivery_destination_code';
    const DESTINATION_NAME_KANA = 'destination_name_kana';
    const DESTINATION_NAME = 'destination_name';
    const POST_CODE = 'post_code';
    const ADDRESS_1 = 'address_1';
    const ADDRESS_2 = 'address_2';
    const DELIVERY_FREQUENCY = 'delivery_frequency';
    const QUANTITY_DELIVERY = 'quantity_delivery';
    // const SPECIFY_DELIVERY_TIME = 'specify_delivery_time';
    const FIRST_SD_TIME = 'first_sd_time';
    const FIRST_SD_SUB_MIN_ONE = 'first_sd_sub_min_one';
    const FIRST_SD_SUB_MIN_SECOND = 'first_sd_sub_min_second';

    const SECOND_SD_TIME = 'second_sd_time';
    const SECOND_SUB_MIN_ONE = 'second_sub_min_one';
    const SECOND_SUB_MIN_SECOND = 'second_sub_min_second';

    const SCHEDULED_TIME_FIRST = 'scheduled_time_first';
    const SCHEDULED_TIME_SECOND = 'scheduled_time_second';
    const VEHICLE_HEIGHT_WIDTH = 'vehicle_height_width';
    const HEIGHT = 'height';
    const WIDTH = 'width';
    const PARKING_PLACE = 'parking_place';
    const NOTE_1 = 'note_1';
    const DELIVERY_SLIP = 'delivery_slip';
    const NOTE_2 = 'note_2';
    const DAISHA = 'daisha';
    const NOTE_3 = 'note_3';
    const PLACE = 'place';
    const NOTE_4 = 'note_4';
    const EMPTY_RECOVERY = 'empty_recovery';
    const KEY = 'key';
    const NOTE_5 = 'note_5';
    const SECURITY = 'security';
    const CANCEL_METHOD = 'cancel_method';
    const GRACE_TIME = 'grace_time';
    const COMPANY_NAME = 'company_name';
    const TEL_NUMBER = 'tel_number';
    const TEL_NUMBER_2 = 'tel_number_2';
    const INSIDE_RULE = 'inside_rule';
    const LICENSE = 'license';
    const RECEPTION_OR_ENTRY = 'reception_or_entry';
    const CERFT_REQUIRED = 'cerft_required';
    const NOTE_6 = 'note_6';
    const ELEVATOR = 'elevator';
    const NOTE_7 = 'note_7';
    const WAITING_PLACE = 'waiting_place';
    const NOTE_8 = 'note_8';
    const DELIVERY_ROUTE_MAP_PATH = 'delivery_route_map_path';
    const DELIVERY_ROUTE_MAP_OTHER_REMARK = 'delivery_route_map_other_remark';
    const PARKING_POSITION_1_FILE_PATH = 'parking_position_1_file_path';
    const PARKING_POSITION_1_OTHER_REMARK = 'parking_position_1_other_remark';
    const PARKING_POSITION_2_FILE_PATH = 'parking_position_2_file_path';
    const PARKING_POSITION_2_OTHER_REMARK = 'parking_position_2_other_remark';
    const PASS_CODE = 'pass_code';
    const DELIVERY_MANUAL = 'delivery_manual';
    const LAST_UPDATED_AT = 'last_updated_at';
    protected $table = 'stores';

    protected $fillable = [
        Store::STORE_NAME,
        Store::DELIVERY_DESTINATION_CODE,
        Store::BUSSINESS_CLASSIFICATION,
        Store::DESTINATION_NAME_KANA,
        Store::DESTINATION_NAME,
        Store::POST_CODE,
        Store::ADDRESS_1,
        Store::ADDRESS_2,
        Store::DELIVERY_FREQUENCY,
        Store::QUANTITY_DELIVERY,

        Store::FIRST_SD_TIME,
        Store::FIRST_SD_SUB_MIN_ONE,
        Store::FIRST_SD_SUB_MIN_SECOND,

        Store::SECOND_SD_TIME,
        Store::SECOND_SUB_MIN_ONE,
        Store::SECOND_SUB_MIN_SECOND,

        Store::SCHEDULED_TIME_FIRST,
        Store::SCHEDULED_TIME_SECOND,
        Store::VEHICLE_HEIGHT_WIDTH,
        Store::HEIGHT,
        Store::WIDTH,
        Store::PARKING_PLACE,
        Store::NOTE_1,
        Store::DELIVERY_SLIP,
        Store::NOTE_2,
        Store::DAISHA,
        Store::NOTE_3,
        Store::PLACE,
        Store::NOTE_4,
        Store::EMPTY_RECOVERY,
        Store::KEY,
        Store::NOTE_5,
        Store::SECURITY,
        Store::CANCEL_METHOD,
        Store::GRACE_TIME,
        Store::COMPANY_NAME,
        Store::TEL_NUMBER,
        Store::TEL_NUMBER_2,
        Store::INSIDE_RULE,
        Store::LICENSE,
        Store::RECEPTION_OR_ENTRY,
        Store::CERFT_REQUIRED,
        Store::NOTE_6,
        Store::ELEVATOR,
        Store::NOTE_7,
        Store::WAITING_PLACE,
        Store::NOTE_8,
        Store::DELIVERY_ROUTE_MAP_PATH,
        Store::DELIVERY_ROUTE_MAP_OTHER_REMARK,
        Store::PARKING_POSITION_1_FILE_PATH,
        Store::PARKING_POSITION_1_OTHER_REMARK,
        Store::PARKING_POSITION_2_FILE_PATH,
        Store::PARKING_POSITION_2_OTHER_REMARK,
        Store::PASS_CODE
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array',
        Store::VEHICLE_HEIGHT_WIDTH => 'integer',
        Store::PARKING_PLACE => 'integer',
        Store::DELIVERY_SLIP => 'integer',
        Store::DAISHA => 'integer',
        Store::KEY => 'integer',
        Store::SECURITY => 'integer',
        Store::INSIDE_RULE => 'integer',
        Store::ELEVATOR => 'integer',
        Store::WAITING_PLACE => 'integer',
    ];

    protected $hidden = [
        'pivot'
    ];

    public function delivery_manual()
    {
        return $this->hasMany('App\Models\DeliveryManual', 'store_id', 'id');
    }
}
