<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Route;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class CourseRoutImport implements ToCollection
{

    public function __construct()
    {
    }

    public function collection(Collection $rows)
    {
        $rowArr = $rows->toArray();
        $rowsValidation = array_diff_key($rowArr, array_flip([0]));

        foreach ($rowsValidation as $row) {
            $course = Course::with('course_code')->withCount('routes')
                ->where('course_code', Arr::get($row, 0))->first();
            $route_chk = Route::where('name', Arr::get($row, 1))->first();
            if ($course && $route_chk) {
                $course->routes->attach($route_chk->id,
                    ['position' => $course->routes_count + 1,]);
            }
        }
    }
}
