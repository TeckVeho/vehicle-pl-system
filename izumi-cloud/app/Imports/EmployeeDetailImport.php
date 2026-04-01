<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeAptitudeAssessmentForms;
use App\Models\EmployeeHealthExaminationResults;
use App\Models\EmployeeWelfareExpenses;
use App\Models\UserContacts;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EmployeeDetailImport implements ToModel, WithBatchInserts, WithChunkReading
{
    use RemembersRowNumber;

    private function convertGenderToInt($gender)
    {
        if ($gender === '男性') {
            return 0;
        } else if ($gender === '女性') {
            return 1;
        }
        return null;
    }

    private function convertJobTypeToInt($jobType)
    {
        if ($jobType === 'ドライバー') {
            return 0;
        } else if ($jobType === '事務') {
            return 1;
        } else if ($jobType === 'オペレーター') {
            return 2;
        }
        return null;
    }

    private function convertEmployeeTypeToInt($employeeType)
    {
        if ($employeeType === '正社員') {
            return 0;
        } else if ($employeeType === 'パート') {
            return 1;
        } else if ($employeeType === '派遣社員') {
            return 3;
        }
        return null;
    }

    private function convertLicenseTypeToInt($licenseType)
    {
        if ($licenseType === '普通') {
            return 0;
        } else if ($licenseType === '準中型5t') {
            return 1;
        } else if ($licenseType === '準中型') {
            return 2;
        } else if ($licenseType === '中型8t') {
            return 3;
        } else if ($licenseType === '中型') {
            return 4;
        } else if ($licenseType === '大型') {
            return 5;
        } else if ($licenseType === 'けん引') {
            return 6;
        }
        return null;
    }

    private function convertEmployeeRoleToInt($employeeRole)
    {
        if ($employeeRole === '部長') {
            return 1;
        } else if ($employeeRole === '本部長') {
            return 2;
        } else if ($employeeRole === '常務') {
            return 3;
        } else if ($employeeRole === '社長') {
            return 4;
        }
        return null;
    }

    private function convertTrainingStatusToInt($status)
    {
        if ($status === '完了') {
            return 1;
        } else if ($status === '未完了') {
            return 2;
        }
        return null;
    }

    public function model(array $row)
    {
        $rowIndex = $this->getRowNumber();
        if ($rowIndex > 1) {
            $employee_code = trim(Arr::get($row, 0));
            if ($employee_code) {
                $this->importEmployee($row);
            }
        }
    }

    private function importEmployee($row)
    {
        $date_of_election = trim(Arr::get($row, 5));
        $personal_tel = trim(Arr::get($row, 7));
        $employee_role = trim(Arr::get($row, 10));
        $driver_license_information = trim(Arr::get($row, 14));
        $aptitude_test_date = trim(Arr::get($row, 15));
        $health_checkup_date = trim(Arr::get($row, 16));
        $training_classroom = trim(Arr::get($row, 17));
        $training_practical = trim(Arr::get($row, 18));
        $age_appropriate_interview = trim(Arr::get($row, 20));
        $employee_code = trim(Arr::get($row, 0));
        $employeeChk = Employee::where('employee_code', $employee_code)->first();

        $employeeData = [
            'date_of_election' => $date_of_election ? Carbon::parse($date_of_election)->format('Y-m-d') : null,
            'employee_role' => $this->convertEmployeeRoleToInt($employee_role),
            'driver_license_information' => $driver_license_information,
            'beginner_driver_training_classroom' => $this->convertTrainingStatusToInt($training_classroom),
            'beginner_driver_training_practical' => $this->convertTrainingStatusToInt($training_practical),
            'age_appropriate_interview' => $this->convertTrainingStatusToInt($age_appropriate_interview),
        ];

        $employeeData = array_filter($employeeData, function($value) {
            return $value !== null && $value !== '';
        });

        if ($employeeChk) {
            $employeeChk->update($employeeData);
            $employee = $employeeChk;
        } else {
            $employee = Employee::create($employeeData);
        }

        if ($personal_tel) {
            $this->saveUserContact($employee->employee_code, $personal_tel);
        }

    }

    private function saveUserContact($employee_code, $personal_tel)
    {
        try {
            $existing = UserContacts::where('user_id', $employee_code)->first();
            if ($existing) {
                $existing->update(['personal_tel' => $personal_tel]);
            } else {
                UserContacts::create([
                    'user_id' => $employee_code,
                    'personal_tel' => $personal_tel,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error saving user contact: ' . $e->getMessage());
        }
    }


    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}

