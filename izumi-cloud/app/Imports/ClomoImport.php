<?php

namespace App\Imports;

use App\Models\EmployeeMobileInfo;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ClomoImport implements ToModel, WithStartRow, WithValidation, SkipsOnFailure
{
  use Importable, SkipsFailures;

  /**
   * @param array $row
   *
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  public function model(array $row)
  {
    $employee = DB::table('employees')
      ->whereRaw("REPLACE(name, '/', '') = '" . $this->removeDashCharacter($row[1]) . "'")
      ->first();

    if ($employee) {
      $employeeMobileInfo = EmployeeMobileInfo::where('employee_id', $employee->id)->orderby('created_at', 'DESC')->first();

      if ($employeeMobileInfo) {
        $updated_column = [];

        if ($employeeMobileInfo->device_type !== $row[0]) {
          $updated_column[] = 'device_type';
        }

        if ($employeeMobileInfo->tel !== $row[4]) {
          $updated_column[] = 'tel';
        }

        if ($employeeMobileInfo->android_id !== $row[7]) {
          $updated_column[] = 'android_id';
        }

        if ($employeeMobileInfo->imei_number !== (string)$row[9]) {
          $updated_column[] = 'imei_number';
        }

        if ($employeeMobileInfo->model_name !== $row[10]) {
          $updated_column[] = 'model_name';
        }

        if (count($updated_column) > 0) {
          EmployeeMobileInfo::create([
            'employee_id' => $employee->id,
            'owner' => $employeeMobileInfo->owner,
            'device_type' => $row[0],
            'tel' => $row[4],
            'android_id' => $row[7],
            'imei_number' => $row[9],
            'model_name' => $row[10],
            'updated_column' => implode(',', $updated_column),
            'connected_at' => null,
          ]);
        }
      } else {
        EmployeeMobileInfo::create([
          'employee_id' => $employee->id,
          'owner' => $this->removeDashCharacter($row[1]),
          'device_type' => $row[0],
          'tel' => $row[4],
          'android_id' => $row[7],
          'imei_number' => $row[9],
          'model_name' => $row[10],
          'updated_column' => null,
          'connected_at' => null,
        ]);
      }
    }
  }

  /**
   * @return int
   */
  public function startRow(): int
  {
    return 2;
  }

  /**
   * @return string
   */
  public function removeDashCharacter($string)
  {
    if (strpos($string, '-')) {
      $result = str_replace('-', '', $string);
    } else {
      $result = $string;
    }

    return $result;
  }

  /**
   * @return string
   */
  public function removeSlashCharacter($string)
  {
    if (strpos($string, '//')) {
      $result = str_replace('//', '', $string);
    } else {
      $result = $string;
    }

    return $result;
  }

  public function rules(): array
  {
    return [
      '*.1' => function ($attribute, $value, $onFailure) {
        $employee = DB::table('employees')
          ->whereRaw("REPLACE(name, '/', '') = '" . $this->removeDashCharacter($value) . "'")
          ->first();

        if (!$employee) {
          $onFailure('Employee name: ' . $this->removeDashCharacter($value) . ' is not existed in vehicel master');
        }
      }
    ];
  }
}