<?php

namespace App\Imports;

use App\Models\DataConnection;
use App\Models\System;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class DataInfoImport implements ToCollection, WithHeadingRow
{

    public function __construct()
    {

    }


    /**
     * @inheritDoc
     */
    public function collection(Collection $collection)
    {
        $allSystems = System::all()->collect();
        $frequencyConf = [
            'Daily' => 'dailyAt',
            'Weekly' => 'weeklyOn',
            'Monthly' => 'monthlyOn',
            'hourlyDays' => 'hourlyDays',
            'hourlyBetween' => 'hourlyBetween',
            'hourlyAt' => 'hourlyAt',
            'hourlyAtBetween' => 'hourlyAtBetween',
            'dailyAt' => 'dailyAt',
            'twiceDaily' => 'twiceDaily',
            'weeklyOn' => 'weeklyOn',
            'monthly' => 'monthly',
            'monthlyOn' => 'monthlyOn',
            'twiceMonthly' => 'twiceMonthly',
            'lastDayOfMonth' => 'lastDayOfMonth',
            'yearlyOn' => 'yearlyOn',
            'weekdaysHourlyBetween' => 'weekdaysHourlyBetween',
            'weekendsHourlyBetween' => 'weekendsHourlyBetween',
            'sundaysHourlyBetween' => 'sundaysHourlyBetween',
            'mondaysHourlyBetween' => 'mondaysHourlyBetween',
            'tuesdaysHourlyBetween' => 'tuesdaysHourlyBetween',
            'wednesdaysHourlyBetween' => 'wednesdaysHourlyBetween',
            'thursdaysHourlyBetween' => 'thursdaysHourlyBetween',
        ];
        $weekConf = ['月' => 1, '火' => 2, '水' => 3, '木' => 4, '金' => 5, '土' => 6, '日' => 7];

        foreach ($collection as $key => $value) {
            if (!data_get($value, 'data_name')) {
                continue;
            }
            $from = $allSystems->where('name', trim(Arr::get($value, 'from')))->first();
            $to = $allSystems->where('name', trim(Arr::get($value, 'to')))->first();
            if (!$from || !$to) {
                continue;
            }
            $type = Str::contains(Arr::get($value, 'type'), 'Active') ? 'active' : 'passive';
            $connectionFrequencyArr = explode('/', Arr::get($value, 'connection_frequency'));
            $frequency = Arr::get($frequencyConf, trim(Arr::get($connectionFrequencyArr, 1)));
            $frequency = $frequency ? $frequency : 'any';
            $dateOn = Arr::get($value, 'date_on');
            $weekOn = Arr::get($value, 'week_on');
            $weekOnInt = Arr::get($value, 'week_on') ? Arr::get($weekConf, Arr::get($value, 'week_on')) : null;
            $data_code = Arr::get($value, 'data_code');
            $timeAt = Date::excelToDateTimeObject(Arr::get($value, 'time_at'))->format('H:i');
            if ($frequency == 'hourlyAtBetween') {
                $timeAt = (int)Arr::get($value, 'time_at');
            }
            if ($data_code && in_array($data_code, ['ICL_1010', 'ICL_1011']) && App::environment('staging')) {
                $timeAt = Date::excelToDateTimeObject(Arr::get($value, 'time_at'))->add(new DateInterval('PT1H'))->format('H:i');
            }
            $frequency_between = Arr::get($value, 'frequency_between');
            if ($frequency_between) {
                $frequency_between = json_decode($frequency_between);
            }
            $IsUpdate = Arr::get($value, 'is_update');

            $connection_timing = '';
            $slat = '';
            switch ($frequency) {
                case 'dailyAt':
                    $connection_timing = $timeAt;
                    break;
                case 'weeklyOn':
                    if ($weekOn && $timeAt) {
                        $slat = '/';
                    }
                    $connection_timing = $weekOn . $slat . $timeAt;
                    break;
                case 'monthlyOn':
                    if ($dateOn && $timeAt) {
                        $slat = '/';
                    }
                    $connection_timing = $dateOn . $slat . $timeAt;
                    break;
                case 'hourlyBetween':
                case 'hourlyAtBetween':
                    if ($frequency_between) {
                        $connection_timing = Arr::get($frequency_between, 0, '') . '~' . Arr::get($frequency_between, 1, '');
                    }
                    break;
            }
            $dataconnect = [
                "name" => Arr::get($value, 'data_name'),
                "from" => $from->id,
                "to" => $to->id,
                "type" => $type,
                "frequency" => $frequency,
                "date_on" => $dateOn,
                "week_on" => $weekOnInt,
                "time_at" => $timeAt,
                "frequency_between" => $frequency_between,
                "connection_frequency" => trim(Arr::get($connectionFrequencyArr, 0)),
                "connection_timing" => $connection_timing,
                "service_class_name" => Arr::get($value, 'service_class_name'),
                "remark" => Arr::get($value, 'remark'),
                "is_import" => Arr::get($value, 'is_import'),
                "import_to_table" => Arr::get($value, 'import_to_table'),
                "file_name_map" => Arr::get($value, 'file_name_map'),
                "data_code" => Arr::get($value, 'data_code'),
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ];
            $dataConnect = DataConnection::withTrashed()
                ->where(DataConnection::DATA_CODE, Arr::get($value, 'data_code'))
                ->first();
            $listNameRole = [];
            foreach (LIST_ROLE as $role) {
                $roleMaster = Arr::get($value, 'role_' . $role);
                if ($roleMaster) {
                    $listNameRole[] = $role;
                }
            }
            if ($dataConnect) {
                if ($dataConnect->frequency && $dataConnect->frequency == 'any') {
                    unset($dataconnect['frequency']);
                }
                if ($IsUpdate) {
                    $dataConnect->update($dataconnect);
                    $dataConnect->syncRoles($listNameRole);
                }
            } else {
                $dataConnect = DataConnection::create($dataconnect);
                $dataConnect->syncRoles($listNameRole);
            }
        }
        DB::commit();
    }

    public function headingRow(): int
    {
        return 1;
    }

}
