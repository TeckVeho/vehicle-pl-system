<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\S3File;
use App\Models\Vehicle;
use App\Models\VehicleCost;
use App\Models\VehicleITPData;
use App\Models\VehicleITPS3Data;
use Helper\Common;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VehicleITPS3Import implements ToModel, WithChunkReading
{
    use RemembersRowNumber;

    protected $s3File;
    protected $indexField;

    public function __construct(S3File $s3File, $indexField)
    {
        $this->s3File = $s3File;
        $this->indexField = $indexField;
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
        $vehicle_identify_number = Arr::get($row, $this->indexField['vehicle_identify_number']);
        $start_date_time = Arr::get($row, $this->indexField['start_date_time']);
        $end_date_time = Arr::get($row, $this->indexField['end_date_time']);
        $no_number_plate = Arr::get($row, $this->indexField['no_number_plate']);
        $vehicle_id = null;
        if ($vehicle_identify_number) {
            $vehicle = Vehicle::query()
                ->where(DB::raw("LPAD(SUBSTRING_INDEX(`vehicle_identification_number`, '-', -1), 8, '0')"), $vehicle_identify_number)
                ->orWhere(DB::raw("LPAD(SUBSTRING_INDEX(`vehicle_identification_number_2`, '-', -1), 8, '0')"), $vehicle_identify_number)
                ->first();
            if ($vehicle) {
                $vehicle_id = $vehicle->id;
//                var_dump('VehicleITPS3Import vehicle find by identification_number ' . $vehicle->vehicle_identification_number . '<==>' . $vehicle_identify_number);
//                Log::info('VehicleITPS3Import vehicle find by identification_number ' . $vehicle->vehicle_identification_number . '<==>' . $vehicle_identify_number);
                $vhcItpS3Data = VehicleITPS3Data::query()->where('vehicle_identification_number')->where('start_date_time', $start_date_time)->first();
                if (!$vhcItpS3Data) {
                    VehicleITPS3Data::query()->create([
                        'vehicle_identification_number' => $vehicle_identify_number,
                        'no_number_plate' => $no_number_plate,
                        'vehicle_id' => $vehicle_id,
                        'start_date_time' => $start_date_time,
                        'end_date_time' => $end_date_time,
                        's3_files_id' => $this->s3File->id,
                    ]);
                    $this->s3File->status = S3File::STATUS_DONE;
                    $this->s3File->save();
                }
            } else {
                Log::error('VehicleITPS3Import vehicle not match identification_number ==>' . $vehicle_identify_number);
            }
        }
    }
}
