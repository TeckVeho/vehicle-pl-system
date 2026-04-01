<?php

namespace App\Jobs;


use App\Imports\EmployeeImport;
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

class ImportDataToTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $toTable;
    protected $pathFile;
    protected $itemUpdate;
    protected $memory_limit;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($toTable, $pathFile, $itemUpdate)
    {
        $this->toTable = $toTable;
        $this->pathFile = $pathFile;
        $this->itemUpdate = $itemUpdate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->toTable) {
            case Employee::class:
                $this->importToTableEmployee();
                break;
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
        unlink(Storage::path($this->pathFile));
        Log::error($exception->getMessage());
    }

    public function importToTableEmployee()
    {
//        ini_set('memory_limit', '-1');
        //Storage::put(TEMP_DISK . "/tmp.txt", " ");
        Common::setInputEncoding(Storage::path($this->pathFile));
        Excel::import(new EmployeeImport, Storage::path($this->pathFile));
        //Storage::put(TEMP_DISK . "/tmp.txt", " ");
        unlink(Storage::path($this->pathFile));
        SyncEmployeesJob::dispatch();
        SyncEmployeeDepartmentJob::dispatch();
        SyncUserJob::dispatch();
    }
}
