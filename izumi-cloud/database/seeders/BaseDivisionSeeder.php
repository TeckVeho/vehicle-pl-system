<?php

namespace Database\Seeders;

use App\Imports\BaseDivisionImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class BaseDivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pathFileImport = __DIR__.'/import/division.xlsx';
        Excel::import(new BaseDivisionImport, $pathFileImport);
    }
}
