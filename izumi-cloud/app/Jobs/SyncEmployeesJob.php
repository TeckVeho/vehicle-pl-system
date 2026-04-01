<?php

namespace App\Jobs;

use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\Employee;

class SyncEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $employeesId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($employeesId = null)
    {
        $this->employeesId = $employeesId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Cloud Sync SyncEmployeesJob Data Start at:" . Carbon::now()->toDateTimeString());
        $listBaseUrl = LIST_BASE_URL_EMPLOYEE_SYNC;

        if (App::environment('staging')) {
            $listBaseUrl = LIST_BASE_URL_EMPLOYEE_SYNC_STAGE;
        }
        if (App::environment('production')) {
            $listBaseUrl = LIST_BASE_URL_EMPLOYEE_SYNC_PRODUCTION;
        }

        if (!$this->employeesId) {
            $datas = Employee::select('id', 'employee_code', 'name', 'birthday', 'retirement_date',
                'employee_type', 'job_type', 'grade', 'employee_grade_2', 'boarding_employee_grade',
                'final_department_id', 'position', 'hire_start_date', 'employee_role')->get();
        } else {
            $datas = Employee::select('id', 'employee_code', 'name', 'birthday', 'retirement_date',
                'employee_type', 'job_type', 'grade', 'employee_grade_2', 'boarding_employee_grade',
                'final_department_id', 'position', 'hire_start_date', 'employee_role')->where('id', $this->employeesId)->get();
        }
        $dataEmployee = [];
        foreach ($datas as $data) {
            $dataEmployee[] = [
                'id' => $data->id,
                'employee_code' => $data->employee_code,
                'name' => $data->name,
                'birthday' => $data->birthday,
                'retirement_date' => $data->retirement_date,
                'employee_type' => $data->employee_type,
                'job_type' => $data->job_type,
                'grade' => $data->grade,
                'employee_grade_2' => $data->employee_grade_2,
                'boarding_employee_grade' => $data->boarding_employee_grade,
                'final_department_id' => $data->final_department_id,
                'position' => $data->position,
                'hire_start_date' => $data->hire_start_date,
                "employee_role" => $data->employee_role
            ];
        }
        foreach ($listBaseUrl as $baseUrl) {
            try {
                Log::info("Cloud Sync employees to:" . $baseUrl . '/api/sync/employees');
                $response = Http::timeout(60)->withoutVerifying()->post($baseUrl . '/api/sync/employees', $dataEmployee)->json();
                Log::info("Cloud Sync employees response:" . json_encode($response));
            } catch (\Exception $exception) {
                Log::error("Sync employees error:" . $exception->getMessage());
                continue;
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
    }

}
