<?php

namespace App\Console\Commands;

use App\Models\Movies;
use App\Models\MovieSchedules;
use App\Models\MovieWatching;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendNotiTheMovieIsDeliveried extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendNotiTheMovieIsDeliveried';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Noti The Movie Is Deliveried';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now()->format('Y-m-d');
        $isNotSendNoti = 0;
        $movieSchedule = MovieSchedules::where('date', $now)
            ->where('time', '<', Carbon::now()->format('H:i:30'))
            ->where('is_send_noti', $isNotSendNoti)
            ->orderBy('id','DESC')
            ->first();
        $baseUrl = BASE_URL_WEB_APP;
        //$baseUrl = "http://izumi-web-app.test";
        if (App::environment('staging')) {
            $baseUrl = BASE_URL_WEB_APP_STAGE;
        }
        if (App::environment('production')) {
            $baseUrl = BASE_URL_WEB_APP_PRODUCTION;
        }

        if($movieSchedule) {
            $movieSchedule->is_send_noti = 1;
            $movieSchedule->save();
            $response = Http::timeout(60)->withoutVerifying()->get($baseUrl . '/api/cloud/noti-movie', [
                'type' => 0,
                'movie_id' => $movieSchedule->id
            ])->json();
            Log::info("movieId: " . $movieSchedule->id);
        }
        return 0;
    }
}
