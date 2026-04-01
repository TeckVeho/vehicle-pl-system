<?php

namespace App\Jobs;

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

class SendNotiDeleteMovieNow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $baseUrl = BASE_URL_WEB_APP;
        if (App::environment('staging')) {
            $baseUrl = BASE_URL_WEB_APP_STAGE;
        }
        if (App::environment('production')) {
            $baseUrl = BASE_URL_WEB_APP_PRODUCTION;
        }
        $response = Http::timeout(60)->withoutVerifying()
            ->get($baseUrl . '/api/cloud/noti-movie', ['type' => 1, 'movie_id' => null])
            ->json();
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
    }
}
