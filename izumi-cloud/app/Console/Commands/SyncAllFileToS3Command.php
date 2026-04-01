<?php

namespace App\Console\Commands;

use App\Models\DataItem;
use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class SyncAllFileToS3Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:all_file_data_connection_to_s3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $basePath = 'cloud_dev/';
        if (App::environment('staging')) {
            $basePath = 'cloud_staging/';
        }
        if (App::environment('production')) {
            $basePath = 'cloud_production/';
        }

        $allFileInDataItem = DataItem::query()->whereNotNull('file_id')->pluck('file_id')->toArray();

        $datFiles = File::query()->whereNull('expired_at')->where('file_name', 'like', '%shain.csv%')
            ->whereIn('id', $allFileInDataItem)
            ->whereNull('file_sys_disk')->get();
        foreach ($datFiles as $datFile) {
            try {
                if ($datFile && Storage::exists($datFile->file_path)) {
                    $deleteFileLocal = Storage::path($datFile->file_path);
                    $zipPath = pathinfo($datFile->file_path, PATHINFO_DIRNAME) . '/' . pathinfo($datFile->file_path, PATHINFO_FILENAME) . '.zip';
                    $s3Path = $basePath . $zipPath;
                    $disk = Storage::disk('s3');

                    $zip = new ZipArchive();

                    if ($zip->open(Storage::path($zipPath), ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                        $zip->addFile($deleteFileLocal, basename($deleteFileLocal));
                        $zip->close();
                        $disk->writeStream($s3Path, Storage::readStream($zipPath));
                        if ($disk->exists($s3Path)) {
                            $datFile->file_path = $s3Path;
                            $datFile->file_url = $disk->url($s3Path);
                            $datFile->file_sys_disk = 's3';
                            $datFile->file_name = pathinfo($deleteFileLocal, PATHINFO_FILENAME) . '.zip';
                            $datFile->file_extension = 'zip';
                            $datFile->file_size = $disk->size($s3Path);
                            $check_save = $datFile->save();
                            if ($check_save) {
                                unlink($deleteFileLocal);
                                unlink(Storage::path($zipPath));
                            } else {
                                Log::info('Sync shain file to s3(not save to db) =>' . $datFile->id);
                            }
                        } else {
                            Log::info('Sync shain file to s3(not put file to s3) =>' . $datFile->id);
                        }
                    } else {
                        Log::info('Sync shain file to s3(not create zip) =>' . $datFile->id);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Sync shain file to s3(Exception) =>' . $datFile->id . $e->getMessage());
                continue;
            }
        }

        $datFiles2 = File::query()
            ->whereNull('expired_at')
            ->whereIn('id', $allFileInDataItem)
            ->whereNull('file_sys_disk')->get();
        foreach ($datFiles2 as $datFile2) {
            try {
                if ($datFile2 && Storage::exists($datFile2->file_path)) {
                    $deleteFileLocal = Storage::path($datFile2->file_path);
                    $disk = Storage::disk('s3');

                    $s3Path = $basePath . $datFile2->file_path;
                    $disk->writeStream($s3Path, Storage::readStream($datFile2->file_path));
                    if ($disk->exists($s3Path)) {
                        $datFile2->file_path = $s3Path;
                        $datFile2->file_url = $disk->url($s3Path);
                        $datFile2->file_sys_disk = 's3';
                        $check = $datFile2->save();
                        if ($check) {
                            unlink($deleteFileLocal);
                        } else {
                            Log::info('Sync file to s3(not save to db) =>' . $datFile2->id);
                        }
                    } else {
                        Log::info('Sync file to s3(not put file to s3) =>' . $datFile2->id);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Sync file to s3(Exception) =>' . $datFile2->id . $e->getMessage());
                continue;
            }
        }

        return 0;
    }
}
