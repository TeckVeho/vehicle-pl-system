<?php

namespace App\Console\Commands\DataConnect;

use App\Jobs\ImportVehicleITPS3Job;
use App\Models\S3File;
use Carbon\Carbon;
use Helper\Common;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SaveItpS3DataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:save-itp-s3-data';

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
        $indexField = [
            'vehicle_identify_number' => Common::excelColumnToIndex('C'),
            'start_date_time' => Common::excelColumnToIndex('L'),
            'end_date_time' => Common::excelColumnToIndex('M'),
            'no_number_plate' => Common::excelColumnToIndex('K'),
        ];
        $filesItpS3 = S3File::query()
            ->where('status', '<', 1)
            ->orderBy('id', 'desc')
            ->limit(1000)
            ->get();
        foreach ($filesItpS3 as $file) {
            $file->status = S3File::STATUS_PROCESS;
            $file->save();
            ImportVehicleITPS3Job::dispatch($file, $indexField);
        }
    }
}
