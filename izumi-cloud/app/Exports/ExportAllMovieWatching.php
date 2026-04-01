<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
class ExportAllMovieWatching implements WithMultipleSheets
{
    protected $datas;

    public function __construct($datas)
    {
        $this->datas = $datas;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->datas as  $data) {
            $sheets[] = new SingleUserMovieWatching($data->movieWatching, $data->title);
        }

        return $sheets;
    }
}
