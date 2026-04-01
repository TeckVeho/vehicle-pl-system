<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SaveItkeeperJob;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class SaveItkeeper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:save-itkeeper';

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
        $startDate = Carbon::create(2024, 1, 1); // Ngày bắt đầu: 1/1/2024
        $endDate = Carbon::create(2024, 12, 31); // Ngày kết thúc: 31/12/2024
        $result = CarbonPeriod::create($startDate, '1 day', $endDate);
        foreach ($result as $dt) {
            SaveItkeeperJob::dispatch($dt);
        }
    }
}
