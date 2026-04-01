<?php

namespace App\Jobs;

use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncCourse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $coursesId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($coursesId = null)
    {
        $this->coursesId = $coursesId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Cloud Sync SyncCoursesJob Data Start at:" . Carbon::now()->toDateTimeString());
        $baseUrl = BASE_URL_WORK_SHIFT;
        if (App::environment('staging')) {
            $baseUrl = BASE_URL_WORK_SHIFT_STAGE;
        }
        if (App::environment('production')) {
            $baseUrl = BASE_URL_WORK_SHIFT_PRODUCTION;
        }

        try {
            if ($this->coursesId) {
                $courses = Course::query()->where('id', $this->coursesId)->get();

            } else {
                $courses = Course::query()->get();

            }
            $dataCourses = [];
            foreach ($courses as $course) {
                $dataCourses[] = [
                    'id' => $course->coursesId,
                    'course_code' => $course->course_code,
                    'start_date' => $course->start_date,
                    'end_date' => $course->end_date,
                    //'course_type' => $course->course_type,
                    //'bin_type' => $course->bin_type,
                    //'delivery_type' => $course->delivery_type,
                    'start_time' => $course->start_time,
//                    'gate' => $course->gate,
//                    'wing' => $course->wing,
//                    'tonnage' => $course->tonnage,
//                    'quantity' => $course->quantity,
//                    'allowance' => $course->allowance,
                    'department_id' => $course->department_id,
                    'course_flag' => $course->course_flag
                ];
            }

            Log::info("Cloud Sync Courses to:" . $baseUrl . '/api/sync/cloud/courses');
            $response = Http::timeout(60)->withoutVerifying()->post($baseUrl . '/api/sync/cloud/courses', $dataCourses)->json();
            Log::info("Cloud Sync Courses response:" . json_encode($response));

        } catch (\Exception $exception) {
            Log::error("Sync Courses error: $baseUrl" . $exception->getMessage());
        }
    }
}
