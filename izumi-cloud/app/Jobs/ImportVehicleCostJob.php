<?php

namespace App\Jobs;


use App\Imports\EmployeeImport;
use App\Imports\VehicleCostImport;
use App\Models\Employee;
use Helper\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportVehicleCostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pathFile;
    protected $memory_limit;
    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pathFile, $date)
    {
        $this->pathFile = $pathFile;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->importToTableEmployee();
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

    public function importToTableEmployee()
    {
//        ini_set('memory_limit', '-1');
        //Storage::put(TEMP_DISK . "/tmp.txt", " ");
        Common::setInputEncoding(Storage::path($this->pathFile));
        Excel::import(new VehicleCostImport($this->date), Storage::path($this->pathFile));
        //Storage::put(TEMP_DISK . "/tmp.txt", " ");
    }
}
