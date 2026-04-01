<?php

namespace App\Imports;

use App\Models\Route;
use App\Models\Store;
use App\Rules\CheckStoreExistsRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class RouteImport implements ToCollection
{
    protected $departments;
    protected $customers;
    protected $errorsMs;

    public function __construct($departments, $customers)
    {
        $this->departments = $departments;
        $this->customers = $customers;
    }

    public function collection(Collection $rows)
    {
        $rowArr = $rows->toArray();
        $rowsValidation = array_diff_key($rowArr, array_flip([0, 1]));
        $validator = Validator::make($rowsValidation, [
            '*.0' => 'required|exists:departments,name',
            '*.2' => 'required',
            '*.3' => 'required|exists:customers,customer_name',
            '*.4' => ['required', Rule::in(array_keys(ROUTE_FARE_TYPE_NAME))],
//            '*.5' => 'nullable|numeric',
//            '*.6' => 'nullable|numeric',
//            '*.7' => 'nullable|numeric',
//            '*.8' => ['required', new CheckStoreExistsRule()]
        ],
            [
                'required' => 'インポートファイルの{:index}行目にエラーがあります。',
                'exists' => 'インポートファイルの{:index}行目にエラーがあります。',
                'in' => 'インポートファイルの{:index}行目にエラーがあります。',
                'numeric' => 'インポートファイルの{:index}行目にエラーがあります。',
            ]
        );
        $validator->after(function ($validator) {
            $errors = $validator->errors();
            $this->errorsMs = [];
            foreach ($errors->get('*.*') as $key => $messages) {
                $index = explode('.', $key);
                foreach ($messages as $message) {
                    if (!isset($this->errorsMs[$index[0] + 1])) {
                        $this->errorsMs[$index[0] + 1][] = Str::replace(':index', $index[0] + 1, $message);
                    }
                }
            }
        });

        if ($validator->fails()) {
            throw ValidationException::withMessages(Arr::collapse($this->errorsMs));
        }

        foreach ($rowsValidation as $row) {
            $departmentID = Arr::get($this->departments, Arr::get($row, 0));
            $route_chk = Route::withTrashed()->where('id', intval(Arr::get($row, 1)))->first();
            $routeName = Arr::get($row, 2);
            $customerId = Arr::get($this->customers, Arr::get($row, 3));
            $fareType = Arr::get(ROUTE_FARE_TYPE_NAME, Arr::get($row, 4));
            $fare = intval(Arr::get($row, 5), 0);
            $highwayFee = intval(Arr::get($row, 6), 0);
            $highwayFeeHoliday = intval(Arr::get($row, 7), 0);
            $strStore = Arr::get($row, 8);
            $isGovernmentHoliday = boolval(Arr::get($row, 16));
            $listMonth = array_slice($row, 17, 31);
            $listWeek = array_slice($row, 9, 7);

            $data = [
                'department_id' => $departmentID,
                'name' => $routeName,
                'customer_id' => $customerId,
                'route_fare_type' => $fareType,
                'fare' => $fare,
                'highway_fee' => $highwayFee,
                'highway_fee_holiday' => $highwayFeeHoliday,
                'is_government_holiday' => $isGovernmentHoliday
            ];

            //create new user
            if ($route_chk) {
                $route_chk->update($data);
                $route_chk->route_non_delivery()->delete();
                foreach ($listWeek as $key => $week) {
                    $number_at = $key + 1;
                    if (!$week) {
                        continue;
                    }
                    $dataWeek = [
                        'number_at' => $number_at,
                        'is_week' => 1
                    ];
                    $route_chk->route_non_delivery()->updateOrCreate($dataWeek);
                }
                foreach ($listMonth as $key => $date_at) {
                    $number_at = $key + 1;
                    if (!$date_at) {
                        continue;
                    }
                    $dataMonth = [
                        'number_at' => $number_at,
                        'is_week' => 0
                    ];
                    $route_chk->route_non_delivery()->updateOrCreate($dataMonth);
                }
                if ($strStore) {
                    $listVals = explode(',', $strStore);
                    $result = array_map('trim', $listVals);
                    $listIdStore = Store::whereIn('store_name', $result)->pluck('id', 'id')->toArray();
                    $route_chk->stores()->sync($listIdStore);
                }
            } else {
                $route_chk = Route::create($data);
                foreach ($listWeek as $key => $week) {
                    $number_at = $key + 1;
                    if (!$week) {
                        continue;
                    }
                    $dataWeek = [
                        'number_at' => $number_at,
                        'is_week' => 1
                    ];
                    $route_chk->route_non_delivery()->updateOrCreate($dataWeek);
                }

                foreach ($listMonth as $key => $date_at) {
                    $number_at = $key + 1;
                    if (!$date_at) {
                        continue;
                    }
                    $dataMonth = [
                        'number_at' => $number_at,
                        'is_week' => 0
                    ];
                    $route_chk->route_non_delivery()->updateOrCreate($dataMonth);
                }

                $listVals = explode(',', $strStore);
                $result = array_map('trim', $listVals);
                $listIdStore = Store::whereIn('store_name', $result)->pluck('id', 'id')->toArray();
                $route_chk->stores()->sync($listIdStore);
            }
        }
    }
}
