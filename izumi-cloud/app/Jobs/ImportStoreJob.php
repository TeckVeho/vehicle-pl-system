<?php

namespace App\Jobs;


use App\Imports\EmployeeImport;
use App\Imports\StoreExcelImport;
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

class ImportStoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pathFile;
    protected $file_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file_name,$pathFile)
    {
        $this->pathFile = $pathFile;
        $this->file_name = $file_name;
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
        Storage::delete($this->pathFile);
    }

    public function importToTableEmployee()
    {
        //Storage::put(TEMP_DISK . "/tmp.txt", " ");
        Excel::import(new StoreExcelImport, Storage::path($this->pathFile));
        Storage::delete($this->pathFile);
        //Storage::put(TEMP_DISK . "/tmp.txt", " ");
    }
}
