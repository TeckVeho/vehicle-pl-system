<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use App\Models\InsuranceRate;
use App\Models\InsuranceRateHistory;
use Illuminate\Support\Facades\DB;

class EmployeeDefaultPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Employee::query()->update(['position' => DB::raw('id')]);
    }
}
