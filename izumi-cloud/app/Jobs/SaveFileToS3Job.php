<?php

namespace App\Jobs;


use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Repository\DriverRecorderRepository;
use Throwable;

class SaveFileToS3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idFile;
    protected $repository;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($idFile = null, $type = null)
    {
        $this->idFile = $idFile;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->saveFileToS3($this->idFile);
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

    private function saveFileToS3($idFile)
    {
        $datFile = File::where('id', $idFile)->whereNull('expired_at')->where('file_sys_disk', 'public')->first();
        if ($datFile && Storage::exists($datFile->file_path)) {
            $deleteFileLocal = Storage::path($datFile->file_path);
            $disk = Storage::disk('s3');
            $dateFolder = date("YmW");
            // Build the file path
            $basePath = 'cloud_dev/';
            if (App::environment('staging')) {
                $basePath = 'cloud_staging/';
            }
            if (App::environment('production')) {
                $basePath = 'cloud_production/';
            }

            $filePath = $basePath . DRIVER_PATH_UPLOAD_FILE_S3 . "/$dateFolder/" . basename(Storage::path($datFile->file_path));

            if ($this->type =='VehicleMaintenanceCost') {
                $filePath = $basePath . VEHICLE_MAINTENANCE_COST_PATH_UPLOAD_FILE_S3 . "/$dateFolder/" . basename(Storage::path($datFile->file_path));
            }
            if ($this->type == 'movie') {
                $filePath = $basePath . MOVIE_PATH_UPLOAD_FILE_S3 . "/$dateFolder/" . basename(Storage::path($datFile->file_path));
            }

            if ($this->type == 'employee') {
                $filePath = $basePath . EMPLOYEE_PATH_UPLOAD_FILE_S3 . "/$dateFolder/" . basename(Storage::path($datFile->file_path));
            }

            if ($this->type == 'news_letter') {
                $filePath = $basePath . NEWS_LETTER_PATH_UPLOAD_FILE_S3 . "/$dateFolder/" . basename(Storage::path($datFile->file_path));
            }


            // It's better to use streaming Streaming (laravel 5.4+)
//            $file = fopen(Storage::path($datFile->file_path), 'r');
            $pathIns3 = $disk->writeStream($filePath, Storage::readStream($datFile->file_path));
//            $disk->put($datFile->file_path, fopen($sourceFile, 'r+'));
            if ($pathIns3) {
                $datFile->file_path = $filePath;
                $datFile->file_url = Storage::disk('s3')->url($filePath);
                $datFile->file_sys_disk = 's3';
                $datFile->save();

                // for older laravel
                // $disk->put($fileName, file_get_contents($file), 'public');
                //mime = str_replace('/', '-', $file->getMimeType());

                // We need to delete the file when uploaded to s3
                unlink($deleteFileLocal);
            }
        }
    }
}
