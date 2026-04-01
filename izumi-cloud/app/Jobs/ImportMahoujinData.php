<?php

namespace App\Jobs;

use App\Imports\MahoujinDataImport;
use Helper\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportMahoujinData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $memory_limit;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
        unlink(Storage::path($this->filePath));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        ini_set('memory_limit', '-1');
        Common::setInputEncoding(Storage::path($this->filePath));
        Excel::import(new MahoujinDataImport(), $this->filePath);
        unlink(Storage::path($this->filePath));
    }
}
