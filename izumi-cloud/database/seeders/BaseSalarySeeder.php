<?php

namespace Database\Seeders;

use App\Imports\BaseSalaryImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class BaseSalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pathFileImport = __DIR__.'/import/base_salary.xlsx';
        Excel::import(new BaseSalaryImport, $pathFileImport);
    }
}
