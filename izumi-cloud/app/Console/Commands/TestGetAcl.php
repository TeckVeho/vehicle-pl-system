<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonPeriod;
use App\Models\Vehicle;
use App\Models\AlcoholCheckLogsWorkShift;
use App\Models\Employee;


class TestGetAcl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-get-acl';

    /**
     * The console command description. 
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $dataConnection;
    protected $dataItem;
    protected $dataContent;
    protected $type = 'active';
    protected $dateKeeper;
    protected $dateKeeperStart;
    protected $updateItDate = true;
    protected $errorIT = null;
    protected $previousConnectionData = [];
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = null;
        Log::info("IT V2 started at: " . Carbon::now()->toDateTimeString());
        if (!$date) {
            $date = @trim(file_get_contents(storage_path("itv2-date.txt")));
            $this->dateKeeperStart = $date;
            $startDate = Carbon::parse($this->dateKeeperStart)->subDays(2);
            $dateCf = Carbon::now();
        } else {
            $this->dateKeeperStart = $date;
            $startDate = Carbon::parse($this->dateKeeperStart)->subDays(2);
            $dateCf = Carbon::parse($this->dateKeeperStart);
        }
        $noNumberPlateInvalidName = ['代車レンタカー', '代車その他営業所XXXX', '点呼者', '同乗', '構内作業', '事務点呼者', 
        '事務員', '代車', '事務作業', '出退勤確認用', '倉庫作業員', '予備車', '点呼者事務員'];
        //$result = CarbonPeriod::create($startDate, '1 day', $dateCf);
        $month = 8;
        $year = 2025;
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $result = CarbonPeriod::create($startDate, '1 day', $endDate);
        foreach ($result as $dt) {
            $keySigna = Config::get('common.itv2_keeper_key');
            $domain_it_keeper = ITV2_KEEPER_URL_API;
            $dateStr = $dt->format('Y/m/d');
            $time = Carbon::now()->timestamp;
            $env = App::environment();
            $urlSigna = "/apitenkos/get?action=1&time=$time&company=hmftphYm&office=&employee=&tenkoDate=$dateStr&env=$env";
            $signature = hash_hmac('sha256', $urlSigna, $keySigna);

            $url = $domain_it_keeper . $urlSigna . "&signature=$signature";
            $response = Http::timeout(60)->get($url)->json();
            if (Arr::get($response, 'dataCount') > 0) {
                foreach (Arr::get($response, 'data') as $key => $value) {
                    if (Arr::get($value, 'mae_tenkoResult', 'NG') == 'OK' || Arr::get($value, 'ato_tenkoResult', 'NG') == 'OK') {
                        $employeeCd = intval($value['employeeCd']);
                        $no_number_plate = '';
                        $vehicleId= null;
                        if (!empty(Arr::get($value, 'mae_car_bangou', ''))) {
                            $no_number_plate = str_replace(['-', '・'], '', $value['mae_car_bangou']);
                            $vehicle = Vehicle::whereHas('latestNumberPlateHistory', function ($query) use ($no_number_plate) {
                                $query->where('no_number_plate', $no_number_plate);
                            })->first();
                            $vehicleId = $vehicle ? $vehicle->id : null;
                        }
                        if (!$employeeCd || $employeeCd <= 0) {
                            $employeeCd = (int)Arr::get(explode('_', $value['employeeName']), 1);
                        }

                        if (!empty(Arr::get($value, 'mae_tenkoTime', '')) && $employeeCd) {
                            $dataDate = Carbon::parse($value['mae_tenkoTime'])->format('Y-m-d H:i:s');
                            $codeCheck = $employeeCd . Carbon::parse($dataDate)->timestamp;
                            if (!in_array($codeCheck, $this->previousConnectionData) && !in_array($no_number_plate, $noNumberPlateInvalidName)) {
                                $this->dataContent['keeper'][] = [
                                    "employee_code" => $employeeCd,
                                    "employee_name" => $value['employeeName'],
                                    "type" => 0,
                                    "date" => $dataDate,
                                    "no_number_plate" => $no_number_plate,
                                    "vehicle_id" => $vehicleId,
                                ];


                            }
                        }
                    } else {
                        $dataNg = [
                            "employee_code" => $value['employeeCd'],
                            "employee_name" => $value['employeeName'],
                            "mae_tenkoTime" => $value['mae_tenkoTime'],
                            "ato_tenkoTime" => $value['ato_tenkoTime'],
                            "no_number_plate" => $value['mae_car_bangou'],
                        ];
                        Log::info("Data NG: " . json_encode($dataNg));
                    }
                }

            } else {
                $this->updateItDate = false;
                $this->errorIT[] = Arr::get($response, 'error');
                $this->changeStatus('fail', json_encode(Arr::get($response, 'error')));
                break;
            }
            $this->dateKeeper = $dateStr;
        }
        $this->matchAclData($this->dataContent['keeper'], "employee_name");
        Log::info("Data: " . json_encode($this->dataContent));
    }

    private function matchAclData($aclData, $by = "employee_code", $flag = 0)
    {
        $count = 0;
        $aclDataInsert = [];
        $specifyUser = [
            "99999"
        ];
        $aclDataOfSpecifyUser = [];
        $is_calculate_previous_month = [];
        foreach ($aclData as $key => $value) {
            if ($by == "employee_name") {
                $employee = $this->whereByEmployeeName('name', $value['employee_name']);
                if ($employee) {
                    $aclDataInsert = [
                        "employee_code" => $employee->employee_code,
                        "employee_name" => str_replace('//', '　', $employee->name),
                        "employee_id" => $employee->id,
                        "type" => $value['type'],
                        "date" => $value['date'],
                        "no_number_plate" => $value['no_number_plate'],
                        "created_at" => Carbon::now('Asia/Tokyo'),
                        "updated_at" => Carbon::now('Asia/Tokyo'),
                    ];
                    $AlcoholCheckExist = AlcoholCheckLogsWorkShift::where('employee_code', Arr::get($aclDataInsert, 'employee_code'))
                        ->where('type', Arr::get($aclDataInsert, 'type'))
                        ->where('date', Arr::get($aclDataInsert, 'date'))->first();
                    if (!$AlcoholCheckExist) {
                        $AlcoholCheckLog = AlcoholCheckLogsWorkShift::insert($aclDataInsert);
                    }
                } else {
                    $employeeCode = '';
                    $employeeName = '';
                    if (strpos($value['employee_name'], '_') !== false) {
                        // Tách chuỗi thành mảng sử dụng '_' làm điểm tách
                        $parts = explode('_', $value['employee_name']);
                        $employeeCode = $parts[1];
                        $employeeName = $parts[0];
                    }
                    if($employeeCode){
                        $aclDataInsert = [
                            "employee_code" => $employeeCode,
                            "employee_name" => $employeeName,
                            "employee_id" => null,
                            "type" => $value['type'],
                            "date" => $value['date'],
                            "no_number_plate" => $value['no_number_plate'],
                            "created_at" => Carbon::now('Asia/Tokyo'),
                            "updated_at" => Carbon::now('Asia/Tokyo'),
                        ];
                        $AlcoholCheckLog = AlcoholCheckLogsWorkShift::insert($aclDataInsert);
                    }
                }
            } else if ($by == "employee_code") {
                if (in_array($value['employee_code'], $specifyUser)) {
                    $aclDataOfSpecifyUser[][] = [
                        "employee_code" => $value['employee_code'],
                        "employee_name" => $value['employee_name'],
                        "date" => $value['date'],
                        "type" => $value['type'],
                        "department" => $value->department
                    ];
                }
                $employee = Employee::where('employee_code', $value['employee_code'])->first();
                if ($employee) {
                    Log::info("Match by code: " . json_encode($value));
                    $aclDataInsert = [
                        "employee_code" => $employee->employee_code,
                        "employee_name" => str_replace('//', '　', $employee->name),
                        "employee_id" => $employee->id,
                        "type" => $value['type'],
                        "date" => $value['date'],
                        "no_number_plate" => $value['no_number_plate'],
                        "created_at" => Carbon::now('Asia/Tokyo'),
                        "updated_at" => Carbon::now('Asia/Tokyo'),
                    ];
                    $AlcoholCheckExist = AlcoholCheckLogsWorkShift::where('employee_code', Arr::get($aclDataInsert, 'employee_code'))
                        ->where('type', Arr::get($aclDataInsert, 'type'))
                        ->where('date', Arr::get($aclDataInsert, 'date'))->first();
                    if (!$AlcoholCheckExist) {
                        $AlcoholCheckLog = AlcoholCheckLogsWorkShift::insert($aclDataInsert);
                    }
                } else {
                    Log::info("Not Match by code: " . json_encode($value));
                    $aclDataInsert = [
                        "employee_code" => $value['employee_code'],
                        "employee_name" => $value['employee_name'],
                        "employee_id" => null,
                        "type" => $value['type'],
                        "date" => $value['date'],
                        "no_number_plate" => $value['no_number_plate'],
                        "created_at" => Carbon::now('Asia/Tokyo'),
                        "updated_at" => Carbon::now('Asia/Tokyo'),
                    ];
                    $AlcoholCheckLog = AlcoholCheckLogsWorkShift::insert($aclDataInsert);
                    $this->dataMisMatch['by_employee_code'][] = ['code' => $value['employee_code'], 'date' => $value['date']];
                    Log::info("Miss Match by code: " . json_encode($value));
                }
            }
        }
        
    }

    private function whereByEmployeeName($collumn, $value)
    {
        $employeeCode = null;
        $employeeName = $value;
        if (strpos($value, '_') !== false) {
            // Tách chuỗi thành mảng sử dụng '_' làm điểm tách
            $parts = explode('_', $value);
            $employeeCode = $parts[1];
            $employeeName = $parts[0];
        }

        $value = preg_replace('/ |　/', '', $employeeName);
        $raw = "REPLACE(&collumn&, '/', '') = '&name&'";
        $raw = str_replace('&collumn&', $collumn, $raw);
        $raw = str_replace('&name&', $value, $raw);
        $employee = Employee::query()
            ->whereRaw($raw)
            ->when($employeeCode, function ($query) use ($employeeCode) {
                return $query->orWhere('employee_code', $employeeCode);
            })
            ->orderByDesc('id')->first();
        return $employee;
    }
}