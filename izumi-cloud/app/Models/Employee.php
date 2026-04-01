<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Employee extends Model
{
    use HasFactory;

    CONST EMPLOYEE_CODE = 'employee_code';
    CONST NAME = 'name';
    CONST SEX = 'sex';
    CONST BIRTHDAY = 'birthday';
    CONST HIRE_START_DATE = 'hire_start_date';
    CONST RETIREMENT_DATE = 'retirement_date';
    CONST LICENSE_TYPE = 'license_type';
    CONST EMPLOYEE_TYPE = 'employee_type';
    CONST JOB_TYPE = 'job_type';
    CONST MIDNIGHT_WORKTIME = 'midnight_worktime';
    CONST SCHEDULE_WORKING_HOURS = 'schedule_working_hours';
    CONST GRADE = 'grade';
    CONST EMPLOYEE_GRADE_2 = 'employee_grade_2';
    CONST BOARDING_EMPLOYEE_GRADE = 'boarding_employee_grade';
    CONST BOARDING_EMPLOYEE_GRADE_2 = 'boarding_employee_grade_2';
    CONST TRANSPORTATION_COMPENSATION = 'transportation_compensation';
    CONST DAILY_TRANSPORTATION_CP = 'daily_transportation_cp';
    CONST WELFARE_EXPENSE = 'welfare_expense';
    CONST FIXED_SALARY = 'fixed_salary';
    CONST FINAL_DEPARTMENT_ID = 'final_department_id';
    CONST POSITION = 'position';
    CONST EMPLOYEE_ROLE = 'employee_role';
    
    protected $table = 'employees';

    protected $fillable = [
        'id',
        self::EMPLOYEE_CODE,
        self::NAME,
        self::SEX,
        self::BIRTHDAY,
        self::HIRE_START_DATE,
        self::RETIREMENT_DATE,
        self::LICENSE_TYPE,
        self::EMPLOYEE_TYPE,
        self::JOB_TYPE,
        self::MIDNIGHT_WORKTIME,
        self::SCHEDULE_WORKING_HOURS,
        self::GRADE,
        self::EMPLOYEE_GRADE_2,
        self::BOARDING_EMPLOYEE_GRADE,
        self::BOARDING_EMPLOYEE_GRADE_2,
        self::TRANSPORTATION_COMPENSATION,
        self::DAILY_TRANSPORTATION_CP,
        self::WELFARE_EXPENSE,
        self::FIXED_SALARY,
        self::FINAL_DEPARTMENT_ID,
        self::POSITION,
        self::FINAL_DEPARTMENT_ID,
        self::EMPLOYEE_ROLE,
        'company_car',
        'etc_card',
        'fuel_card',
        'other',
        'beginner_driver_training_classroom',
        'beginner_driver_training_practical',
        'driver_license_upload_file_flag',
        'driving_record_certificate_upload_file_flag',
        'health_examination_results_upload_file_flag',
        'aptitude_assessment_form_upload_file_flag',
        'name_in_furigana',
        'date_of_election',
        'address',
        'aptitude_test_date',
        'health_checkup_date',
        'previous_employment_history',
        'driver_license_information',
        'age_appropriate_interview',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'employee_department', 'employee_id', 'department_id')
            ->withPivot('start_date');
    }

    public function departmentWorkings()
    {
        return $this->belongsToMany(Department::class, 'employee_working_department', 'employee_id', 'department_id')
            ->withPivot('start_date', 'boarding_employee_grade', 'boarding_employee_grade_2', 'transportation_compensation', 'daily_transportation_cp', 'midnight_worktime', 'schedule_working_hours', 'is_support', 'temp_wage');
    }

    public function departmentWorkingIsSupport()
    {
        return $this->belongsToMany(Department::class, 'employee_working_department', 'employee_id', 'department_id')
            ->withPivot('start_date', 'end_date', 'is_support')->wherePivot('is_support', '=', 1);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'employee_course', 'employee_id', 'course_id');
    }

    public function employeeMobileInfo()
    {
        return $this->hasMany(EmployeeMobileInfo::class, 'employee_id', 'id');
    }

    public function employeeWelfareExpenses()
    {
        return $this->hasMany(EmployeeWelfareExpenses::class, 'employee_id', 'id');
    }

    public function employeeContent()
    {
        return $this->hasMany(EmployeeContent::class, 'employee_id', 'id');
    }

    public function driverLicenses()
    {
        return $this->hasMany(EmployeeDriverLicenses::class, 'employee_id', 'id');
    }

    public function drivingRecordCertificates()
    {
        return $this->hasMany(EmployeeDrivingRecordCertificates::class, 'employee_id', 'id');
    }

    public function aptitudeAssessmentForms()
    {
        return $this->hasMany(EmployeeAptitudeAssessmentForms::class, 'employee_id', 'id');
    }

    public function healthExaminationResults()
    {
        return $this->hasMany(EmployeeHealthExaminationResults::class, 'employee_id', 'id');
    }

    public function employeeLicense()
    {
        return $this->hasOne(EmployeeLicenses::class, 'employee_id', 'id');
    }

    public function userContacts()
    {
        return $this->belongsTo(UserContacts::class, 'employee_code', 'user_id');
    }

    public function employeePdfUploads()
    {
        return $this->hasMany(EmployeePdfUploads::class, 'employee_id', 'id');
    }
}
