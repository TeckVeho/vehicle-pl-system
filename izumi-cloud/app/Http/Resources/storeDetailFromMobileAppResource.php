<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Store;

class storeDetailFromMobileAppResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            Store::STORE_NAME => $this->store_name,
            Store::BUSSINESS_CLASSIFICATION => $this->bussiness_classification,
            Store::DELIVERY_DESTINATION_CODE => $this->delivery_destination_code,
            Store::DESTINATION_NAME_KANA => $this->destination_name_kana,
            Store::DESTINATION_NAME => $this->destination_name,
            Store::POST_CODE => $this->post_code,
            Store::ADDRESS_1 => $this->address_1,
            Store::ADDRESS_2 => $this->address_2,
            Store::DELIVERY_FREQUENCY => $this->delivery_frequency,
            Store::QUANTITY_DELIVERY => $this->quantity_delivery,

            Store::FIRST_SD_TIME => $this->first_sd_time,
            Store::FIRST_SD_SUB_MIN_ONE => $this->first_sd_sub_min_one,
            Store::FIRST_SD_SUB_MIN_SECOND => $this->first_sd_sub_min_second,

            Store::SECOND_SD_TIME => $this->second_sd_time,
            Store::SECOND_SUB_MIN_ONE => $this->second_sub_min_one,
            Store::SECOND_SUB_MIN_SECOND => $this->second_sub_min_second,

            Store::SCHEDULED_TIME_FIRST => $this->scheduled_time_first,
            Store::SCHEDULED_TIME_SECOND => $this->scheduled_time_second,
            Store::VEHICLE_HEIGHT_WIDTH => $this->vehicle_height_width,
            Store::HEIGHT . "_" . Store::WIDTH => $this->height . " / " . $this->width,
            Store::PARKING_PLACE => $this->parking_place,
            Store::NOTE_1 => $this->note_1,
            Store::DELIVERY_SLIP => $this->delivery_slip,
            Store::NOTE_2 => $this->note_2,
            Store::DAISHA => $this->daisha,
            Store::NOTE_3 => $this->note_3,
            Store::PLACE => $this->place,
            Store::NOTE_4 => $this->note_4,
            Store::EMPTY_RECOVERY => $this->empty_recovery,
            Store::KEY => $this->key,
            Store::NOTE_5 => $this->note_5,
            Store::SECURITY => $this->security,
            Store::CANCEL_METHOD => $this->cancel_method,
            Store::GRACE_TIME => $this->grace_time,
            Store::COMPANY_NAME => $this->company_name,
            Store::TEL_NUMBER => $this->tel_number,
            Store::TEL_NUMBER_2 => $this->tel_number_2,
            Store::INSIDE_RULE => $this->inside_rule,
            Store::LICENSE  => $this->license,
            Store::RECEPTION_OR_ENTRY => $this->reception_or_entry,
            Store::CERFT_REQUIRED => $this->cerft_required,
            Store::NOTE_6 => $this->note_6,
            Store::ELEVATOR => $this->elevator,
            Store::NOTE_7 => $this->note_7,
            Store::WAITING_PLACE => $this->waiting_place,
            Store::NOTE_8 => $this->note_8,
            Store::DELIVERY_ROUTE_MAP_PATH => $this->delivery_route_map_path ? 'storage/' . $this->delivery_route_map_path : $this->delivery_route_map_path,//"/api/mobile/store/image/" . STORE_IMAGE_TYPE['delivery_route_map'] . "/$this->id",
            Store::DELIVERY_ROUTE_MAP_OTHER_REMARK => $this->delivery_route_map_other_remark,
            Store::PARKING_POSITION_1_FILE_PATH => $this->parking_position_1_file_path ? 'storage/' . $this->parking_position_1_file_path : $this->parking_position_1_file_path, //"/api/mobile/store/image/" . STORE_IMAGE_TYPE['parking_position_1'] . "/$this->id",
            Store::PARKING_POSITION_1_OTHER_REMARK => $this->parking_position_1_other_remark,
            Store::PARKING_POSITION_2_FILE_PATH => $this->parking_position_2_file_path ? 'storage/' . $this->parking_position_2_file_path : $this->parking_position_2_file_path, //"/api/mobile/store/image/" . STORE_IMAGE_TYPE['parking_position_2'] . "/$this->id",
            Store::PARKING_POSITION_2_OTHER_REMARK => $this->parking_position_2_other_remark,
            Store::DELIVERY_MANUAL => $this->delivery_manual,
            Store::LAST_UPDATED_AT => $this->updated_at
        ];
    }
}
