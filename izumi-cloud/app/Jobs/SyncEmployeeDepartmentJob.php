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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncEmployeeDepartmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->syncToSaSys();
        $this->syncToAIShiftSys();
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

    private function syncToSaSys()
    {
        Log::info("Cloud Sync SyncEmployeeDepartmentJob Data Start at:" . Carbon::now()->toDateTimeString());
        $listBaseUrl = BASE_URL_SMART_APPROVAL;
        if (App::environment('staging')) {
            $listBaseUrl = BASE_URL_SMART_APPROVAL_STAGE;
        }
        if (App::environment('production')) {
            $listBaseUrl = BASE_URL_SMART_APPROVAL_PRODUCTION;
        }

        $datas = DB::table('employee_department')->select('employee_id', 'department_id', 'start_date')->get();

        $dataEmployee = [];

        foreach ($datas as $data) {
            $dataEmployee[] = [
                'employee_id' => $data->employee_id,
                'department_id' => $data->department_id,
                'start_date' => $data->start_date
            ];
        }
        try {
            Log::info("Cloud Sync employees_department to:" . $listBaseUrl . '/api/sync/employees_department');
            $response = Http::timeout(60)->withoutVerifying()->post($listBaseUrl . '/api/sync/employees_department', $dataEmployee)->json();
            Log::info("Cloud Sync employees_department response:" . json_encode($response));
        } catch (\Exception $exception) {
            Log::error("Sync employees_department error: $listBaseUrl" . $exception->getMessage());
        }
    }

    private function syncToAIShiftSys()
    {
        Log::info("Cloud Sync syncToAIShiftSys Data Start at:" . Carbon::now()->toDateTimeString());
        $listBaseUrl = BASE_URL_WORK_SHIFT;
        if (App::environment('staging')) {
            $listBaseUrl = BASE_URL_WORK_SHIFT_STAGE;
        }
        if (App::environment('production')) {
            $listBaseUrl = BASE_URL_WORK_SHIFT_PRODUCTION;
        }

        $datas = DB::table('employee_working_department')
            ->join('employees', 'employee_working_department.employee_id', '=', 'employees.id')
            ->select([
                'employee_working_department.department_id',
                'employee_working_department.start_date',
                'employee_working_department.end_date',
                'employee_working_department.grade',
                'employee_working_department.employee_grade_2',
                'employee_working_department.boarding_employee_grade',
                'employee_working_department.boarding_employee_grade_2',
                'employee_working_department.midnight_worktime',
                'employee_working_department.transportation_compensation',
                'employee_working_department.daily_transportation_cp',
                'employees.employee_code'
            ])
            ->get();

        try {
            Log::info("Cloud Sync employees_working_department to:" . $listBaseUrl . '/api/sync/employees_department');
            $response = Http::timeout(60)->withoutVerifying()->post($listBaseUrl . '/api/sync/employees_department', $datas->toArray())->json();
            Log::info("Cloud Sync employees_working_department response:" . json_encode($response));
        } catch (\Exception $exception) {
            Log::error("Sync employees_working_department error: $listBaseUrl" . $exception->getMessage());
        }
    }

}
