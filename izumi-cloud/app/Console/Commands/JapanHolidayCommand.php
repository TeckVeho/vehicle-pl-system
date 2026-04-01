<?php

namespace App\Console\Commands;

use App\Models\GovernmentHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;


class JapanHolidayCommand extends \Illuminate\Console\Command
{
    protected $signature = 'update:japan_holiday {start_year?} {end_year?}';
    protected $description = 'japan_holiday run';

    public function handle()
    {
        $checkData = GovernmentHoliday::first();
        $start_year = $this->argument('start_year');
        if (!$start_year) {
            $start_year = 2000;
            if ($checkData) {
                $start_year = Carbon::now()->year;
            }
        }
        $end_year = $this->argument('end_year');
        if (!$end_year) {
            $end_year = 2050;
            if ($checkData) {
                $end_year = Carbon::now()->addYears(30)->year;
            }
        }

        $resultYears = CarbonPeriod::create(Carbon::create($start_year), '2 year', Carbon::create($end_year));
        foreach ($resultYears as $resultYear) {
            $response = Http::timeout(60)->get('http://calendar-service.net/cal',
                [
                    'year_style' => 'normal',
                    'month_style' => 'ja',
                    'wday_style' => 'ja',
                    'format' => 'csv',
                    'holiday_only' => 1,
                    'zero_padding' => 1,
                    'start_year' => $resultYear->year,
                    'end_year' => Carbon::create($resultYear->year)->addYears(2)->year,
                ]
            );
            $string = $response->body();
            $string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string, mb_list_encodings(), true));
            $data = str_getcsv($string, "\n");
            foreach ($data as $key => $str) {
                if ($key > 0) {
                    $dataArr = explode(",", $str);
                    $year = Arr::get($dataArr, 0);
                    $month = Arr::get($dataArr, 1);
                    $day = Arr::get($dataArr, 2);
                    $description = Arr::get($dataArr, 7);
                    $date = Carbon::create($year, $month, $day);
                    if ($description) {
                        $governmentHoliday = GovernmentHoliday::firstOrCreate(['date' => $date]);
                        $governmentHoliday->description = $description;
                        $governmentHoliday->save();
                    }
                }
            }
        }
    }
}
