<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExecuteCalculateMaintJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vehicle_id;
    protected $is_updated;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vehicle_id = null, $is_updated = false)
    {
        $this->vehicle_id = $vehicle_id;
        $this->is_updated = $is_updated;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $baseUrl = BASE_URL_MAINTENANCE;
        if (App::environment('staging')) {
            $baseUrl = BASE_URL_MAINTENANCE_STAGE;
        }
        if (App::environment('production')) {
            $baseUrl = BASE_URL_MAINTENANCE_PRODUCTION;
        }
        $urlCallApi = $baseUrl . API_EXECUTE_CALCULATE_MAINT;
        $res = Http::timeout(60)->withoutVerifying()->get($urlCallApi, ['vehicle_id' => $this->vehicle_id, 'is_updated' => $this->is_updated]);
        if ($res->status() != 200) {
            Log::info('Calculate maintenance cost fail');
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
