<?php

namespace App\Imports;

use App\Models\PLPCAData;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PCADataImport implements ToModel, WithChunkReading
{
    use RemembersRowNumber;

    protected $allDepartment;
    protected $filePcaTmpName;

    protected $dpMap = [
        201 => '横浜第一',
        202 => '平塚',
        205 => '静岡',
        203 => '横浜第二',
        204 => '横浜第三',
        0 => '管理本部',
        207 => '東京',
        208 => '八千代',
        211 => '武蔵野',
        206 => '千葉',
        209 => '古河',
        224 => '名古屋',
        217 => '安城',
        218 => '浜松',
        219 => '富山',
        215 => '新潟',
        220 => '大阪',
        221 => '神戸',
        225 => '所沢',
        223 => '不動産管理',
    ];

    protected $accountItemMap = [
        6150, 6151, 6154, 6156, 6159, 6160, 6162, 6164, 6165, 6166, 6167, 6168, 6171, 6172,
        6173, 6174, 6177, 6178, 6188, 6189, 6371, 6372, 6373, 7037, 7042, 7045, 7047, 7049, 7050, 7053,
        7054, 7055, 7056, 7057, 7058, 7059, 7060, 7062, 7063, 7064, 7067, 7068, 7069, 7071,
        7072, 7073, 7074, 7075, 7078, 7220, 7420,
    ];

    public function __construct($allDepartment, $filePcaTmpName)
    {
        $this->allDepartment = $allDepartment;
        $this->filePcaTmpName = $filePcaTmpName;
    }

    public function model(array $row)
    {
        $checkData = Arr::get($row, 0);
        if ($checkData && (is_int($checkData) || is_integer($checkData))) {
            self::saveDataPCA($row);
        }
    }

    private function saveDataPCA($row)
    {
        $dpStr = Arr::get($row, 5);
        if ($dpStr && !empty($dpStr)) {
            $resultJson = Storage::get($this->filePcaTmpName);
            $dataFileJson = json_decode($resultJson, 1);

            $date = Carbon::parse((string)Arr::get($row, 0))->format('Ymd');
            $dpName = Arr::get($this->dpMap, (int)Arr::get($row, 5));
            $dpChk = $this->allDepartment->where('name', $dpName)->first();
            $accountItemCode = Arr::get($row, 7);
            $cost = Arr::get($row, 13);

            if ($dpChk && $accountItemCode && in_array($accountItemCode, $this->accountItemMap)) {
                $key = $date . '_' . $dpChk->id . '_' . $accountItemCode;
                $dataFileJson[$key] = data_get($dataFileJson, $key) + $cost;
                $checkPca = PLPCAData::query()
                    ->where('date', Carbon::parse((string)Arr::get($row, 0))->format('Y-m-d'))
                    ->where('department_id', $dpChk->id)
                    ->where('account_item_code', $accountItemCode)->first();
                if ($checkPca) {
                    $checkPca->cost = $dataFileJson[$key];
                    $checkPca->save();
                } else {
                    PLPCAData::query()->create([
                        'date' => Carbon::parse((string)Arr::get($row, 0))->format('Y-m-d'),
                        'department_id' => $dpChk->id,
                        'account_item_code' => $accountItemCode,
                        'cost' => $dataFileJson[$key],
                    ]);
                }
                Storage::put($this->filePcaTmpName, collect($dataFileJson)->toJson());
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
