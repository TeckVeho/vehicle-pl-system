<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MovieWatching;

class UpdateMovieWatching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-movie-watching';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $movieWatchings = MovieWatching::where('time', '00:00:00')->get();
        foreach ($movieWatchings as $movieWatching) {
            $movieWatching->export_flag = 1;
            $movieWatching->save();
        }
    }
}
