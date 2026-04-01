<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeDriverLicenses;
use App\Models\EmployeeDriverLicensesHistory;
use App\Models\Employee;

class UpdateDriverLicenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-driver-licenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $employees = Employee::query()->get();
        foreach ($employees as $key => $employee) {
            $employeeDriverLicenses = EmployeeDriverLicenses::query()
                ->where('employee_id', $employee->id)->orderBy('id', 'DESC')->get();
            if ($employeeDriverLicenses->count() > 0) {
                $employeeDriverLicenseFirst = $employeeDriverLicenses->first();
                foreach ($employeeDriverLicenses as $key => $employeeDriverLicense) {
                    $employeeDriverLicenseHistory = EmployeeDriverLicensesHistory::create([
                        'employee_driver_licenses_id' => $employeeDriverLicenseFirst->id,
                        'user_id' => $employeeDriverLicense->user_id,
                        'surface_file_id' => $employeeDriverLicense->surface_file_id,
                        'back_file_id' => $employeeDriverLicense->back_file_id,
                        'created_at' => $employeeDriverLicense->created_at,
                        'updated_at' => $employeeDriverLicense->updated_at,
                    ]);
                    if ($key != 0) {
                        $employeeDriverLicense->delete();
                    }
                }
            }
        }
    }
}
