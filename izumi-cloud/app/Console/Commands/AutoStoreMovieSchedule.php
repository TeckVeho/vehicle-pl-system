<?php

namespace App\Console\Commands;

use App\Models\Movies;
use App\Models\MovieSchedules;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoStoreMovieSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-store-movie-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Log::info("run auto-store-movie-schedule");
        $subday = Carbon::now()->subDay()->format('Y-m-d');
        $now = Carbon::now()->format('Y-m-d');
        $getAllsubMovieSchedules = MovieSchedules::query()
            ->where('auto_flag', 1)
            ->get();
        $subMovieSchedule = MovieSchedules::query()
            ->where('auto_flag', 1)
            ->join('movies', 'movie_schedules.movie_id', '=', 'movies.id')
            ->whereNull('movies.deleted_at')
            ->orderBy('movies.position', 'DESC')
            ->select('movie_schedules.*', 'movies.position as movie_position')
            ->first();
        $nowMovieSchedules = MovieSchedules::query()->where('date', $now)->first();
        if(!$nowMovieSchedules) {
            if ($subMovieSchedule && $subMovieSchedule->movie_position !== null) {
                $movie = Movies::query()
                    ->where('position', '>', $subMovieSchedule->movie_position)
                    ->where('is_loop_enabled', true)
                    ->orderBy('position', 'ASC')
                    ->first();
                if ($movie) {
                    MovieSchedules::create([
                        'movie_id' => $movie->id,
                        'date' => Carbon::now()->format('Y-m-d'),
                        'time' => "12:00:00",
                        'assign_type' => 1,
                        'auto_flag' => 1
                    ]);
                } else {
                    $movieFirst = Movies::query()
                        ->where('is_loop_enabled', true)
                        ->orderBy('position', 'ASC')
                        ->first();
                    if ($movieFirst) {
                        MovieSchedules::create([
                            'movie_id' => $movieFirst->id,
                            'date' => Carbon::now()->format('Y-m-d'),
                            'time' => "12:00:00",
                            'assign_type' => 1,
                            'auto_flag' => 1
                        ]);
                    } else {
                        Log::warning("No loop-enabled movies found for auto-schedule");
                    }
                }

            } else {
                $movieFirst = Movies::query()
                    ->where('is_loop_enabled', true)
                    ->orderBy('position', 'ASC')
                    ->first();
                if ($movieFirst) {
                    MovieSchedules::create([
                        'movie_id' => $movieFirst->id,
                        'date' => Carbon::now()->format('Y-m-d'),
                        'time' => "12:00:00",
                        'assign_type' => 1,
                        'auto_flag' => 1
                    ]);
                } else {
                    Log::warning("No loop-enabled movies found for auto-schedule");
                }
            }
            if ($getAllsubMovieSchedules->count() > 0) {
                $getAllsubMovieSchedules->map(function ($query) {
                    $query->update([
                        'auto_flag' => 0,
                    ]);
                });
            }
        }
    }
}
