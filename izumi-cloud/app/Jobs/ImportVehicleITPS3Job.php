<?php

namespace App\Jobs;


use App\Imports\EmployeeImport;
use App\Imports\VehicleCostImport;
use App\Imports\VehicleITPS3Import;
use App\Models\Employee;
use App\Models\S3File;
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

class ImportVehicleITPS3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $indexField;
    private $itpS3;

    public function __construct(S3File $itpS3, $indexField)
    {
        $this->itpS3 = $itpS3;
        $this->indexField = $indexField;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!\Storage::disk('s3_itp')->exists($this->itpS3->path)) {
            Log::warning("File không tồn tại trên S3, bỏ qua import: {$this->itpS3->path}");
            return;
        }

        Common::setInputEncoding($this->itpS3->path, null, 's3_itp');
        Excel::import(new VehicleITPS3Import($this->itpS3, $this->indexField), $this->itpS3->path, 's3_itp');
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
}
