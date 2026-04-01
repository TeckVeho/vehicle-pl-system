<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class EmployeesExport implements FromView, WithCustomCsvSettings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('export.employees', ["data" => $this->data]);
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true, // BOM sẽ giúp một số ứng dụng nhận diện đúng UTF-8
            'output_encoding' => 'UTF-8',
        ];
    }
}

