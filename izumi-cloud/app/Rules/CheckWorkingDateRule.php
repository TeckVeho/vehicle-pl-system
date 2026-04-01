<?php

namespace App\Rules;

use App\Models\Employee;
use App\Models\Route;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckWorkingDateRule implements Rule
{
    protected $msgError = null;
    protected $employee_id;
    protected $department_working_id;
    protected $department_base_id = null;
    protected $is_support = 1;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($employee_id, $department_working_id)
    {
        $this->employee_id = $employee_id;
        $this->department_working_id = $department_working_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $employee = Employee::with('departments')->where('id', $this->employee_id)->first();
        $validator = Validator::make($value, [
            '*.start_date' => 'required|date_format:Y-m-d',
            '*.end_date' => 'nullable|date_format:Y-m-d',
            '*.is_support' => 'required',
        ]);
        if ($validator->fails()) {
            $this->msgError = $validator->messages();
            return false;
        }

        if ($value && count($value) > 0 && !empty($value)) {
            $workingDate = collect($value)->sortBy(['start_date', 'desc']);
            $checkDuplicateStartDate = $workingDate->duplicates('start_date');
            $checkDuplicateEnd = $workingDate->whereNull('end_date')->duplicates('end_date');
            $checkNullStart = collect($value)->whereNull('start_date');
            $checkNullEnd = collect($value)->whereNull('end_date')->where('is_support', 1);

            if ($checkNullStart && count($checkNullStart) > 0) {
                return false;
            }
            if ($checkNullEnd && count($checkNullEnd) > 0) {
                return false;
            }
            if ($checkDuplicateStartDate && count($checkDuplicateStartDate) > 0) {
                return false;
            }
            if ($checkDuplicateEnd && count($checkDuplicateEnd) > 0) {
                return false;
            }
            $workingDateValidates = collect($value)->whereNotNull('end_date')->sortBy(['start_date', 'desc']);

            if ($workingDateValidates && count($workingDateValidates) > 0) {
                foreach ($workingDateValidates as $workingDateValidate) {
                    $workingDateValidate = (object)$workingDateValidate;
                    $checkLessThanOrEq = Carbon::parse($workingDateValidate->start_date)->lte(Carbon::parse($workingDateValidate->end_date));
                    if (!$checkLessThanOrEq) {
                        return false;
                    }
                }
            }
            foreach ($workingDate as $wkDate) {
                $wkDate = (object)$wkDate;
                if (self::checkDate($wkDate->start_date) || self::checkDate($wkDate->end_date)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function checkDate($date)
    {
        if ($date) {
            $datacheck = DB::table('employee_working_department')
                ->where(function ($query) use ($date) {
                    $query->where('start_date', '<=', $date);
                    $query->where('end_date', '>=', $date);
                })
                ->where('employee_working_department.employee_id', (int)$this->employee_id)
                ->where('employee_working_department.department_id', '<>', (int)$this->department_working_id)
                ->first();
            if ($datacheck) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->msgError) {
            return $this->msgError;
        }
        return '勤務期間が自拠点または他拠点で既に利用されているため登録できません';
    }
}
