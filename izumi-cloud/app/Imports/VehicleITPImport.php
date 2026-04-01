<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Vehicle;
use App\Models\VehicleCost;
use App\Models\VehicleITPData;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VehicleITPImport implements ToModel, WithChunkReading
{
    use RemembersRowNumber;
    protected $date;
    protected $type_code;
    protected $strArrDpNames;
    protected $pathFileInZip;

    public function __construct($date, $type_code, $strArrDpNames, $pathFileInZip = '')
    {
        $this->date = $date;
        $this->type_code = $type_code;
        $this->strArrDpNames = $strArrDpNames;
        $this->pathFileInZip = $pathFileInZip;
    }

    public function model(array $row)
    {
        $rowIndex = $this->getRowNumber();
        if ($rowIndex > 1) {
            self::mapAndSaveData($row);
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function mapAndSaveData($row)
    {
        $arrDpNames = explode(",", $this->strArrDpNames);
        $vehicle_identify_number = Arr::get($row, 1);
        if ($vehicle_identify_number) {
            $vehicle = Vehicle::query()
                ->where(DB::raw("LPAD(SUBSTRING_INDEX(`vehicle_identification_number`, '-', -1), 8, '0')"), $vehicle_identify_number)
                ->orWhere(DB::raw("LPAD(SUBSTRING_INDEX(`vehicle_identification_number_2`, '-', -1), 8, '0')"), $vehicle_identify_number)
                ->first();
            if ($vehicle) {
                $departmentId = $vehicle->department_id;
                if ($arrDpNames && count($arrDpNames) == 1) {
                    $department = Department::query()->where('name', Arr::get($arrDpNames, 0))->first();
                    $departmentId = $department ? $department->id : $departmentId;
                }
                $vhcItpData = VehicleITPData::query()
                    ->where('type', $this->type_code)
                    ->where('vehicle_id', $vehicle->id)
                    ->where('department_id', $departmentId)
                    ->where('year', Carbon::parse($this->date)->year)
                    ->where('month', Carbon::parse($this->date)->month)
                    ->first();
                if ($this->type_code == 'etc') {
                    //etc column 'n'=>13
                    $cost = (double)Arr::get($row, 8, 0);
                } else {
                    //km/L column 'n'=>13
                    $cost = (double)Arr::get($row, 13, 0);
                }
                if ($vhcItpData) {
                    $vhcItpData->cost = $cost;
                    $vhcItpData->save();
                } else {
                    VehicleITPData::query()->create([
                        'type' => $this->type_code,
                        'vehicle_id' => $vehicle->id,
                        'department_id' => $departmentId,
                        'year' => Carbon::parse($this->date)->year,
                        'month' => Carbon::parse($this->date)->month,
                        'cost' => $cost,
                    ]);
                }
            }
        }
        if (Storage::exists($this->pathFileInZip)) {
            Storage::delete($this->pathFileInZip);
        }
    }
}
