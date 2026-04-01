<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class SingleUserMovieWatching implements  FromView, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;
    protected $sheet_name;

    public function __construct($data, $sheet_name)
    {
        $this->data = $data;
        $this->sheet_name = $sheet_name;
    }

    public function view(): View
    {
        return view('export.SingleUserMovieWatching', [
            'data' => $this->data,
        ]);
    }

    public function title(): string
    {
        return $this->sheet_name;
    }
}
