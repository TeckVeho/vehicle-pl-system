<?php

namespace App\Imports;

use App\Models\Vehicle;
use App\Models\VehicleCost;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VehicleCostImport implements ToModel, WithChunkReading
{
    use RemembersRowNumber;
    protected $date;

    public function __construct($date)
    {
        $this->date = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');
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
        //$roleIm = intval();
        $vhc_indent_number = Arr::get($row, 0);
        $vchek = Vehicle::query()->where('vehicle_identification_number', $vhc_indent_number)->first();
        if ($vchek) {
            $checkVhcCost = VehicleCost::query()
                ->where('vehicle_id', $vchek->id)
                ->where('date', $this->date)->first();
            if ($checkVhcCost) {
                $checkVhcCost->lease_depreciation = Arr::get($row, 2);
                $checkVhcCost->car_tax = Arr::get($row, 3);
                $checkVhcCost->maintenance_lease = Arr::get($row, 4);
                $checkVhcCost->save();
            } else {
                VehicleCost::query()->create([
                    'vehicle_id' => $vchek->id,
                    'lease_depreciation' => Arr::get($row, 2),
                    'car_tax' => Arr::get($row, 3),
                    'maintenance_lease' => Arr::get($row, 4),
                    'date' => $this->date,
                ]);
            }
        }
    }
}
