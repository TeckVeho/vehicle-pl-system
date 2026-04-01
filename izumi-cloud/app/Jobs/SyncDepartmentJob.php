<?php

namespace App\Jobs;

use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncDepartmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Cloud Sync SyncDepartmentJob Data Start at:" . Carbon::now()->toDateTimeString());
        $listBaseUrl = LIST_BASE_URL_USER_SYNC;
        if (App::environment('staging')) {
            $listBaseUrl = LIST_BASE_URL_USER_SYNC_STAGE;
        }
        if (App::environment('production')) {
            $listBaseUrl = LIST_BASE_URL_USER_SYNC_PRODUCTION;
        }
        $departments = Department::query()->get();

        foreach ($listBaseUrl as $baseUrl) {
            try {
                Log::info("Cloud Sync Department to:" . $baseUrl . '/api/sync/department');
                $response = Http::timeout(60)->withoutVerifying()->post($baseUrl . '/api/sync/department', $departments->toArray())->json();
                Log::info("Cloud Sync Department response:" . json_encode($response));
            } catch (\Exception $exception) {
                Log::error("Sync department error: $baseUrl" . $exception->getMessage());
                continue;
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
    }

}
