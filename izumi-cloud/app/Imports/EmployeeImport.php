<?php

namespace App\Imports;

use App\Jobs\SyncEmployeeDepartmentJob;
use App\Jobs\SyncEmployeesJob;
use App\Models\BaseSalary;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeWelfareExpenses;
use App\Models\InsuranceRate;
use App\Models\User;
use Carbon\Carbon;
use Helper\Common;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\Role;

class EmployeeImport implements ToModel, WithBatchInserts, WithChunkReading
{
    use RemembersRowNumber;

    const jobType = [
        3 => 0,
        4 => 2,
        5 => 1
    ];

    const employeeType = [
        1 => 0,
        2 => 0,
        3 => 3, // temp type
        4 => 1,
        5 => 1
    ];

    public function model(array $row)
    {
        $rowIndex = $this->getRowNumber();
        if ($rowIndex > 1) {
            $checkDp = Department::query()->where('id', intval(Arr::get($row, 20)))->first();
            $employee_code = $row[0];
            if ($checkDp && $employee_code) {
                self::mapUser($row);
                self::mapEmployee($row);
            }
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

    private function mapUser($row)
    {
        $user_chk = User::withTrashed()->where('id', intval(Arr::get($row, 0)))->first();
        $roleIm = intval(Arr::get($row, 11));
        $allDp = Department::query()->get()->pluck('name', 'id')->toArray();
        $specialDp = Arr::get($row, Common::excelColumnToIndex('CM'));
        $department_code = intval(Arr::get($row, 20));
        if ($specialDp) {
            foreach ($allDp as $key => $value) {
                if (Str::contains($specialDp, $value)) {
                    $department_code = $key;
                    break;
                }
            }
        }
        //create new user
        if ($roleIm && in_array($roleIm, [5])) {
            $role = Role::findByName(ROLE_CLERKS);
        } else {
            $role = Role::findByName(ROLE_CREW);
        }
        if ($user_chk) {
            if (Arr::get($row, 8, null)) {
                if (Carbon::parse(Common::japanDateToDate(Arr::get($row, 8)))->format('Ymd') <= Carbon::now()->format('Ymd')) {
                    $user_chk->deleted_at = Carbon::parse(Common::japanDateToDate(Arr::get($row, 8)));
                    $user_chk->expected_retirement_date = null;
                } else {
                    $user_chk->expected_retirement_date = Carbon::parse(Common::japanDateToDate(Arr::get($row, 8)));
                    $user_chk->deleted_at = null;
                }
            }
            if ($user_chk->role) {
                $role_check = Role::find($user_chk->role);
                if ($role_check) {
                    $user_chk->syncRoles($role_check);
                } else {
                    $user_chk->syncRoles($role);
                }
            } else {
                $user_chk->role = $role->id;
                $user_chk->syncRoles($role);
            }
            $user_chk->name = trim(Arr::get($row, 1));
            $user_chk->department_code = $department_code;
            $user_chk->save();
        } else {
            $user_chk = User::create([
                'id' => intval(Arr::get($row, 0)),
                'name' => trim(Arr::get($row, 1)),
                'role' => $role->id,
                'department_code' => $department_code,
            ]);
            $user = User::withTrashed()->where('id', $user_chk->id)->first();
            $user->syncRoles($role);
        }
    }

    private function mapEmployee($row)
    {
        //$employeeArr = json_decode(Storage::get(TEMP_DISK . "/tmp.txt"));

        $isValid = $this->validateData($row);
        if ($isValid === true) {
            self::newOrUpdateEmployee($row);
        } else {
            $employeeArr[] = [
                'employee_code' => (int)Arr::get($row, 0),
                'validate_import' => $isValid
            ];
            //Storage::put(TEMP_DISK . "/tmp.txt", json_encode($employeeArr));
        }
    }

    private function validateData($data)
    {
        $departmentCode = (int)Arr::get($data, 9, null);
        $jobType = (int)Arr::get($data, 11, null);
        $departmentNewCode = Arr::get($data, 20, null);
        $employeeType = (int)Arr::get($data, 2, null);
        $checkDepartmentExit = Department::find($departmentCode);
        if (!$checkDepartmentExit) {
            return "Invalid department code: " . (int)$departmentCode;
        }

        if (!$jobType || Arr::get(self::jobType, (int)$jobType) === null) {
            return "Invalid Job type: " . (int)$jobType;
        }

        if (!$employeeType || Arr::get(self::employeeType, (int)$employeeType) === null) {
            return "Invalid employee_type: " . (int)$employeeType;
        }
        return true;
    }

    private function newOrUpdateEmployee($row)
    {
        $name = preg_replace('/  +/', '//', $row[1]);
        $name = preg_replace('/ |　/', '//', $name);
        $welfare_expense = $this->CalculateWelfareExpenses(intval($row[22]), Carbon::parse(Common::japanDateToDate($row[6]))->diff(Carbon::now())->y);

        $retirement_date = null;
        if (Arr::get($row, 8, null)) {
            if (Carbon::parse(Common::japanDateToDate(Arr::get($row, 8)))->format('Ymd') <= Carbon::now()->format('Ymd')) {
                $retirement_date = Carbon::parse(Common::japanDateToDate(Arr::get($row, 8)));
            }
        }
        $allDp = Department::query()->get()->pluck('name', 'id')->toArray();
        $specialDp = Arr::get($row, Common::excelColumnToIndex('CM'));
        $department_code = intval(Arr::get($row, 20));
        if ($specialDp) {
            foreach ($allDp as $key => $value) {
                if (Str::contains($specialDp, $value)) {
                    $department_code = $key;
                    break;
                }
            }
        }
        $address = $row[95] ? ($row[99] ? $row[95] . ',' . $row[96] . ',' . $row[97] . ',' . $row[98] . ',' . $row[99] : $row[95] . ',' . $row[96] . ',' . $row[97] . ',' . $row[98] . ',' . $row[99]) : null;
        $employee = [
            "employee_code" => (int)$row[0],
            "employee_type" => self::employeeType[(int)$row[2]],
            'grade' => (int)$row[13],
            "employee_grade_2" => (int)$row[15],
            "temp_wage" => (int)$row[24],
            "sex" => $row[5] == '男性' ? 0 : 1,
            "birthday" => Common::japanDateToDate($row[6]),
            "hire_start_date" => Common::japanDateToDate($row[7]),
            "retirement_date" => $retirement_date,
            "job_type" => self::jobType[(int)$row[11]],
            'license_type' => 0,
            'boarding_employee_grade' => 1,
            'boarding_employee_grade_2' => 1,
            'midnight_worktime' => 0,
            'schedule_working_hours' => 0,
            'daily_transportation_cp' => 0,
            'welfare_expense' => $welfare_expense,
            'fixed_salary' => $row[25],
            'final_department_id' => $department_code,
            'name' => $name,
            'driver_license_number' => $row[1018],
            'name_in_furigana' => $row[24],
            'address' => $address,
        ];

        $employeeChk = Employee::where('employee_code', (int)$row[0])->first();

        if ($employeeChk) {
            $employeeChk->update($employee);
            //sysn employ update
            $employee["id"] = $employeeChk->id;
        } else {
            $employeeChk = Employee::create($employee);
            //sysn employ create
        }
        self::checkChangeDepartment($row);
        self::syncWorkingDepartment($row);
        self::saveHistoryWelfareExpenses($employeeChk->id, $welfare_expense);
    }

    private function checkChangeDepartment($row)
    {
        $employee_code = (int)$row[0];
        $change_dp = (int)$row[18];//S 0
        $hire_start_date = Common::japanDateToDate($row[7]);//H
        $change_dp_date = Common::japanDateToDate($row[17]);//R
        $departmentCode = intval(Arr::get($row, 9));//J
        $newDepartment = (int)$row[20];//U
        $employeeDpCurrent = null;
        $allDp = Department::query()->get()->pluck('name', 'id')->toArray();
        $specialDp = Arr::get($row, Common::excelColumnToIndex('CM'));
        $departmentCodeCm = null;
        if ($specialDp) {
            foreach ($allDp as $key => $value) {
                if (Str::contains($specialDp, $value)) {
                    $departmentCodeCm = $key;
                    break;
                }
            }
        }
        $employeeDpCurrentCk = Employee::with('departments')->withCount('departments')
            ->where('employee_code', $employee_code)->first();
        if ($employeeDpCurrentCk && $employeeDpCurrentCk->departments_count > 0) {
            $employeeDpCurrent = $employeeDpCurrentCk->departments()->orderBy('start_date', 'desc')->first();
        }

        if ($departmentCodeCm) {
            $employee = Employee::with('departments')->withCount('departments')
                ->where('employee_code', $employee_code)->first();
            if ($employee) {
                Log::info('import emploey with dp CM');
                $newComeDate = Carbon::now()->format('Y-m-d');
                $employeeChk = Employee::query()
                    ->whereHas('departments', function ($query) use ($departmentCodeCm, $newComeDate) {
                        $query->whereDate('start_date', '>=', $newComeDate);
                    })
                    ->where('employee_code', $employee_code)
                    ->first();
                if (!$employeeChk) {
                    Log::info('thêm mới emp vơi dp CM');
                    $employee->departments()->attach($departmentCodeCm,
                        [
                            'employee_data' => json_encode($employee->toArray()),
                            'start_date' => $newComeDate
                        ]);
                }
            }
        } else {
            if ($change_dp == 1 && $change_dp_date && ($employeeDpCurrent && $employeeDpCurrent->id !== $newDepartment)) {
                $employee = Employee::with('departments')->withCount('departments')
                    ->where('employee_code', $employee_code)->first();
                if ($employee) {
                    $newComeDate = Carbon::createFromFormat('Y-m-d', $change_dp_date)->format('Y-m-d');
                    $employee = Employee::with('departments')->withCount('departments')
                        ->where('employee_code', $employee_code)->first();
                    $employeeCome = $employee->departments()->whereDate('start_date', '>=', $newComeDate)->get();
                    if ($employeeCome->count() <= 0) {
                        $employee->departments()->attach($newDepartment,
                            [
                                'employee_data' => json_encode($employee->toArray()),
                                'start_date' => $newComeDate
                            ]);
                    }
                }
            } else {
                $employee = Employee::with('departments')->withCount('departments')
                    ->where('employee_code', $employee_code)->first();
                if ($employee) {
                    $newComeDate = $change_dp_date ? Carbon::createFromFormat('Y-m-d', $change_dp_date)->format('Y-m-d') : null;
                    if ($employee->final_department_id == $departmentCode) {
                        $employee->departments()
                            ->where('employee_id', $employee->id)
                            ->where('department_id', $departmentCode)
                            ->update(['employee_data' => json_encode($employee->toArray())]);
                    }
                    if ($newComeDate) {
                        if ($employee->departments_count <= 0) {
                            $employee->departments()->attach($departmentCode,
                                [
                                    'employee_data' => json_encode($employee->toArray()),
                                    'start_date' => $newComeDate
                                ]);
                        } else {
                            $employee->departments()
                                ->where('employee_id', $employee->id)
                                ->where('department_id', $departmentCode)
                                ->where('start_date', $newComeDate)
                                ->update(
                                    [
                                        'employee_data' => json_encode($employee->toArray()),
                                        'start_date' => $newComeDate
                                    ]);
                        }
                    } else {
                        $employee->departments()
                            ->where('employee_id', $employee->id)
                            ->where('department_id', $departmentCode)
                            ->where('start_date', Carbon::createFromFormat('Y-m-d', $hire_start_date)->format('Y-m-d'))
                            ->update(
                                [
                                    'employee_data' => json_encode($employee->toArray()),
                                    'start_date' => Carbon::createFromFormat('Y-m-d', $hire_start_date)->format('Y-m-d')
                                ]);
                    }
                }
            }
        }
    }

    private function syncWorkingDepartment($row)
    {
        $employee_code = (int)$row[0];
        $employee = Employee::with(['departments', 'departmentWorkings'])->withCount('departments')
            ->where('employee_code', $employee_code)->first();
//        $departmentWorkingData = $employee->departmentWorkings()->wherePivot('is_support', 0)
//            ->orderBy('start_date', 'asc')->first();
        $employee->departmentWorkings()->wherePivot('is_support', 0)->detach();

        if ($employee->departments_count > 0) {
            $employeeDepartments = $employee->departments()->get();
            foreach ($employeeDepartments as $employeeDepartment) {
                $employee_first_data = DB::table('employee_department')->where('employee_id', $employee->id)->first();
                $employee_data = json_decode($employee_first_data->employee_data, 1);
                $employee->departmentWorkings()->attach($employeeDepartment->id, [
                    "start_date" => $employeeDepartment->pivot->start_date,
                    'grade' => Arr::get($employee_data, 'grade', 0),
                    "employee_grade_2" => Arr::get($employee_data, 'employee_grade_2', 0),
                    "boarding_employee_grade" => Arr::get($employee_data, 'boarding_employee_grade', 1),
                    "boarding_employee_grade_2" => Arr::get($employee_data, 'boarding_employee_grade_2', 1),
                    "transportation_compensation" => Arr::get($employee_data, 'transportation_compensation', 0),
                    "daily_transportation_cp" => Arr::get($employee_data, 'daily_transportation_cp', 0),
                    "midnight_worktime" => Arr::get($employee_data, 'midnight_worktime', 0),
                    "schedule_working_hours" => Arr::get($employee_data, 'schedule_working_hours', 0),
                    "temp_wage" => Arr::get($employee_data, 'temp_wage', 0),
                    "is_support" => 0,
                ]);
            }
        }
    }

    private function CalculateWelfareExpenses($monthly_salary, $yearOld)
    {
        //Monthly Salary (1)
        $monthly_salary1 = $monthly_salary * 100;

        //Monthly Salary (2)
        $monthly_salary2 = 0;
        $refer_monthly_salary = BaseSalary::query()
            ->where('min', '<=', $monthly_salary1)
            ->where('max', '>', $monthly_salary1)->first();
        if ($refer_monthly_salary) {
            $monthly_salary2 = $refer_monthly_salary->monthly_salary * 1000;
        }

        // Social insurance premium(1)
        $insurance_rates = InsuranceRate::query()->get();
        $health_insurance = 0;
        $chk_health_insurance = $insurance_rates->where('name', '健康保険料率')->first();
        if ($chk_health_insurance) {
            $health_insurance = $monthly_salary2 * $chk_health_insurance->current_rate;
        }
        $nenkin = 0;
        $chk_nenkin = $insurance_rates->where('name', '厚生年金料率')->first();
        if ($chk_nenkin) {
            if ($refer_monthly_salary && $refer_monthly_salary->monthly_salary <= 98) {
                $nenkin = (98 * 1000) * $chk_nenkin->current_rate;
            } elseif ($refer_monthly_salary && $refer_monthly_salary->monthly_salary >= 620) {
                $nenkin = (620 * 1000) * $chk_nenkin->current_rate;
            } else {
                $nenkin = $monthly_salary2 * $chk_nenkin->current_rate;
            }
        }
        $automatic_contribution = 0;
        $chk_automatic_contribution = $insurance_rates->where('name', '児童拠出料率')->first();
        if ($chk_automatic_contribution) {
            if ($refer_monthly_salary && $refer_monthly_salary->monthly_salary <= 98) {
                $automatic_contribution = (98 * 1000) * $chk_automatic_contribution->current_rate;
            } elseif ($refer_monthly_salary && $refer_monthly_salary->monthly_salary >= 620) {
                $automatic_contribution = (620 * 1000) * $chk_automatic_contribution->current_rate;
            } else {
                $automatic_contribution = $monthly_salary2 * $chk_automatic_contribution->current_rate;
            }
        }
        $long_term_care_insurance = 0;
        $chk_long_term_care_insurance = $insurance_rates->where('name', '介護保険料率')->first();
        if ($chk_long_term_care_insurance) {
            $long_term_care_insurance = $monthly_salary2 * $chk_long_term_care_insurance->current_rate;
        }

        //Social insurance premium(2)
        $social_insurance_premium2 = 0;
        if ($yearOld < 40) {
            $social_insurance_premium2 = $health_insurance + $nenkin + $automatic_contribution;
        }
        if ($yearOld >= 40) {
            $social_insurance_premium2 = $health_insurance + $nenkin + $automatic_contribution + $long_term_care_insurance;
        }

        //Labor insurance premium (1)
        $employment = 0;
        $chk_employment = $insurance_rates->where('name', '雇用料率')->first();
        if ($chk_employment && $refer_monthly_salary) {
            $employment = $refer_monthly_salary->max * $chk_employment->current_rate;
        }

        $industrial_accident = 0;
        $chk_industrial_accident = $insurance_rates->where('name', '労災料率')->first();
        if ($chk_industrial_accident && $refer_monthly_salary) {
            $industrial_accident = $refer_monthly_salary->max * $chk_industrial_accident->current_rate;
        }

        //Labor insurance premium (2)
        $labor_insurance_premium2 = $employment + $industrial_accident;

        //welfare expenses
        $welfare_expenses = $social_insurance_premium2 + $labor_insurance_premium2;
        return $welfare_expenses;
    }

    private function saveHistoryWelfareExpenses($employee_id, $welfare_expense)
    {
        if ($employee_id) {
            $chkWelfare = EmployeeWelfareExpenses::query()
                ->where('employee_id', $employee_id)
                ->orderBy('start_date', 'desc')
                ->first();
            if (!$chkWelfare || ($chkWelfare && $chkWelfare->welfare_expense !== $welfare_expense)) {
                EmployeeWelfareExpenses::query()->create([
                    'employee_id' => $employee_id,
                    'welfare_expense' => $welfare_expense,
                    'start_date' => Carbon::now()
                ]);
            }
        }
    }
}
