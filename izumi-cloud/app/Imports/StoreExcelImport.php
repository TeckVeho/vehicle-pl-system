<?php

namespace App\Imports;

use App\Models\Store;
use App\Models\Vehicle;
use App\Models\VehicleCost;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class StoreExcelImport implements WithMappedCells, ToModel, WithHeadingRow
{
    use RemembersRowNumber;

    protected $objectSore;
    protected $objectDeliveryManual;
    protected $daishaMap = [
        '指定無し' => 1,
        '指定なし' => 1,
        '台車' => 2,
        'ゴム台車' => 2,
        '手持ち' => 5,
    ];
    protected $checkTrue = ['あり', '有り'];

    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function mapping(): array
    {
        return [
            "store_name" => "F7",
            "bussiness_classification" => "F4",
            "delivery_destination_code" => "F5",
            "destination_name_kana" => "F6",
            "destination_name" => "F7",
            "post_code" => "F8",
            "address_1" => "R8",
            "address_2" => "F9",
            "delivery_frequency_1" => "F12",
            "delivery_frequency_2" => "J12",
            "quantity_delivery" => "H13",
            "first_sd_time_h" => "H14",
            "first_sd_time_i" => "J14",
            "first_sd_sub_min_one" => "N14",
            "first_sd_sub_min_second" => "R14",
            "second_sd_time_h" => "H15",
            "second_sd_time_i" => "J15",
            "second_sub_min_one" => "N15",
            "second_sub_min_second" => "R15",
            "scheduled_time_first" => "T13",
            "vehicle_height_width" => "F16",
            "height" => "R16",
            "width" => "V16",
            "parking_place" => "F17",
            "note_1" => "M17",
            "delivery_slip" => "F18",
            "note_2" => "M18",
            "daisha" => "F19",
            "note_3" => "M19",
            "place" => "F20",
            "note_4" => "F21",
            "empty_recovery" => "F22",
            "key" => "F23",
            "note_5" => "M23",
            "security" => "F24",
            "cancel_method" => "M24",
            "grace_time" => "M25",
            "company_name" => "M26",
            "tel_number" => "F10",
            "tel_number_2" => "T26",
            "inside_rule" => "F27",
            "license" => "M27",
            "reception_or_entry" => "M28",
            "cerft_required" => "F29",
            "note_6" => "M29",
            "elevator" => "F30",
            "note_7" => "M30",
            "waiting_place" => "F31",
            "note_8" => "M31",
//            "delivery_route_map_path" => null,
            "delivery_route_map_other_remark" => "Z31",
//            "parking_position_1_file_path" => null,
            "parking_position_1_other_remark" => "A61",
//            "parking_position_2_file_path" => null,
            "parking_position_2_other_remark" => "Z61",
//            "pass_code" => null,
            "delivery_manual_5" => "AD5",
            "delivery_manual_6" => "AD6",
            "delivery_manual_7" => "AD7",
            "delivery_manual_8" => "AD8",
            "delivery_manual_9" => "AD9",
        ];
    }

    public function model(array $row)
    {
        $this->mapDataObjectImport($row);
        if ($this->objectSore && Arr::get($this->objectSore, 'store_name', null)) {
            $store = Store::query()->where('store_name', Arr::get($this->objectSore, 'store_name'))->first();
            if ($store) {
                $store->update($this->objectSore);
            } else {
                $store = Store::query()->create($this->objectSore);
            }

            $store->delivery_manual()->delete();
            if ($this->objectDeliveryManual && count($this->objectDeliveryManual) > 0) {
                foreach ($this->objectDeliveryManual as $key => $value) {
                    $store->delivery_manual()->create(['content' => $value,]);
                }
            }
        }
        return;
    }

    private function mapDataObjectImport($data)
    {
        $this->objectSore = [
            "store_name" => trim(Arr::get($data, 'store_name')),
            "bussiness_classification" => Arr::get($data, 'bussiness_classification'),
            "delivery_destination_code" => intval(Arr::get($data, 'delivery_destination_code')),
            "destination_name_kana" => Arr::get($data, 'destination_name_kana'),
            "destination_name" => Arr::get($data, 'destination_name'),
            "post_code" => Arr::get($data, 'post_code'),
            "address_1" => Arr::get($data, 'address_1'),
            "address_2" => Arr::get($data, 'address_2'),
            "delivery_frequency" => Arr::get($data, 'delivery_frequency_1') . Arr::get($data, 'delivery_frequency_2'),
            "quantity_delivery" => Arr::get($data, 'quantity_delivery'),
            "first_sd_time" => $this->convertTime(Arr::get($data, 'first_sd_time_h'), Arr::get($data, 'first_sd_time_i')),
            "first_sd_sub_min_one" => Arr::get($data, 'first_sd_sub_min_one'),
            "first_sd_sub_min_second" => Arr::get($data, 'first_sd_sub_min_second'),
            "second_sd_time" => $this->convertTime(Arr::get($data, 'second_sd_time_h'), Arr::get($data, 'second_sd_time_i')),
            "second_sub_min_one" => Arr::get($data, 'second_sub_min_one'),
            "second_sub_min_second" => Arr::get($data, 'second_sub_min_second'),
            "scheduled_time_first" => Arr::get($data, 'scheduled_time_first'),
            "vehicle_height_width" => Arr::get($data, 'scheduled_time_first') ? 1 : 0,
            "height" => Arr::get($data, 'height'),
            "width" => Arr::get($data, 'width'),
            "parking_place" => $this->checkValueWithNeed(Arr::get($data, 'parking_place')),
            "note_1" => Arr::get($data, 'note_1'),
            "delivery_slip" => $this->checkValueWithNeed(Arr::get($data, 'delivery_slip')),
            "note_2" => Arr::get($data, 'note_2'),
            "daisha" => Arr::get($data, 'daisha') ? Arr::get($this->daishaMap, Arr::get($data, 'daisha', 0)) : null,
            "note_3" => Arr::get($data, 'note_3'),
            "place" => Arr::get($data, 'place'),
            "note_4" => Arr::get($data, 'note_4'),
            "empty_recovery" => Arr::get($data, 'empty_recovery'),
            "key" => $this->checkValueWithNeed(Arr::get($data, 'key')),
            "note_5" => Arr::get($data, 'note_5'),
            "security" => $this->checkValueWithNeed(Arr::get($data, 'security')),
            "cancel_method" => Arr::get($data, 'cancel_method'),
            "grace_time" => Arr::get($data, 'grace_time'),
            "company_name" => Arr::get($data, 'company_name'),
            "tel_number" => $this->formatPhoneNumber(Arr::get($data, 'tel_number')),
            "tel_number_2" => $this->formatPhoneNumber(Arr::get($data, 'tel_number_2')),
            "inside_rule" => $this->checkValueWithNeed(Arr::get($data, 'inside_rule')),
            "license" => Arr::get($data, 'license'),
            "reception_or_entry" => Arr::get($data, 'reception_or_entry'),
            "cerft_required" => $this->checkValueWithNeed(Arr::get($data, 'cerft_required')),
            "note_6" => Arr::get($data, 'note_6'),
            "elevator" => $this->checkValueWithNeed(Arr::get($data, 'elevator')),
            "note_7" => Arr::get($data, 'note_7'),
            "waiting_place" => $this->checkValueWithNeed(Arr::get($data, 'waiting_place')),
            "note_8" => Arr::get($data, 'note_8'),
            "delivery_route_map_other_remark" => Arr::get($data, 'delivery_route_map_other_remark'),
            "parking_position_1_other_remark" => Arr::get($data, 'parking_position_1_other_remark'),
            "parking_position_2_other_remark" => Arr::get($data, 'parking_position_2_other_remark'),
        ];

        for ($i = 5; $i <= 9; $i++) {
            if (!empty(Arr::get($data, 'delivery_manual_' . $i))) {
                $this->objectDeliveryManual[] = Arr::get($data, 'delivery_manual_' . $i);
            }
        }
    }

    private function checkValueWithNeed($val)
    {
        if ($val && in_array($val, $this->checkTrue)) {
            return 1;
        } else {
            return 0;
        }
    }

    private function convertTime($hour, $minute)
    {
        if ($hour !== null && $minute !== null) {
            return Carbon::now()->hour((int)$hour)->minute((int)$minute)->format('H:i');
        } elseif ($hour !== null && $minute == null) {
            return Carbon::now()->hour((int)$hour)->format('H:i');
        } elseif ($hour == null && $minute !== null) {
            return Carbon::now()->hour(0)->minute((int)$minute)->format('H:i');
        } else {
            return null;
        }
    }

    private function formatPhoneNumber($phoneNumber)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (strlen($phoneNumber) == 10) {
            $formattedNumber = substr($phoneNumber, 0, 3) . '-' . substr($phoneNumber, 3, 4) . '-' . substr($phoneNumber, 7);
            return $formattedNumber;
        } elseif (strlen($phoneNumber) == 11) {
            $formattedNumber = substr($phoneNumber, 0, 4) . '-' . substr($phoneNumber, 4, 4) . '-' . substr($phoneNumber, 8);
            return $formattedNumber;
        }
        return $phoneNumber;
    }

}
