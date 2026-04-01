<?php

namespace App\Jobs;


use App\Imports\MaintenanceCostImport;
use Helper\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use ZipArchive;

class ImportMaintCostTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $toTable;
    protected $pathFile;
    protected $pathFileCsv;
    protected $memory_limit;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pathFile)
    {
        $this->pathFile = $pathFile;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $zip = new ZipArchive();
        if ($zip->open(Storage::path($this->pathFile)) === TRUE) {
            $fileContent = $zip->getStreamIndex(0);
            $fileName = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_' . $zip->getNameIndex(0);
            $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd') . '/unzip';
            Storage::writeStream($path . '/' . $fileName, $fileContent);
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path);
            }
            $this->pathFileCsv = Storage::path($path . '/' . $fileName);
//            ini_set('memory_limit', '-1');
            //Storage::put(TEMP_DISK . "/tmp.txt", " ");
            Common::setInputEncoding($this->pathFileCsv);
            Excel::import(new MaintenanceCostImport, $this->pathFileCsv);
            //Storage::put(TEMP_DISK . "/tmp.txt", " ");
            unlink($this->pathFileCsv);
            $zip->close();
        }
        unlink(Storage::path($this->pathFile));
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        if ($this->pathFileCsv) {
            unlink($this->pathFileCsv);
        }
        unlink(Storage::path($this->pathFile));
        Log::error($exception->getMessage());
    }
}
