<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-08-24
 */

namespace App\Http\Requests;

use App\Rules\CheckWorkingDateRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return self::getCustomRule(Route::getCurrentRoute()->getActionMethod());
    }

    public function getCustomRule($action)
    {
        if ($action == 'index') {
            return [
                "month" => "required|date_format:Y-m"
            ];
        }
        if ($action == 'update') {
            $id = $this->route('employee');
            $department_working_id = $this->get('department_working_id');
            return [
                'working_date' => ['nullable', 'array', new CheckWorkingDateRule($id, $department_working_id)],
                'department_working_id' => 'required|exists:departments,id',
                'grade' => 'nullable|numeric|min:0',
                'employee_grade_2' => 'nullable|numeric|min:0',
                'boarding_employee_grade' => 'nullable|numeric|min:0',
                'boarding_employee_grade_2' => 'nullable|numeric|min:0',
                'transportation_compensation' => 'nullable|numeric|min:0',
                'daily_transportation_cp' => 'nullable|numeric|min:0',
                'midnight_worktime_hour' => 'nullable|numeric|min:0',
                'midnight_worktime_minutes' => 'nullable|numeric|min:0',
                'scheduled_labor_hour' => 'nullable|numeric|min:0',
                'scheduled_labor_minutes' => 'nullable|numeric|min:0',
                'employee_courses' => 'nullable|array',
                'employee_courses.*' => 'nullable|exists:courses,id',
                'beginner_driver_training_classroom' => 'nullable|numeric|min:0',
                'beginner_driver_training_practical' => 'nullable|numeric|min:0',
                'driver_training_upload_file_flag' => 'nullable|numeric|min:0',
                'age_appropriate_interview' => 'nullable|numeric|min:0',
            ];
        }
        if ($action == 'contentEmployee') {
            $id = $this->route('id');
            $this->merge(['id' => $id]);
            return [
                'company_car' => 'nullable|max:20',
                'etc_card' => 'nullable|max:20',
                'fuel_card' => 'nullable|max:20',
                'other' => 'nullable|max:200',
                'employee_role' => 'nullable|in:' . implode(',', EMPLOYEE_ROLE),
                'beginner_driver_training_classroom' => 'nullable',
                'beginner_driver_training_practical' => 'nullable',
                'age_appropriate_interview' => 'nullable',
            ];
        }
        
        if ($action == 'addDriverLicense') {
            return [
                'surface_file_id' => 'nullable|exists:files,id',
                'back_file_id' => 'nullable|exists:files,id',
                'employee_id' => 'required|exists:employees,id',
            ];
        }

        if ($action == 'addDrivingRecordCertificate') {
            return [
                'file_id' => 'required|exists:files,id',
                'employee_id' => 'required|exists:employees,id',
            ];
        }

        if ($action == 'addAptitudeAssessmentForm') {
            return [
                'employee_id' => 'required|exists:employees,id',
            ];
        }

        if ($action == 'addHealthExaminationResults') {
            return [
                'employee_id' => 'required|exists:employees,id',
            ];
        }
        if ($action == 'store') {
            return [

            ];
        }
        return [];
    }

    public function messages()
    {
        return [
            'regex' => ':attribute only numbers and alphabet',
            'numeric' => ':attributeは半角数字でのみ保存が可能です'
        ];
    }

    public function attributes()
    {
        return [
            "department_working_id" => '拠点',
            "grade" => "等級",
            "employee_grade_2" => "号棒",
            "boarding_employee_grade" => "同乗等級",
            "boarding_employee_grade_2" => "同乗号棒",
            "transportation_compensation" => "通勤手当",
            "daily_transportation_cp" => "通勤手当(1日単価)",
            "employee_courses" => "コース",
            "employee_courses.*" => "コース",
        ];
    }
}
