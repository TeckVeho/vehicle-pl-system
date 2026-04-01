<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-08-24
 */

namespace Repository;

use App\Jobs\SyncEmployeesJob;
use App\Models\Course;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeContent;
use App\Models\Route;
use App\Models\User;
use App\Models\EmployeeMobileInfo;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeDriverLicenses;
use App\Models\EmployeeDrivingRecordCertificates;
use App\Models\EmployeeAptitudeAssessmentForms;
use App\Models\EmployeeHealthExaminationResults;
use App\Models\AptitudeAssessmentFormsFileHistory;
use App\Models\HealthExaminationResultsFileHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\EmployeePdfUploads;
use App\Jobs\SaveFileToS3Job;
use App\Models\EmployeeDriverLicensesHistory;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param Employee $model
     */

    public function model()
    {
        return Employee::class;
    }


    public function index($request)
    {
        $listRoleName = auth()->user()->getRoleNames()->toArray();

        $month = $request->get('month');
        $page = (int)$request->get('page', 1);
        $perPage = (int)$request->get('per_page', 20);
        $firstMonth = Carbon::parse($month . '-01')->firstOfMonth()->format('Y-m-d');
        $endMonth = Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d');
        $f_working_base_id = $request->get('working_base_id');

        $f_department_base_id = $request->get('department_base_id');
        if (in_array('tl', $listRoleName) || in_array('clerks', $listRoleName)) {
            $user = auth()->user()->load(['department']);
            if ($user->department) {
                $f_department_base_id = $user->department->id;
            }
        }

        $sortby = $request->get('sort_by') ? $request->get('sort_by') : 'employee_id';
        if ($sortby && $sortby == 'department_base') {
            $sortby = 'department_base';
        }
        if ($sortby && $sortby == 'working_base') {
            $sortby = 'working_base';
        }
        if ($sortby && $sortby == 'employee_id') {
            $sortby = 'employee_code';
        }
        if ($sortby && $sortby == 'employee_name') {
            $sortby = 'name';
        }
        if ($sortby && $sortby == 'retirement_date') {
            $sortby = 'retirement_date';
        }

        $sorttype = $request->boolean('sort_type') ? 'desc' : 'asc';

        $latestDepartment = DB::table('employee_department as latestDepartment')
            ->select('latestDepartment.employee_id')
            ->selectRaw('MAX(latestDepartment.start_date) as last_start_date')
            ->where('latestDepartment.start_date', '<=', $endMonth)
            ->groupBy('latestDepartment.employee_id');

        $latestEmployeeDepartment = DB::table('employee_department as latestEmployeeDepartment')
            ->select('latestEmployeeDepartment.employee_id', 'latestEmployeeDepartment.start_date',
                'latestEmployeeDepartment.department_id', 'departments.name as department_base',
                'departments.position as department_position')
            ->joinSub($latestDepartment, 'latestDepartment', function ($join) {
                $join->on('latestDepartment.employee_id', '=', 'latestEmployeeDepartment.employee_id')
                    ->on('latestDepartment.last_start_date', '=', 'latestEmployeeDepartment.start_date');
            })->leftJoin('departments', function ($join) {
                $join->on('departments.id', '=', 'latestEmployeeDepartment.department_id');
                $join->whereRaw('departments.deleted_at is null');
            })
            ->where('latestEmployeeDepartment.start_date', '<=', $endMonth);

        $latestDepartmentWorking = DB::table('employee_working_department as latestDepartmentWorking')
            ->select('latestDepartmentWorking.employee_id')
            ->selectRaw('MAX(latestDepartmentWorking.start_date) as last_start_date_working')
            ->where('latestDepartmentWorking.start_date', '<=', $endMonth)
            ->orWhere('latestDepartmentWorking.end_date', '<=', $endMonth)
            ->groupBy('latestDepartmentWorking.employee_id');

        $latestEmployeeDepartmentWorking = DB::table('employee_working_department as latestEmployeeDepartmentWorking')
            ->select('latestDepartmentWorking.employee_id', 'latestEmployeeDepartmentWorking.start_date',
                'latestEmployeeDepartmentWorking.department_id', 'departments.name as working_base',
                'departments.position as working_department_position')
            ->joinSub($latestDepartmentWorking, 'latestDepartmentWorking', function ($join) {
                $join->on('latestEmployeeDepartmentWorking.employee_id', '=', 'latestDepartmentWorking.employee_id')
                    ->on('latestEmployeeDepartmentWorking.start_date', '=', 'latestDepartmentWorking.last_start_date_working');
            })->leftJoin('departments', function ($join) {
                $join->on('departments.id', '=', 'latestEmployeeDepartmentWorking.department_id');
                $join->whereRaw('departments.deleted_at is null');
            })
            ->where('latestEmployeeDepartmentWorking.start_date', '<=', $endMonth)
            ->orWhere('latestEmployeeDepartmentWorking.end_date', '<=', $endMonth);

        $employees = Employee::with(['departments:id,name', 'departmentWorkingIsSupport:id,name', 'employeeLicense'])
            ->select('employees.id', 'employees.name', 'employees.retirement_date', 'employees.employee_code',
                'latestEmployeeDepartment.department_base', 'latestEmployeeDepartment.department_base as working_base',
                'latestEmployeeDepartment.department_id as department_base_id',
                'latestEmployeeDepartment.department_id as working_base_id',
                'latestEmployeeDepartment.department_position',
                'latestEmployeeDepartmentWorking.working_department_position',
                'employees.beginner_driver_training_classroom',
                'employees.beginner_driver_training_practical',
                'employees.driver_license_upload_file_flag',
                'employees.driving_record_certificate_upload_file_flag',
                'employees.health_examination_results_upload_file_flag',
                'employees.aptitude_assessment_form_upload_file_flag')
            ->leftJoinSub($latestEmployeeDepartment, 'latestEmployeeDepartment', function ($join) use ($endMonth) {
                $join->on('latestEmployeeDepartment.employee_id', '=', 'employees.id');
            })
            ->leftJoinSub($latestEmployeeDepartmentWorking, 'latestEmployeeDepartmentWorking', function ($join) use ($endMonth) {
                $join->on('latestEmployeeDepartmentWorking.employee_id', '=', 'employees.id');
            })
            ->when($request->get('employee_id'), function ($query) use ($request) {
                return $query->where('employees.employee_code', $request->get('employee_id'));
            })
            ->when($request->get('employee_name'), function ($query) use ($request) {
                return $query->whereRaw("REPLACE(employees.name, '/', '') like '%{$request->get('employee_name')}%'");
            })
            ->where(function ($query) use ($endMonth, $firstMonth) {
                $query->whereDate('employees.retirement_date', '>=', $firstMonth);
                $query->orWhereNull('employees.retirement_date');
            })
            ->when($f_department_base_id, function ($query) use ($f_department_base_id) {
                return $query->where('latestEmployeeDepartment.department_id', $f_department_base_id);
            })
            ->when($f_working_base_id, function ($query) use ($f_working_base_id) {
                return $query->where('latestEmployeeDepartmentWorking.department_id', $f_working_base_id);
            })
            ->whereNotNull('latestEmployeeDepartment.department_id');

        if ($sortby == 'department_base') {
            $employees = $employees->orderBy('latestEmployeeDepartment.department_position', $sorttype)
                ->orderBy('latestEmployeeDepartment.department_base', $sorttype);
        } elseif ($sortby == 'working_base') {
            $employees = $employees->orderBy('latestEmployeeDepartmentWorking.working_department_position', $sorttype)
                ->orderBy('latestEmployeeDepartmentWorking.working_base', $sorttype);
        } else {
            $employees = $employees->orderBy($sortby, $sorttype);
        }

        $employees = $employees->groupby('employees.id')
            ->paginate($perPage);
        foreach ($employees as $key => $employee) {
            $retirementDate = $employee->retirement_date;
            if($retirementDate && Carbon::parse($retirementDate)->diffInYears(Carbon::now()) >= 3) {
                $employee->driver_license_upload_file_flag = 0;
                $employee->driving_record_certificate_upload_file_flag = 0;
                $employee->health_examination_results_upload_file_flag = 0;
                $employee->aptitude_assessment_form_upload_file_flag = 0;
            }
            self::mapChangeBaseInMonth($firstMonth, $endMonth, $employee);
            self::mapChangeWorkingBaseInMonth($firstMonth, $endMonth, $employee);
        }

        return $employees;
    }

    private function mapChangeBaseInMonth($firstMonth, $endMonth, &$employee)
    {
        $departmentsBeforeMonth = $employee->departments->where('pivot.start_date', '<', $firstMonth)
            ->sortBy([['pivot.start_date', 'desc']])->first();
        if ($departmentsBeforeMonth) {
            $employee->department_base = $departmentsBeforeMonth->name;
            $employee->department_base_id = $departmentsBeforeMonth->id;
        } else {
            $employee->department_base = null;
        }

        $departmentsName = $employee->departments->whereBetween('pivot.start_date', [$firstMonth, $endMonth])
            ->sortBy([['pivot.start_date', 'asc']])->pluck('name', 'id')->toArray();

        if ($departmentsName) {
            $departmentsNameArr = array_values($departmentsName);
            $departmentsKey = array_keys($departmentsName);
            if ($employee->department_base) {
                array_unshift($departmentsNameArr, $employee->department_base);
            }
            array_unshift($departmentsKey, $employee->department_base_id);
            $departmentsNameArr = array_unique($departmentsNameArr);
            $departmentsKey = array_unique($departmentsKey);
            $employee->department_base = implode(' ==> ', $departmentsNameArr);
            $employee->department_base_id = $departmentsKey;
        } else {
            $employee->department_base_id = [$employee->department_base_id];
        }
    }


    private function mapChangeWorkingBaseInMonth($firstMonth, $endMonth, &$employee)
    {
        $departmentsName = $employee->departmentWorkingIsSupport->filter(function ($item) use ($firstMonth, $endMonth) {
            return Carbon::parse($firstMonth)->between($item->pivot->start_date, $item->pivot->end_date) ||
                Carbon::parse($endMonth)->between($item->pivot->start_date, $item->pivot->end_date) ||
                Carbon::parse($item->pivot->start_date)->between($firstMonth, $endMonth) ||
                Carbon::parse($item->pivot->end_date)->between($firstMonth, $endMonth);
        })->sortBy([['pivot.start_date', 'asc']])
            ->pluck('name', 'id')
            ->toArray();
        if (count($departmentsName)) {
            $departmentsNameArr = array_values($departmentsName);
            $departmentsKey = array_keys($departmentsName);
            array_unshift($departmentsKey, $employee->working_base_id);
            if ($employee->working_base) {
                array_push($departmentsNameArr, $employee->working_base);
            }
            $departmentsNameArr = array_unique($departmentsNameArr);
            $employee->working_base = implode(', ', $departmentsNameArr);
            $employee->working_base_id = $departmentsKey;
        } else {
            $employee->working_base_id = [$employee->working_base_id];
        }
    }


    public function detail($id, $request)
    {
        $listRoleName = auth()->user()->getRoleNames()->toArray();
        $f_department_base_id = null;
        if (in_array('tl', $listRoleName) || in_array('clerks', $listRoleName)) {
            $user = auth()->user()->load(['department']);
            if ($user->department) {
                $f_department_base_id = $user->department->id;
            }
        }

        $departmentsWorkings = [];
        $employee = null;
        $department_history = [];
        $employees = Employee::with(['departmentWorkings',
            "employeeMobileInfo" => function ($query) {
                $query->orderBy('created_at', 'DESC');
            },
            "employeeWelfareExpenses" => function ($query) {
                $query->orderBy('start_date', 'DESC');
            },
            "employeeContent" => function ($query) {
                $query->orderBy('created_at', 'DESC');
            },
            "driverLicenses" => function ($query) {
                $query->orderBy('id', 'DESC');
            },
            "drivingRecordCertificates" => function ($query) {
                $query->orderBy('id', 'DESC');
            },
            "aptitudeAssessmentForms" => function ($query) {
                $query->orderBy('id', 'DESC');
            },
            "healthExaminationResults" => function ($query) {
                $query->orderBy('id', 'DESC');
            },
            "driverLicenses.surface_file",
            "driverLicenses.back_file",
            "drivingRecordCertificates.file",
            "driverLicenses.employeeDriverLicensesHistory" => function ($query) {
                $query->orderBy('created_at', 'DESC');
            },
            "driverLicenses.employeeDriverLicensesHistory.surface_file",
            "driverLicenses.employeeDriverLicensesHistory.back_file",
            "driverLicenses.employeeDriverLicensesHistory.user",
            "drivingRecordCertificates.user",
            "healthExaminationResults.fileHistory" => function ($query) {
                $query->select("*")->orderBy('created_at', 'DESC');
            },
            "aptitudeAssessmentForms.fileHistory" => function ($query) {
                $query->select('*')->orderBy('created_at', 'DESC');
            },
            "aptitudeAssessmentForms.fileHistory.file" => function ($query) {
                $query->select('*')->orderBy('created_at', 'DESC');
            },
            "healthExaminationResults.fileHistory.file" => function ($query) {
                $query->select('*')->orderBy('created_at', 'DESC');
            },
            "healthExaminationResults.fileHistory.user" => function ($query) {
                $query->select('id', 'name');
            },
            "aptitudeAssessmentForms.fileHistory.user" => function ($query) {
                $query->select('id', 'name');
            },
            "employeeLicense",
            "userContacts" => function ($query) {
                $query->select('user_id', 'personal_tel');
            },
            "employeePdfUploads" => function ($query) {
                $query->orderBy('created_at', 'DESC');
            },
            "employeePdfUploads.file",
            "employeePdfUploads.user" => function ($query) {
                $query->select('id', 'name');
            },
        ])->select('employees.*', 'departments.name as department_base',
            'departments.name as working_base', 'departments.id as department_base_id', 'employee_department.start_date', 'users.email', 'users.role')
            ->join('employee_department', function ($join) {
                $join->on('employee_department.employee_id', '=', 'employees.id');
            })->leftJoin('departments', function ($join) {
                $join->on('departments.id', '=', 'employee_department.department_id');
            })->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'employees.employee_code');
            })->where('employees.id', $id)
            ->when($f_department_base_id, function ($query) use ($f_department_base_id) {
                return $query->where('departments.id', $f_department_base_id);
            })
            ->orderby('employee_department.start_date', 'asc')->get();
        foreach ($employees as $value) {
            $employee = $value;
            $department_history[] = [
                'start_date' => $value->start_date,
                'department_name' => $value->department_base,
            ];
        }
        if ($employee) {
            $departmentsWorkings = Department::query()->select('id', 'name')
                ->where('id', '<>', $employee->department_base_id)
                ->orderBy('position', 'ASC')
                ->get();
            foreach ($departmentsWorkings as $departmentsWorking) {
                $employeesWorkings = $employee->departmentWorkings()->where('id', $departmentsWorking->id)->get();
                if ($employeesWorkings->count() > 0) {
                    $departmentsWorking->color = 'yellow';
                } else {
                    $departmentsWorking->color = 'gray';
                }
            }
            if ($departmentsWorkings->count() > 0) {
                $departmentsWorkings = $departmentsWorkings->toArray();
                $department_base = [
                    'id' => $employee->department_base_id,
                    'name' => $employee->department_base,
                    'color' => 'orange'
                ];
                array_unshift($departmentsWorkings, $department_base);
            }
            $retirementDate = $employee->retirement_date;
            if($retirementDate && Carbon::parse($retirementDate)->diffInYears(Carbon::now()) >= 3) {
                unset($employee['driverLicenses']);
                unset($employee['drivingRecordCertificates']);
                unset($employee['aptitudeAssessmentForms']);
                unset($employee['healthExaminationResults']);
                $employee->driver_licenses = [];
                $employee->driving_record_certificates = [];
                $employee->aptitude_assessment_forms = [];
                $employee->health_examination_results = [];
                $employee->driver_license_upload_file_flag = 0;
                $employee->driving_record_certificate_upload_file_flag = 0;
                $employee->health_examination_results_upload_file_flag = 0;
                $employee->aptitude_assessment_form_upload_file_flag = 0;
            }
            $employee = $employee->toArray();
            unset($employee['department_workings']);
        }

        return ['employee' => $employee, 'department_workings' => $departmentsWorkings, 'department_history' => $department_history];
    }

    public function getAllForExport($request = null)
    {
        $listRoleName = auth()->user()->getRoleNames()->toArray();

        $month = $request ? $request->get('month') : null;
        if (!$month) {
            $month = date('Y-m');
        }
        $firstMonth = Carbon::parse($month . '-01')->firstOfMonth()->format('Y-m-d');
        $endMonth = Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d');
        $f_working_base_id = $request ? $request->get('working_base_id') : null;

        $f_department_base_id = $request ? $request->get('department_base_id') : null;
        if (in_array('tl', $listRoleName) || in_array('clerks', $listRoleName)) {
            $user = auth()->user()->load(['department']);
            if ($user->department) {
                $f_department_base_id = $user->department->id;
            }
        }

        $latestDepartment = DB::table('employee_department as latestDepartment')
            ->select('latestDepartment.employee_id')
            ->selectRaw('MAX(latestDepartment.start_date) as last_start_date')
            ->where('latestDepartment.start_date', '<=', $endMonth)
            ->groupBy('latestDepartment.employee_id');

        $latestEmployeeDepartment = DB::table('employee_department as latestEmployeeDepartment')
            ->select('latestEmployeeDepartment.employee_id', 'latestEmployeeDepartment.start_date',
                'latestEmployeeDepartment.department_id', 'departments.name as department_base',
                'departments.position as department_position')
            ->joinSub($latestDepartment, 'latestDepartment', function ($join) {
                $join->on('latestDepartment.employee_id', '=', 'latestEmployeeDepartment.employee_id')
                    ->on('latestDepartment.last_start_date', '=', 'latestEmployeeDepartment.start_date');
            })->leftJoin('departments', function ($join) {
                $join->on('departments.id', '=', 'latestEmployeeDepartment.department_id');
                $join->whereRaw('departments.deleted_at is null');
            })
            ->where('latestEmployeeDepartment.start_date', '<=', $endMonth);

        $latestDepartmentWorking = DB::table('employee_working_department as latestDepartmentWorking')
            ->select('latestDepartmentWorking.employee_id')
            ->selectRaw('MAX(latestDepartmentWorking.start_date) as last_start_date_working')
            ->where('latestDepartmentWorking.start_date', '<=', $endMonth)
            ->orWhere('latestDepartmentWorking.end_date', '<=', $endMonth)
            ->groupBy('latestDepartmentWorking.employee_id');

        $latestEmployeeDepartmentWorking = DB::table('employee_working_department as latestEmployeeDepartmentWorking')
            ->select('latestDepartmentWorking.employee_id', 'latestEmployeeDepartmentWorking.start_date',
                'latestEmployeeDepartmentWorking.department_id', 'departments.name as working_base',
                'departments.position as working_department_position')
            ->joinSub($latestDepartmentWorking, 'latestDepartmentWorking', function ($join) {
                $join->on('latestEmployeeDepartmentWorking.employee_id', '=', 'latestDepartmentWorking.employee_id')
                    ->on('latestEmployeeDepartmentWorking.start_date', '=', 'latestDepartmentWorking.last_start_date_working');
            })->leftJoin('departments', function ($join) {
                $join->on('departments.id', '=', 'latestEmployeeDepartmentWorking.department_id');
                $join->whereRaw('departments.deleted_at is null');
            })
            ->where('latestEmployeeDepartmentWorking.start_date', '<=', $endMonth)
            ->orWhere('latestEmployeeDepartmentWorking.end_date', '<=', $endMonth);

        $query = Employee::with([
            "aptitudeAssessmentForms" => function ($query) {
                $query->orderBy('id', 'DESC');
            },
            "aptitudeAssessmentForms.fileHistory" => function ($query) {
                $query->orderBy('id', 'DESC');
            },
            "healthExaminationResults" => function ($query) {
                $query->orderBy('id', 'DESC');
            },
            "employeeLicense",
            "userContacts" => function ($query) {
                $query->select('user_id', 'personal_tel');
            },
        ])
            ->leftJoinSub($latestEmployeeDepartment, 'latestEmployeeDepartment', function ($join) use ($endMonth) {
                $join->on('latestEmployeeDepartment.employee_id', '=', 'employees.id');
            })
            ->leftJoinSub($latestEmployeeDepartmentWorking, 'latestEmployeeDepartmentWorking', function ($join) use ($endMonth) {
                $join->on('latestEmployeeDepartmentWorking.employee_id', '=', 'employees.id');
            })
            ->when($request && $request->get('employee_id'), function ($query) use ($request) {
                return $query->where('employees.employee_code', $request->get('employee_id'));
            })
            ->when($request && $request->get('employee_name'), function ($query) use ($request) {
                return $query->whereRaw("REPLACE(employees.name, '/', '') like '%{$request->get('employee_name')}%'");
            })
            ->where(function ($query) use ($endMonth, $firstMonth) {
                $query->whereDate('employees.retirement_date', '>=', $firstMonth);
                $query->orWhereNull('employees.retirement_date');
            })
            ->when($f_department_base_id, function ($query) use ($f_department_base_id) {
                return $query->where('latestEmployeeDepartment.department_id', $f_department_base_id);
            })
            ->when($f_working_base_id, function ($query) use ($f_working_base_id) {
                return $query->where('latestEmployeeDepartmentWorking.department_id', $f_working_base_id);
            })
            ->whereNotNull('latestEmployeeDepartment.department_id')
            ->groupby('employees.id');

        $employees = $query->get();

        $result = [];
        foreach ($employees as $employee) {
            $aptitudeAssessmentForms = '';
            if($employee->aptitudeAssessmentForms && count($employee->aptitudeAssessmentForms) > 0) {
                $aptitudeAssessmentForms = $this->handleAptitudeAssessmentForms($employee->aptitudeAssessmentForms[0]->fileHistory->toArray());
            }
            $employee->aptitude_assessment_forms_value = $aptitudeAssessmentForms;
            $employeeData = $employee->toArray();
            $result[] = $employeeData;
        }

        return $result;
    }

    public function handleAptitudeAssessmentForms($data)
    {
        $result = '';
        $seenTypes = [];
        foreach ($data as $item) {
            $type = $item['type'] ?? null;
            if ($type !== null && !in_array($type, $seenTypes)) {
                $typeText = '';
                if($type == 1) {
                    $typeText = '初任';
                } else if($type == 2) {
                    $typeText = '適齢';
                } else if($type == 3) {
                    $typeText = '特定';
                } else if($type == 4) {
                    $typeText = '一般';
                }
                $seenTypes[] = $type;
                $result .= $typeText . ':' . $item['date_of_visit'] . ',';
            }
        }
        return rtrim($result, ',');
    }

    public function departmentWorking($employee_id, $department_id)
    {
        $employeeWorking = null;
        $departmentDates = null;
        $employeeCourse = null;
        if ($employee_id && $department_id) {
            $departmentsWorkings = DB::table('employee_working_department')
                ->select('employee_working_department.*', 'departments.id', 'departments.name')
                ->leftJoin('departments', function ($join) {
                    $join->on('employee_working_department.department_id', '=', 'departments.id');
                })
//                ->where('employee_working_department.is_support', $isSupport)
                ->where('employee_working_department.department_id', $department_id)
                ->where('employee_working_department.employee_id', $employee_id)
                ->orderby('employee_working_department.start_date', 'asc')->get();

            if ($departmentsWorkings->count() > 0) {
                foreach ($departmentsWorkings as $value) {
                    $employeeWorking = $value;
                    $departmentDates[] = [
                        'start_date' => $value->start_date,
                        'end_date' => $value->end_date,
                        'is_support' => $value->is_support,
                    ];
                    $employeeWorking->midnight_worktime_hour = (int)$employeeWorking->midnight_worktime;
                    $employeeWorking->midnight_worktime_minutes = (int)(($employeeWorking->midnight_worktime - (int)$employeeWorking->midnight_worktime) * 60 + 0.1);
                    $employeeWorking->scheduled_labor_hour = (int)$employeeWorking->schedule_working_hours;
                    $employeeWorking->scheduled_labor_minutes = (int)(($employeeWorking->schedule_working_hours - (int)$employeeWorking->schedule_working_hours) * 60 + 0.1);


                }
            }
            if ($employeeWorking) {
                $employeeCourse = DB::table('employee_course')
                    ->select('courses.id', 'courses.course_code')
                    ->join('courses', function ($join) {
                        $join->on('employee_course.course_id', '=', 'courses.id');
                    })
//                    ->where('employee_course.is_support', $isSupport)
                    ->where('employee_course.department_id', $department_id)
                    ->where('employee_course.employee_id', $employee_id)->get();
            }
        }
        return ['employee_data' => $employeeWorking, 'employee_working_departments' => $departmentDates, 'employee_courses' => $employeeCourse];
    }

    public function updateEmployeeDpWorking($employee_id, $attributes)
    {
        $is_support = 1;
        $department_working_id = Arr::get($attributes, 'department_working_id');
        $employee = Employee::with(['departments', 'departmentWorkings', 'courses'])->where('id', $employee_id)->first();
        $employee->beginner_driver_training_classroom = Arr::get($attributes, 'beginner_driver_training_classroom');
        $employee->beginner_driver_training_practical = Arr::get($attributes, 'beginner_driver_training_practical');
        $employee->save();
        $departmentBase = $employee->departments()->orderByPivot('start_date', 'desc')->first();
        if ($departmentBase) {
            if ((int)$departmentBase->id == (int)$department_working_id) {
                $is_support = 0;
            }
        }

        $employee->departmentWorkings()->wherePivot('department_id', $department_working_id)->detach();

        $working_dates = Arr::get($attributes, 'working_date');
        $employee_courses = Arr::get($attributes, 'employee_courses');
        if ($working_dates && !empty($working_dates)) {
            foreach ($working_dates as $working_date) {
                $employee->departmentWorkings()->attach($department_working_id, [
                    "start_date" => Arr::get($working_date, 'start_date'),
                    "end_date" => Arr::get($working_date, 'end_date'),
                    "grade" => Arr::get($attributes, 'grade'),
                    "employee_grade_2" => Arr::get($attributes, 'employee_grade_2'),
                    "boarding_employee_grade" => Arr::get($attributes, 'boarding_employee_grade'),
                    "boarding_employee_grade_2" => Arr::get($attributes, 'boarding_employee_grade_2'),
                    "transportation_compensation" => Arr::get($attributes, 'transportation_compensation'),
                    "daily_transportation_cp" => Arr::get($attributes, 'daily_transportation_cp'),
                    "midnight_worktime" => Arr::get($attributes, 'midnight_worktime_hour') + (Arr::get($attributes, 'midnight_worktime_minutes') / 60),
                    "schedule_working_hours" => Arr::get($attributes, 'scheduled_labor_hour') + (Arr::get($attributes, 'scheduled_labor_minutes') / 60),
                    "temp_wage" => Arr::get($attributes, "temp_wage", null),
                    "is_support" => Arr::get($working_date, 'is_support', 1),
                ]);
            }

            $employee->courses()->wherePivot('department_id', $department_working_id)->detach();

            if ($employee_courses && !empty($employee_courses)) {
                foreach ($employee_courses as $key => $course) {
                    $employee->courses()->attach($course, [
                        "department_id" => $department_working_id,
                        "is_support" => $is_support,
                        "position" => $key + 1,
                    ]);
                }
            }
        }
    }

    public function addContentEmployee($attributes, $employee_id)
    {
        $employee = Employee::query()->findOrFail($employee_id);
        $fileId = Arr::get($attributes, 'file_id');
        $user = Auth::user();
        if ($employee) {
            $employee->company_car = Arr::get($attributes, 'company_car');
            $employee->etc_card = Arr::get($attributes, 'etc_card');
            $employee->fuel_card = Arr::get($attributes, 'fuel_card');
            $employee->other = Arr::get($attributes, 'other');
            $employee->employee_role = Arr::get($attributes, 'employee_role');
            $employee->beginner_driver_training_classroom = Arr::get($attributes, 'beginner_driver_training_classroom');
            $employee->beginner_driver_training_practical = Arr::get($attributes, 'beginner_driver_training_practical');
            $employee->date_of_election = Arr::get($attributes, 'date_of_election');
            $employee->previous_employment_history = Arr::get($attributes, 'previous_employment_history');
            $employee->age_appropriate_interview = Arr::get($attributes, 'age_appropriate_interview');
            $employee->save();
            if ($fileId) {
                EmployeePdfUploads::query()->create([
                    'employee_id' => $employee_id,
                    'file_id' => $fileId,
                    'user_id' => $user->id,
                ]);
            }
            //sysn employ update
            $attributes['id'] = $employee_id;
            SyncEmployeesJob::dispatch($employee_id);
            if ($employee->wasChanged(['company_car', 'etc_card', 'fuel_card', 'other'])) {
                EmployeeContent::query()->create(
                    [
                        'employee_id' => $employee_id,
                        'company_car' => Arr::get($attributes, 'company_car'),
                        'etc_card' => Arr::get($attributes, 'etc_card'),
                        'fuel_card' => Arr::get($attributes, 'etc_card'),
                        'other' => Arr::get($attributes, 'etc_card'),
                    ]
                );
            }
        }
        return $employee;
    }

    public function uploadEmployeePdf($attributes)
    {
        $employee = Employee::query()->findOrFail($attributes['employee_id']);
        $fileId = Arr::get($attributes, 'file_id');
        $file = File::query()->findOrFail($fileId);
        $user = Auth::user();
        if ($employee && $fileId && $file) {
            $employeePdfUpload = EmployeePdfUploads::query()->create([
                'employee_id' => $attributes['employee_id'],
                'file_id' => $attributes['file_id'],
                'user_id' => $user->id,
            ]);
            return $employeePdfUpload;
        }
        return null;
    }

    public function deleteEmployeePdf($id)
    {
        $employeePdfUpload = EmployeePdfUploads::query()->findOrFail($id);
        if ($employeePdfUpload) {
            $employeePdfUpload->delete();
        }
        return $employeePdfUpload;
    }

    public function listCourseByDepartment($department_id)
    {
        $data = Course::query()
            ->select('id', 'course_code')
            ->where('department_id', $department_id)->get();
        return $data;
    }

    public function addDriverLicense($attributes)
    {
        try {
            DB::beginTransaction();
            $employee = Employee::query()->findOrFail($attributes['employee_id']);
            $user = Auth::user();
            if ($employee) {
                if($employee->retirement_date) {
                    $pastTime = Carbon::parse($employee->retirement_date);
                    if ($pastTime->lt(Carbon::now()->subYears(3))) {
                        return null;
                    }
                }
                $employeeDriverLicenseFirst = EmployeeDriverLicenses::query()
                    ->where('employee_id', $attributes['employee_id'])
                    ->first();
                if (!$employeeDriverLicenseFirst) {
                    $employeeDriverLicenseFirst = EmployeeDriverLicenses::query()->create([
                        'employee_id' => $attributes['employee_id'],
                        'user_id' => $user->id,
                        'surface_file_id' => $attributes['surface_file_id'],
                        'back_file_id' => $attributes['back_file_id'],
                    ]);
                    if ($employeeDriverLicenseFirst) {
                        EmployeeDriverLicensesHistory::query()->create([
                            'employee_driver_licenses_id' => $employeeDriverLicenseFirst->id,
                            'user_id' => $user->id,
                            'surface_file_id' => $attributes['surface_file_id'],
                            'back_file_id' => $attributes['back_file_id'],
                        ]);
                    }
                    $employee->driver_license_upload_file_flag = 1;
                    $employee->save();

                    $employeePdfUploadBack = EmployeePdfUploads::query()
                        ->where('employee_id', $attributes['employee_id'])
                        ->where('file_id', $attributes['back_file_id'])->first();
                    if ($employeePdfUploadBack) {
                        $employeePdfUploadBack->delete();
                    }
                    $employeePdfUploadSurface = EmployeePdfUploads::query()
                        ->where('employee_id', $attributes['employee_id'])
                        ->where('file_id', $attributes['surface_file_id'])->first();
                    if ($employeePdfUploadSurface) {
                        $employeePdfUploadSurface->delete();
                    }

                } else {
                    if ($attributes['surface_file_id'] && $attributes['back_file_id']) {
                        $employeeDriverLicenseFirst->surface_file_id = $attributes['surface_file_id'];
                        $employeeDriverLicenseFirst->back_file_id = $attributes['back_file_id'];
                        $employeeDriverLicenseFirst->save();

                        EmployeeDriverLicensesHistory::query()->create([
                            'employee_driver_licenses_id' => $employeeDriverLicenseFirst->id,
                            'user_id' => $user->id,
                            'surface_file_id' => $attributes['surface_file_id'],
                            'back_file_id' => $attributes['back_file_id'],
                        ]);

                        $employeePdfUpload = EmployeePdfUploads::query()
                            ->where('employee_id', $attributes['employee_id'])
                            ->whereIn('file_id', [$attributes['surface_file_id'], $attributes['back_file_id']])->get();
                        if ($employeePdfUpload->count() > 0) {
                            foreach ($employeePdfUpload as $item) {
                                $item->delete();
                            }
                        }
                        SaveFileToS3Job::dispatch($attributes['surface_file_id'], 'employee')->delay(now()->addMinute());
                        SaveFileToS3Job::dispatch($attributes['back_file_id'], 'employee')->delay(now()->addMinute());
                    } else if ($attributes['surface_file_id'] && $attributes['back_file_id'] == null) {
                        $employeeDriverLicenseFirst->surface_file_id = $attributes['surface_file_id'];
                        $employeeDriverLicenseFirst->save();
                        $employeePdfUpload = EmployeePdfUploads::query()
                            ->where('employee_id', $attributes['employee_id'])
                            ->where('file_id', $attributes['surface_file_id'])->first();
                        if ($employeePdfUpload) {
                            $employeePdfUpload->delete();
                        }
                        EmployeeDriverLicensesHistory::query()->create([
                            'employee_driver_licenses_id' => $employeeDriverLicenseFirst->id,
                            'user_id' => $user->id,
                            'surface_file_id' => $attributes['surface_file_id'],
                        ]);
                        SaveFileToS3Job::dispatch($attributes['surface_file_id'], 'employee')->delay(now()->addMinute());
                    } else if ($attributes['surface_file_id'] == null && $attributes['back_file_id']) {
                        $employeeDriverLicenseFirst->back_file_id = $attributes['back_file_id'];
                        $employeeDriverLicenseFirst->save();
                        $employeePdfUpload = EmployeePdfUploads::query()
                            ->where('employee_id', $attributes['employee_id'])
                            ->where('file_id', $attributes['back_file_id'])->first();
                        if ($employeePdfUpload) {
                            $employeePdfUpload->delete();
                        }
                        EmployeeDriverLicensesHistory::query()->create([
                            'employee_driver_licenses_id' => $employeeDriverLicenseFirst->id,
                            'user_id' => $user->id,
                            'back_file_id' => $attributes['back_file_id'],
                        ]);
                        SaveFileToS3Job::dispatch($attributes['back_file_id'], 'employee')->delay(now()->addMinute());
                    }
                }
                DB::commit();
                return $employeeDriverLicenseFirst;
            }
            return null;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('addDriverLicense: ' . $e->getMessage());
            return  $e->getMessage();
        }
        
    }

    public function addDrivingRecordCertificate($attributes)
    {
        $employee = Employee::query()->findOrFail($attributes['employee_id']);
        $user = Auth::user();
        if ($employee) {
            if($employee->retirement_date) {
                $pastTime = Carbon::parse($employee->retirement_date);
                if ($pastTime->lt(Carbon::now()->subYears(3))) {
                    return null;
                }
            }
            $employeeDrivingRecordCertificate = EmployeeDrivingRecordCertificates::query()->create([
                'employee_id' => $attributes['employee_id'],
                'user_id' => $user->id,
                'file_id' => $attributes['file_id'],
            ]);
            if ($attributes['file_id']) {
                $employeePdfUpload = EmployeePdfUploads::query()
                    ->where('employee_id', $attributes['employee_id'])
                    ->where('file_id', $attributes['file_id'])->first();
                if ($employeePdfUpload) {
                    $employeePdfUpload->delete();
                }
                SaveFileToS3Job::dispatch($attributes['file_id'], 'employee')->delay(now()->addMinute());
            }
            $employee->driving_record_certificate_upload_file_flag = 1;
            $employee->save();
            return $employeeDrivingRecordCertificate;
        }
        return null;
    }

    public function addAptitudeAssessmentForm($attributes)
    {
        $employee = Employee::query()->findOrFail($attributes['employee_id']);
        $user = Auth::user();
        if ($employee) {
            if($employee->retirement_date) {
                $pastTime = Carbon::parse($employee->retirement_date);
                if ($pastTime->lt(Carbon::now()->subYears(3))) {
                    return null;
                }
            }
            $employeeAptitudeAssessmentForm = EmployeeAptitudeAssessmentForms::query()->where('employee_id', $attributes['employee_id'])->first();
            if($employeeAptitudeAssessmentForm) {
                if($attributes['file_id']) {
                    
                    AptitudeAssessmentFormsFileHistory::create([
                        'employee_aptitude_assessment_forms_id' => $employeeAptitudeAssessmentForm->id,
                        'file_id' => $attributes['file_id'],
                        'user_id' => $user->id,
                        'date_of_visit' => $attributes['date_of_visit'],
                        'type' => $attributes['type'],
                    ]);
                    $employeePdfUpload = EmployeePdfUploads::query()
                        ->where('employee_id', $attributes['employee_id'])
                        ->where('file_id', $attributes['file_id'])->first();
                    if ($employeePdfUpload) {
                        $employeePdfUpload->delete();
                    }
                    $employeeAptitudeAssessmentForm->update([
                        'type' => $attributes['type'],
                        'date_of_visit' => $attributes['date_of_visit'],
                    ]);
                    SaveFileToS3Job::dispatch($attributes['file_id'], 'employee')->delay(now()->addMinute());
                } else {
                    $employeeAptitudeAssessmentForm->update([
                        'type' => $attributes['type'],
                        'date_of_visit' => $attributes['date_of_visit'],
                    ]);
                }

            } else {
                $employeeAptitudeAssessmentForm = EmployeeAptitudeAssessmentForms::query()->create([
                    'employee_id' => $attributes['employee_id'],
                    'type' => $attributes['type'],
                    'date_of_visit' => $attributes['date_of_visit'],
                ]);
                if($attributes['file_id']) {
                    AptitudeAssessmentFormsFileHistory::create([
                        'employee_aptitude_assessment_forms_id' => $employeeAptitudeAssessmentForm->id,
                        'file_id' => $attributes['file_id'],
                        'user_id' => $user->id,
                        'date_of_visit' => $attributes['date_of_visit'],
                        'type' => $attributes['type'],
                    ]);
                    $employeePdfUpload = EmployeePdfUploads::query()
                        ->where('employee_id', $attributes['employee_id'])
                        ->where('file_id', $attributes['file_id'])->first();
                    if ($employeePdfUpload) {
                        $employeePdfUpload->delete();
                    }
                    SaveFileToS3Job::dispatch($attributes['file_id'], 'employee')->delay(now()->addMinute());
                }
            }

            if ($employeeAptitudeAssessmentForm) {
                $employee->aptitude_assessment_form_upload_file_flag = 1;
                $employee->save();
            }
            return $employeeAptitudeAssessmentForm;
        }
        return null;
    }

    public function addHealthExaminationResults($attributes)
    {
        $employee = Employee::query()->findOrFail($attributes['employee_id']);
        $user = Auth::user();
        if ($employee) {
            if($employee->retirement_date) {
                $pastTime = Carbon::parse($employee->retirement_date);
                if ($pastTime->lt(Carbon::now()->subYears(3))) {
                    return null;
                }
            }
            $employeeHealthExaminationResults = EmployeeHealthExaminationResults::query()->where('employee_id', $attributes['employee_id'])->first();
            if($employeeHealthExaminationResults) {
                if($attributes['file_id']) {
                    HealthExaminationResultsFileHistory::create([
                        'employee_health_examination_results_id' => $employeeHealthExaminationResults->id,
                        'file_id' => $attributes['file_id'],
                        'user_id' => $user->id,
                        'date_of_visit' => $attributes['date_of_visit'],
                    ]);
                    $employeePdfUpload = EmployeePdfUploads::query()
                        ->where('employee_id', $attributes['employee_id'])
                        ->where('file_id', $attributes['file_id'])->first();
                    if ($employeePdfUpload) {
                        $employeePdfUpload->delete();
                    }
                    $employeeHealthExaminationResults->update([
                        'date_of_visit' => $attributes['date_of_visit'],
                    ]);
                } else {
                    $employeeHealthExaminationResults->update([
                        'date_of_visit' => $attributes['date_of_visit'],
                    ]);
                }
                if ($attributes['file_id']) {
                    SaveFileToS3Job::dispatch($attributes['file_id'], 'employee')->delay(now()->addMinute());
                }
            } else {
                $employeeHealthExaminationResults = EmployeeHealthExaminationResults::query()->create([
                    'employee_id' => $attributes['employee_id'],
                    'date_of_visit' => $attributes['date_of_visit'],
                ]);
                if($attributes['file_id']) {
                    HealthExaminationResultsFileHistory::create([
                        'employee_health_examination_results_id' => $employeeHealthExaminationResults->id,
                        'file_id' => $attributes['file_id'],
                        'user_id' => $user->id,
                        'date_of_visit' => $attributes['date_of_visit'],
                    ]);
                    $employeePdfUpload = EmployeePdfUploads::query()
                        ->where('employee_id', $attributes['employee_id'])
                        ->where('file_id', $attributes['file_id'])->first();
                    if ($employeePdfUpload) {
                        $employeePdfUpload->delete();
                    }
                    SaveFileToS3Job::dispatch($attributes['file_id'], 'employee')->delay(now()->addMinute());
                }
            }
            if ($employeeHealthExaminationResults) {
                $employee->health_examination_results_upload_file_flag = 1;
                $employee->save();
                if ($attributes['file_id']) {
                    SaveFileToS3Job::dispatch($attributes['file_id'], 'employee')->delay(now()->addMinute());
                }
            }
            return $employeeHealthExaminationResults;
        }
        return null;
    }

    public function deleteHealthExaminationFileHistory($id)
    {
        try {
            DB::beginTransaction();
            $authUser = Auth::user();
            $healthExaminationResultsFileHistory = HealthExaminationResultsFileHistory::query()->where('id', $id)->first();
            Log::info('delete health examination file history id: ' .$id . "user id: " . $authUser->id);
            if ($healthExaminationResultsFileHistory) {
                $employeeHealthExaminationResults = EmployeeHealthExaminationResults::query()
                    ->where('id', $healthExaminationResultsFileHistory->employee_health_examination_results_id)
                    ->first();
                $healthExaminationResultsFileHistory->delete();

                $healthExaminationResultsFileHistoryFirst = HealthExaminationResultsFileHistory::query()
                    ->where('employee_health_examination_results_id', $employeeHealthExaminationResults->id)
                    ->orderBy('created_at', 'DESC')
                    ->first();
                if ($healthExaminationResultsFileHistoryFirst) {
                    $employeeHealthExaminationResults->date_of_visit = $healthExaminationResultsFileHistoryFirst->date_of_visit;
                    $employeeHealthExaminationResults->save();
                } else {
                    $employee = Employee::query()->where('id', $employeeHealthExaminationResults->employee_id)->first();
                    if ($employee) {
                        $employee->health_examination_results_upload_file_flag = 0;
                        $employee->save();
                    }
                    $employeeHealthExaminationResults->date_of_visit = null;
                    $employeeHealthExaminationResults->save();
                }
            }
            DB::commit();
            return ['delete success'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('deleteHealthExaminationFileHistory: ' . $e->getMessage());
            return ['delete failed'];
        }
    }

    public function deleteAptitudeAssessmentForm($id)
    {
        try {
            DB::beginTransaction();
            $authUser = Auth::user();
            $aptitudeAssessmentFormsFileHistory = AptitudeAssessmentFormsFileHistory::query()->where('id', $id)->first();
            Log::info('delete aptitude assessment form pdf history id: ' .$id . "user id: " . $authUser->id);
            if ($aptitudeAssessmentFormsFileHistory) {
                $employeeAptitudeAssessmentForm = EmployeeAptitudeAssessmentForms::query()
                    ->where('id', $aptitudeAssessmentFormsFileHistory->employee_aptitude_assessment_forms_id)
                    ->first();
                $employeeAptitudeAssessmentFormFirst = AptitudeAssessmentFormsFileHistory::query()
                    ->where('employee_aptitude_assessment_forms_id', $employeeAptitudeAssessmentForm->id)
                    ->orderBy('created_at', 'DESC')
                    ->first();
                if ($employeeAptitudeAssessmentFormFirst) {
                    $employeeAptitudeAssessmentForm->type = $employeeAptitudeAssessmentFormFirst->type;
                    $employeeAptitudeAssessmentForm->date_of_visit = $employeeAptitudeAssessmentFormFirst->date_of_visit;
                    $employeeAptitudeAssessmentForm->save();
                } else {
                    $employeeAptitudeAssessmentForm->type = null;
                    $employeeAptitudeAssessmentForm->date_of_visit = null;
                    $employeeAptitudeAssessmentForm->save();
                    $employee = Employee::query()->where('id', $employeeAptitudeAssessmentForm->employee_id)->first();
                    if ($employee) {
                        $employee->aptitude_assessment_form_upload_file_flag = 0;
                        $employee->save();
                    }
                }
                $aptitudeAssessmentFormsFileHistory->delete();
            }
            DB::commit();
            return ['delete success'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('deleteHealthExaminationResults: ' . $e->getMessage());
            return ['delete failed'];
        }
    }

    public function deleteEmployeeDrivingRecordCertificates($id)
    {
        try {
            DB::beginTransaction();
            $authUser = Auth::user();
            Log::info('delete employee driving record certificates id: ' .$id . "user id: " . $authUser->id);
            $employeeDrivingRecordCertificates = EmployeeDrivingRecordCertificates::query()->where('id', $id)->first();
            $employeeId = $employeeDrivingRecordCertificates->employee_id;
            if ($employeeDrivingRecordCertificates) {
                $employeeDrivingRecordCertificates->delete();
            }
            $employeeDrivingRecordCertificatesFirst = EmployeeDrivingRecordCertificates::query()
                ->where('employee_id', $employeeId)
                ->orderBy('created_at', 'DESC')
                ->first();
            if (!$employeeDrivingRecordCertificatesFirst) {
                $employee = Employee::query()->where('id', $employeeId)->first();
                if ($employee) {
                    $employee->driving_record_certificate_upload_file_flag = 0;
                    $employee->save();
                }
            }
            DB::commit();
            return ['delete success'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('deleteDrivingRecordCertificate: ' . $e->getMessage());
            return ['delete failed'];
        }
    }

    public function deleteDriverLicense($id)
    {
        try {
            DB::beginTransaction();
            $authUser = Auth::user();
            Log::info('delete driver license id: ' .$id . "user id: " . $authUser->id);
            $employeeDriverLicenses = EmployeeDriverLicenses::query()->orderBy('updated_at', 'DESC')->first();
            $employeeDriverLicenseFileHistory = EmployeeDriverLicensesHistory::query()->where('id', $id)->first();
            if ($employeeDriverLicenses && $employeeDriverLicenseFileHistory) {
                if ($employeeDriverLicenses->surface_file_id == $employeeDriverLicenseFileHistory->surface_file_id
                && $employeeDriverLicenses->back_file_id == $employeeDriverLicenseFileHistory->back_file_id) {
                    $employeeDriverLicenseFileHistoryFirst = EmployeeDriverLicensesHistory::query()
                        ->where('employee_driver_licenses_id', $employeeDriverLicenses->id)
                        ->orderBy('created_at', 'DESC')
                        ->first();
                    if ($employeeDriverLicenseFileHistoryFirst) {
                        $employeeDriverLicenses->surface_file_id = $employeeDriverLicenseFileHistoryFirst->surface_file_id;
                        $employeeDriverLicenses->back_file_id = $employeeDriverLicenseFileHistoryFirst->back_file_id;
                        $employeeDriverLicenses->save();
                    } else {
                        $employeeDriverLicenses->surface_file_id = null;
                        $employeeDriverLicenses->back_file_id = null;
                        $employeeDriverLicenses->save();
                        $employee = Employee::query()->where('id', $employeeDriverLicenses->employee_id)->first();
                        if ($employee) {
                            $employee->driver_license_upload_file_flag = 0;
                            $employee->save();
                        }
                    }
                   
                } else  if ($employeeDriverLicenses->surface_file_id == $employeeDriverLicenseFileHistory->surface_file_id
                    && $employeeDriverLicenses->back_file_id != $employeeDriverLicenseFileHistory->back_file_id) {
                    $employeeDriverLicenseFileHistoryBackFirst = EmployeeDriverLicensesHistory::query()
                        ->where('employee_driver_licenses_id', $employeeDriverLicenses->id)
                        ->whereNotNull('back_file_id')
                        ->orderBy('created_at', 'DESC')
                        ->first();

                    $employeeDriverLicenses->back_file_id = $employeeDriverLicenseFileHistoryBackFirst ? $employeeDriverLicenseFileHistoryBackFirst->back_file_id : null;
                    $employeeDriverLicenses->updated_at = Carbon::now();
                    $employeeDriverLicenses->save();
                   
                } else if ($employeeDriverLicenses->surface_file_id != $employeeDriverLicenseFileHistory->surface_file_id
                    && $employeeDriverLicenses->back_file_id == $employeeDriverLicenseFileHistory->back_file_id) {
                    $employeeDriverLicenseFileHistorySurfaceFirst = EmployeeDriverLicensesHistory::query()
                        ->where('employee_driver_licenses_id', $employeeDriverLicenses->id)
                        ->whereNotNull('surface_file_id')
                        ->orderBy('created_at', 'DESC')
                        ->first();
                    $employeeDriverLicenses->surface_file_id = $employeeDriverLicenseFileHistorySurfaceFirst ? $employeeDriverLicenseFileHistorySurfaceFirst->surface_file_id : null;
                    $employeeDriverLicenses->updated_at = Carbon::now();
                    $employeeDriverLicenses->save();
                }

                $employeeDriverLicensesHistoryAll = EmployeeDriverLicensesHistory::query()
                    ->where('employee_driver_licenses_id', $employeeDriverLicenses->id)
                    ->where('id', '!=', $employeeDriverLicenseFileHistory->id)
                    ->get();
                if ($employeeDriverLicensesHistoryAll->count() == 0) {
                    $employeeDriverLicenses->surface_file_id = null;
                    $employeeDriverLicenses->back_file_id = null;
                    $employeeDriverLicenses->updated_at = Carbon::now();
                    $employeeDriverLicenses->save();
                    $employee = Employee::query()->where('id', $employeeDriverLicenses->employee_id)->first();
                    if ($employee) {
                        $employee->driver_license_upload_file_flag = 0;
                        $employee->save();
                    }
                }
                
                $employeeDriverLicenseFileHistory->delete();
            }
           
            
            DB::commit();
            return ['delete success'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('deleteDriverLicense: ' . $e->getMessage());
            return ['delete failed'];
        }
    }


    public function saveFile(UploadedFile $file)
    {
        $fileName = $this->createFilename($file);
        // Group files by mime type
        $mime = str_replace('/', '-', $file->getMimeType());
        // Group files by the date (week
        $dateFolder = date("YmW");

        // Build the file path
        $filePath = EMPLOYEE_UPLOAD_FILE . "/{$dateFolder}";
        $fileData = File::create([
            'file_path' => $file->storeAs($filePath, $fileName),
            'file_name' => $file->getClientOriginalName(),
            "file_extension" => $file->getClientOriginalExtension(),
            "file_size" => $file->getSize(),
            "file_sys_disk" => 'public',
            "file_url" => Storage::url($filePath . '/' . $fileName)
        ]);

        return $fileData;
    }

    protected function createFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = str_replace("." . $extension, "", $file->getClientOriginalName()); // Filename without extension
        // Add timestamp hash to name of the file
        $filename .= "_" . md5(time()) . "." . $extension;
        return $filename;
    }
  
    public function getAll()
    {
        $employee = Employee::query()->select('id', 'name')->whereNull('deleted_at')->get();
        $employee->map(function ($item) {
            $item->name = str_replace('//', '　', $item->name);
            return $item;
        });
        return $employee;
    }

    public function getEmployeeByDepartmentId($department_id)
    {
        $employee = Employee::query()
            ->select('id', 'name', 'employee_code','final_department_id')
            ->where('final_department_id', $department_id)
            ->get();
        $employee->map(function ($item) {
            $item->name = str_replace('//', '　', $item->name);
            return $item;
        });
        return $employee;
    }

}
