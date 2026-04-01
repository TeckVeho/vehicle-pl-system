<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class CourseExport implements FromView, WithCustomCsvSettings
{
    protected $dataCourse;

    public function __construct($dataCourse)
    {
        $this->dataCourse = $dataCourse;
    }

    public function view(): View
    {
        return view('export.course', ["data" => $this->dataCourse]);
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => false,
            'output_encoding' => 'SJIS-win',
        ];
    }
}
