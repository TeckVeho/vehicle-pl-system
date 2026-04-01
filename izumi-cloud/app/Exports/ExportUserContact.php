<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportUserContact implements WithMultipleSheets
{
    protected $datas;
    public function __construct($datas)
    {
        $this->datas = $datas;
    }
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new SingleUrgentContactExport($this->datas['data_all'], "全て");
        foreach ($this->datas['data'] as $key => $data) {
            $sheets[] = new SingleUrgentContactExport($data, $key);
        }

        return $sheets;
    }

}
