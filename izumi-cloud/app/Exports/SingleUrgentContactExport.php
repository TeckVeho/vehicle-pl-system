<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class SingleUrgentContactExport implements  FromView, WithTitle
{
    protected $data;
    protected $department_name;

    public function __construct($data, $formatName)
    {
        $this->data = $data;
        $this->department_name = $formatName;
    }
    public function view(): View
    {
        return view('export.userContact', [
            'data' => $this->data,
        ]);
    }

    public function title(): string
    {
        return $this->department_name;
    }
}
