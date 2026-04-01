<?php

namespace App\Exports;

use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehicleExportForItp implements FromArray, WithCustomCsvSettings, WithMapping, WithHeadings
{
    protected $datas;

    public function __construct($dataVehicle)
    {
        $this->datas = $dataVehicle;
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => false,
            'output_encoding' => 'SJIS-win',
        ];
    }

    /**
     * @inheritDoc
     */
    public function array(): array
    {
        return $this->datas;
    }

    public function headings(): array
    {
        return [
            '車両番号',
            '所属コード',
            '車両名称',
            'グループコード',
            '車種名称',
            '配送種別コード',
            'エンジン種別コード',
            '契約終了年月日',
            '契約距離',
            'DPR有無',
            '整備区分',
            'タイヤ',
            '登録ナンバー',
            '登録年月日',
            '初度登録年月',
            '車体の形状',
            '車名',
            '最大積載量',
            '車台番号',
            '型式',
            '所有者名称',
            '車両総重量（kg）',
            '乗車定員（人）',
            '一般／旅客',
            '牽引',
            '使用者名称',
            '急ブレーキ多発マップグループコード',
            '有効期間満了日',
            '手動変速時間集計',
            'エンジンオーバー判定回転数（一般道空車）',
            'エンジンオーバー判定回転数（一般道実車）',
            'エンジンオーバー判定回転数（市街地空車）',
            'エンジンオーバー判定回転数（市街地実車）',
            'エンジンオーバー判定回転数（高速道空車）',
            'エンジンオーバー判定回転数（高速道実車）',
            'エンジンオーバー判定回転数（専用道空車）',
            'エンジンオーバー判定回転数（専用道実車）',
            'エンジンオーバー判定回転数（一般道空車坂道）',
            'エンジンオーバー判定回転数（一般道実車坂道）',
            'エンジンオーバー判定回転数（市街地空車坂道）',
            'エンジンオーバー判定回転数（市街地実車坂道）',
            'エンジンオーバー判定回転数（高速道空車坂道）',
            'エンジンオーバー判定回転数（高速道実車坂道）',
            'エンジンオーバー判定回転数（専用道空車坂道）',
            'エンジンオーバー判定回転数（専用道実車坂道）',
            'エンジン回転ノイズフィルタ',
            '車幅（mm）',
            '車高（mm）',
            '車種別料金区分',
            '車両分類',
            'ナビ探索条件',
            'itp_start_time',
            'itp_end_time',
            'itp_is_updated',
            'itp_error_message',
        ];
    }

    public function map($row): array
    {
        return [
            "vehicle_identification_number" => Arr::get($row, 'vehicle_identification_number'),
            "department_code" => Arr::get($row, 'department_code'),
            "no_number_plate" => Arr::get($row, 'no_number_plate'),
            "group_code" => '',
            "truck_classification_2" => Arr::get($row, 'truck_classification_2'),
            "delivery_type_code" => '',
            "engine_type" => '',
            "inspection_expiration_date" => Arr::get($row, 'inspection_expiration_date'),
            "contract_distance" => '',
            "dpr" => '',
            "maintenance_category" => '',
            "tire_size" => '',
            "registration_number" => '',
            "registration_date" => Arr::get($row, 'registration_date'),
            "1st_registration" => Arr::get($row, '1st_registration'),
            "body_shape" => Arr::get($row, 'body_shape'),
            "vehicle_name" => Arr::get($row, 'vehicle_name'),
            "maximum_loading_capacity" => Arr::get($row, 'maximum_loading_capacity'),
            "chassis_number" => Arr::get($row, 'chassis_number'),
            "model" => Arr::get($row, 'model'),
            "owner" => Arr::get($row, 'owner'),
            "vehicle_total_weight" => Arr::get($row, 'vehicle_total_weight'),
            "riding_capacity" => Arr::get($row, 'riding_capacity'),
            "general_passenger" => '',
            "traction" => '',
            "user_name" => Arr::get($row, 'user_name'),
            "blake_map_group_code" => '',
            "validity_expiration_date" => Arr::get($row, 'validity_expiration_date'),
            "aggregation_of_manual_shifting_time" => '',
            "engine_over1" => '',
            "engine_over2" => '',
            "engine_over3" => '',
            "engine_over4" => '',
            "engine_over5" => '',
            "engine_over6" => '',
            "engine_over7" => '',
            "engine_over8" => '',
            "engine_over9" => '',
            "engine_over10" => '',
            "engine_over11" => '',
            "engine_over12" => '',
            "engine_over13" => '',
            "engine_over14" => '',
            "engine_over15" => '',
            "engine_over16" => '',
            "engine_rotation_noise_filter" => '',
            "width" => Arr::get($row, 'width'),
            "height" => Arr::get($row, 'height'),
            "vehicle_type_fee_classification" => '',
            "vehicle_type" => '',
            "navi_research_condition" => '',
            "itp_start_time" => Arr::get($row, 'itp_start_time'),
            "itp_end_time" => Arr::get($row, 'itp_end_time'),
            "itp_is_updated" => Arr::get($row, 'itp_is_updated'),
            "itp_error_message" => Arr::get($row, 'itp_error_message'),
        ];
    }
}

