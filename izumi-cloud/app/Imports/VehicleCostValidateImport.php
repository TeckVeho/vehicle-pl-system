<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class VehicleCostValidateImport implements ToModel, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
    }

    /**
     * @return int
     */
//    public function startRow(): int
//    {
//        return 1;
//    }

    public function rules(): array
    {
        return [
            '*.2' => ['required', function ($attribute, $value, $onFailure) {
                $rowindex = explode('.', $attribute);
                if (!is_numeric($value) && $rowindex[0] > 1) {
                    $onFailure('インポートデータの[' . 'C' . $rowindex[0] . ']に不適切なデータが含まれています。');
                }
            }],
            '*.3' => ['required', function ($attribute, $value, $onFailure) {
                $rowindex = explode('.', $attribute);
                if (!is_numeric($value) && $rowindex[0] > 1) {
                    $onFailure('インポートデータの[' . 'D' . $rowindex[0] . ']に不適切なデータが含まれています。');
                }
            }],
            '*.4' => ['required', function ($attribute, $value, $onFailure) {
                $rowindex = explode('.', $attribute);
                if (!is_numeric($value) && $rowindex[0] > 1) {
                    $onFailure('インポートデータの[' . 'E' . $rowindex[0] . ']に不適切なデータが含まれています。');
                }
            }]
        ];
    }
}
