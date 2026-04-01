<?php

namespace App\Jobs;

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
use Throwable;

class SyncVehicleHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Cloud Sync SyncVehicleHistoryJob Data Start at:" . Carbon::now()->toDateTimeString());
        $listBaseUrl = BASE_URL_SMART_APPROVAL;
        if (App::environment('staging')) {
            $listBaseUrl = BASE_URL_SMART_APPROVAL_STAGE;
        }
        if (App::environment('production')) {
            $listBaseUrl = BASE_URL_SMART_APPROVAL_PRODUCTION;
        }

        try {
            Log::info("Cloud Sync vehicles history to:" . $listBaseUrl . '/api/sync/vehicles-history');
            $response = Http::timeout(60)->withoutVerifying()->post($listBaseUrl . '/api/sync/vehicles-history',  $this->data)->json();
            Log::info("Cloud Sync vehicles history response:" . json_encode($response));
        } catch (\Exception $exception) {
            Log::error("Sync vehicles history error: $listBaseUrl" . $exception->getMessage());
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
    }
}
