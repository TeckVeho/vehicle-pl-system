<?php

namespace App\Jobs;

use App\Models\Department;
use App\Models\Vehicle;
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

class SyncVehicleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vehicle_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vehicle_id = null)
    {
        $this->vehicle_id = $vehicle_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Cloud Sync SyncVehicleJob Data Start at:" . Carbon::now()->toDateTimeString());
        $listBaseUrl = BASE_URL_SMART_APPROVAL;
        if (App::environment('staging')) {
            $listBaseUrl = BASE_URL_SMART_APPROVAL_STAGE;
        }
        if (App::environment('production')) {
            $listBaseUrl = BASE_URL_SMART_APPROVAL_PRODUCTION;
        }

        if ($this->vehicle_id) {
            $vehicles = Vehicle::withTrashed()->with('plate_history')->where('id', $this->vehicle_id)->get()->toArray();
        } else {
            $vehicles = Vehicle::withTrashed()->with('plate_history')->get()->toArray();
        }

        try {
            Log::info("Cloud Sync vehicles to:" . $listBaseUrl . '/api/sync/vehicles');
            $response = Http::timeout(60)->withoutVerifying()->post($listBaseUrl . '/api/sync/vehicles', $vehicles)->json();
            Log::info("Cloud Sync vehicles response:" . json_encode($response));
        } catch (\Exception $exception) {
            Log::error("Sync vehicles error: $listBaseUrl" . $exception->getMessage());
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
