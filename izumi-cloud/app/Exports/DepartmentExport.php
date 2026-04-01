<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class DepartmentExport implements FromView, WithCustomCsvSettings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('export.department', ["data" => $this->data]);
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
            'output_encoding' => 'UTF-8',
        ];
    }
}
