<?php

namespace App\Imports;

use App\Models\DataConnection;
use App\Models\MaintenanceCost;
use App\Models\System;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class MaintenanceCostImport implements ToCollection, WithHeadingRow
{

    public function __construct()
    {

    }

    /**
     * @inheritDoc
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $key => $value) {
            $check = MaintenanceCost::query()->where('cost_code', Arr::get($value, 'cost_id'))->first();
            if ($check) {
                $check->update([
                    'vehicle_id' => Arr::get($value, 'vehicle_id'),
                    'vehicle_identification_number' => Arr::get($value, 'vehicle_identification_number'),
                    'plate' => Arr::get($value, 'plate'),
                    'scheduled_date' => Arr::get($value, 'scheduled_date'),
                    'maintained_date' => Arr::get($value, 'maintained_date'),
                    'total_amount_excluding_tax' => Arr::get($value, 'total_amount_excluding_tax'),
                    'discount' => Arr::get($value, 'discount'),
                    'total_amount_including_tax' => Arr::get($value, 'total_amount_including_tax'),
                    'note' => Arr::get($value, 'note'),
                    'status' => Arr::get($value, 'status'),
                ]);
            } else {
                MaintenanceCost::query()->create([
                    'vehicle_id' => Arr::get($value, 'vehicle_id'),
                    'vehicle_identification_number' => Arr::get($value, 'vehicle_identification_number'),
                    'cost_code' => Arr::get($value, 'cost_id'),
                    'plate' => Arr::get($value, 'plate'),
                    'scheduled_date' => Arr::get($value, 'scheduled_date'),
                    'maintained_date' => Arr::get($value, 'maintained_date'),
                    'total_amount_excluding_tax' => Arr::get($value, 'total_amount_excluding_tax'),
                    'discount' => Arr::get($value, 'discount'),
                    'total_amount_including_tax' => Arr::get($value, 'total_amount_including_tax'),
                    'note' => Arr::get($value, 'note'),
                    'status' => Arr::get($value, 'status'),
                ]);
            }
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}
