<?php

namespace App\Console\Commands;

use App\Jobs\DataConnectionJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Repository\DataConnectionRepository;

class DataconnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:data_connection {id} {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $date = $this->argument('date') ? $this->argument('date') : Carbon::now()->format('Y-m-d');
        DataConnectionJob::dispatch($this->argument('id'), $date);
    }
}
