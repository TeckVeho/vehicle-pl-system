<?php

namespace App\Imports;

use App\Models\BaseSalary;
use App\Models\DataConnection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class BaseSalaryImport implements ToCollection, WithHeadingRow
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
            $dataConnect = BaseSalary::create([
                'monthly_salary' => Arr::get($value, 'monthly_salary'),
                'min' => Arr::get($value, 'min'),
                'max' => Arr::get($value, 'max'),
            ]);
        }
        return;
    }

    public function headingRow(): int
    {
        return 1;
    }

}
