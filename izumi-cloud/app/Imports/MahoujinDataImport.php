<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Vehicle;
use App\Models\Mahoujin;
use Carbon\Carbon;

class MahoujinDataImport implements ToCollection
{

    protected $typeVehice = [5 => 'vehicle', 6 => 'lease'];
    protected $dataCost;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $year = Carbon::now()->format('Y');
        $costCel = [
            1 => 151,
            2 => 152,
            3 => 153,
            4 => 154,
            5 => 155,
            6 => 156,
            7 => 157,
            8 => 158,
            9 => 159,
            10 => 160,
            11 => 161,
            12 => 162,
        ];
        foreach ($collection as $row => $data) {
            if (intval($data[5]) != 1 && ($data[1] == 5 || $data[1] == 6)) {
                $vehicle = Vehicle::where('vehicle_identification_number', $data[11])->first();
                if ($vehicle) {
                    foreach ($costCel as $month => $cost) {
                        $keyCheck = $vehicle->id. $data[1];
                        $this->dataCost[$keyCheck][$month]= data_get($this->dataCost, "$keyCheck.$month", 0) + intval(str_replace(",", "", $data[$cost]));
                        $mahoujin = Mahoujin::where('type', $this->typeVehice[$data[1]])
                            ->where('year', $year)
                            ->where('month', $month)
                            ->where('vehicle_id', $vehicle->id)
                            ->first();
                        if ($mahoujin) {
                            $mahoujin->cost = $this->dataCost[$keyCheck][$month];
                            $mahoujin->save();
                        } else {
                            Mahoujin::create([
                                'type' => $this->typeVehice[$data[1]],
                                'vehicle_id' => $vehicle->id,
                                'year' => $year,
                                'month' => $month,
                                'cost' => $this->dataCost[$keyCheck][$month],
                            ]);
                        }
                    }
                }
            }
        }
    }
}
