<?php
/**
 * Created by VeHo.
 * Year: 2026-03-16
 */

namespace Repository;

use App\Models\EmployeePdfStorage;
use App\Repositories\Contracts\EmployeePdfStorageRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\Employee;
use App\Models\EmployeeDriverLicenses;
use App\Models\EmployeeDrivingRecordCertificates;
use App\Models\EmployeeAptitudeAssessmentForms;
use App\Models\EmployeeHealthExaminationResults;
use App\Models\AptitudeAssessmentFormsFileHistory;
use App\Models\HealthExaminationResultsFileHistory;
use App\Jobs\SaveFileToS3Job;
use App\Models\EmployeeDriverLicensesHistory;


class EmployeePdfStorageRepository extends BaseRepository implements EmployeePdfStorageRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param EmployeePdfStorage $model
       */

    public function model()
    {
        return EmployeePdfStorage::class;
    }


    public function createNewEmployeePdfStorage($attributes)
    {
        $user = Auth::user();
        $attributes['user_id'] = $user->id;
        return $this->model->create($attributes);
    }

    public function listAll($request)
    {
        $perPage = Arr::get($request, 'per_page', 10);
        $page = Arr::get($request, 'page', 1);
        $sortBy = Arr::get($request, 'sort_by') ? Arr::get($request, 'sort_by') : 'id';
        $sortType = Arr::get($request,'sort_type') ? 'asc' : 'desc';
        $department_id = Arr::get($request, 'department_id');
        $user_id = Arr::get($request, 'user_id');

        $query = $this->model->with([
            'user' => function ($query) {
                $query->select('id', 'name');
            },
            'file',
            'department' => function ($query) {
                $query->select('id', 'name');
            }
        ]);

        if ($department_id) {
            $query->where('department_id', $department_id);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($sortBy && $sortBy == 'user_id') {
           $sortBy = 'user_id';
        }

        if ($sortBy && $sortBy == 'file_id') {
            $sortBy = 'file_id';
        }

        if ($sortBy && $sortBy == 'department_id') {
            $sortBy = 'department_id';
        }

        $query =  $query->orderBy($sortBy, $sortType)->paginate($perPage);

        return $query;
    }

    public function getById($id)
    {
        return $this->model->where('id', $id)
        ->with([ 
            'file',
            'user' => function ($query) {
                $query->select('id', 'name');
            },
            'department' => function ($query) {
                $query->select('id', 'name');
            }
        ])
        ->first();
    }

    public function addDriverLicense($attributes)
    {
        try {
            DB::beginTransaction();
            $employee = Employee::query()->findOrFail($attributes['employee_id']);
            $user = Auth::user();
            $employee_pdf_storage_id = Arr::get($attributes, 'employee_pdf_storage_id');
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

                } else {
                     if ($attributes['surface_file_id'] && $attributes['back_file_id'] == null) {
                        $employeeDriverLicenseFirst->surface_file_id = $attributes['surface_file_id'];
                        $employeeDriverLicenseFirst->save();
                        EmployeeDriverLicensesHistory::query()->create([
                            'employee_driver_licenses_id' => $employeeDriverLicenseFirst->id,
                            'user_id' => $user->id,
                            'surface_file_id' => $attributes['surface_file_id'],
                        ]);
                        SaveFileToS3Job::dispatch($attributes['surface_file_id'], 'employee')->delay(now()->addMinute());
                    } else if ($attributes['surface_file_id'] == null && $attributes['back_file_id']) {
                        $employeeDriverLicenseFirst->back_file_id = $attributes['back_file_id'];
                        $employeeDriverLicenseFirst->save();
                        EmployeeDriverLicensesHistory::query()->create([
                            'employee_driver_licenses_id' => $employeeDriverLicenseFirst->id,
                            'user_id' => $user->id,
                            'back_file_id' => $attributes['back_file_id'],
                        ]);
                        SaveFileToS3Job::dispatch($attributes['back_file_id'], 'employee')->delay(now()->addMinute());
                    }
                }

                $employee_pdf_storage = $this->model->where('id', $employee_pdf_storage_id)->first();
                if ($employee_pdf_storage) {
                    $employee_pdf_storage->delete();
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
        try {
            DB::beginTransaction();
            $employee = Employee::query()->findOrFail($attributes['employee_id']);
            $employee_pdf_storage_id = Arr::get($attributes, 'employee_pdf_storage_id');
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
                    SaveFileToS3Job::dispatch($attributes['file_id'], 'employee')->delay(now()->addMinute());
                }
                $employee->driving_record_certificate_upload_file_flag = 1;
                $employee->save();
                $employee_pdf_storage = $this->model->where('id', $employee_pdf_storage_id)->first();
                if ($employee_pdf_storage) {
                    $employee_pdf_storage->delete();
                }
                DB::commit();
                return $employeeDrivingRecordCertificate;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('addDrivingRecordCertificate: ' . $e->getMessage());
            return  $e->getMessage();
        }
    }

    public function addAptitudeAssessmentForm($attributes)
    {
        try {
            DB::beginTransaction();
            $employee = Employee::query()->findOrFail($attributes['employee_id']);
            $user = Auth::user();
            $employee_pdf_storage_id = Arr::get($attributes, 'employee_pdf_storage_id');

            if ($employee->retirement_date) {
                $pastTime = Carbon::parse($employee->retirement_date);
                if ($pastTime->lt(Carbon::now()->subYears(3))) {
                    DB::rollBack();
                    return null;
                }
            }

            $employeeAptitudeAssessmentForm = EmployeeAptitudeAssessmentForms::query()
                ->where('employee_id', $attributes['employee_id'])
                ->first();

            if ($employeeAptitudeAssessmentForm) {
                if (Arr::get($attributes, 'file_id')) {
                    AptitudeAssessmentFormsFileHistory::create([
                        'employee_aptitude_assessment_forms_id' => $employeeAptitudeAssessmentForm->id,
                        'file_id' => $attributes['file_id'],
                        'user_id' => $user->id,
                        'date_of_visit' => $attributes['date_of_visit'],
                        'type' => $attributes['type'],
                    ]);
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
                if (Arr::get($attributes, 'file_id')) {
                    AptitudeAssessmentFormsFileHistory::create([
                        'employee_aptitude_assessment_forms_id' => $employeeAptitudeAssessmentForm->id,
                        'file_id' => $attributes['file_id'],
                        'user_id' => $user->id,
                        'date_of_visit' => $attributes['date_of_visit'],
                        'type' => $attributes['type'],
                    ]);
                    SaveFileToS3Job::dispatch($attributes['file_id'], 'employee')->delay(now()->addMinute());
                }
            }

            if ($employeeAptitudeAssessmentForm) {
                $employee->aptitude_assessment_form_upload_file_flag = 1;
                $employee->save();
            }

            if ($employee_pdf_storage_id) {
                $employee_pdf_storage = $this->model->where('id', $employee_pdf_storage_id)->first();
                if ($employee_pdf_storage) {
                    $employee_pdf_storage->delete();
                }
            }

            DB::commit();
            return $employeeAptitudeAssessmentForm;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('addAptitudeAssessmentForm: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function addHealthExaminationResults($attributes)
    {
        try {
            DB::beginTransaction();
        $employee = Employee::query()->findOrFail($attributes['employee_id']);
        $user = Auth::user();
        $employee_pdf_storage_id = Arr::get($attributes, 'employee_pdf_storage_id');
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
            $employee_pdf_storage = $this->model->where('id', $employee_pdf_storage_id)->first();
            if ($employee_pdf_storage) {
                $employee_pdf_storage->delete();
            }
            DB::commit();
            return $employeeHealthExaminationResults;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('addHealthExaminationResults: ' . $e->getMessage());
            return  $e->getMessage();   
        }
        return null;
    }

}
