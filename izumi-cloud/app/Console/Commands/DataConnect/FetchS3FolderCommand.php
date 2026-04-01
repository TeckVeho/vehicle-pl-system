<?php

namespace App\Console\Commands\DataConnect;

use App\Models\S3File;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FetchS3FolderCommand extends Command
{
    protected $signature = 'fetch:s3-folder {folder}';
    protected $description = 'Fetch files from a specific S3 folder';
    private $s3Client = null;

    public function handle()
    {
        try {
            $folder = $this->argument('folder');
            $this->getS3Client();
            $listFileFromS3 = $this->getFileFromS3($folder);
            Log::info("Processing folder: $folder");

            if (!$listFileFromS3) {
                Log::warning("No file found in S3 for folder: $folder");
                return;
            }

            // Collect all keys from S3 files
            $allKeys = array_map(fn($file) => $file['Key'], $listFileFromS3);

            // Get existing file paths from DB in one query
            $existingPaths = S3File::whereIn('path', $allKeys)->pluck('path')->toArray();

            // Filter out files that already exist
            $newFiles = array_filter($listFileFromS3, function ($file) use ($existingPaths) {
                return !in_array($file['Key'], $existingPaths);
            });

            $recordsToInsert = [];

            foreach ($newFiles as $file) {
                // Get the file content from S3 and store locally
                $object = $this->s3Client->getObject([
                    'Bucket' => config('filesystems.disks.s3_itp.bucket'),
                    'Key' => $file['Key'],
                ]);
                $fileContent = $object['Body']->getContents();
                Storage::disk('local')->put($file['Key'], $fileContent);

                $recordsToInsert[] = [
                    'folder' => basename($folder),
                    'name' => basename($file['Key']),
                    'path' => $file['Key'],
                    'status' => S3File::STATUS_PENDING,
                    'last_modified' => $file['LastModified']->format('Y-m-d H:i:s'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            // Insert in chunks of 1000
            foreach (array_chunk($recordsToInsert, 1000) as $chunk) {
                S3File::insert($chunk);
            }

            Log::info("Completed folder: $folder");
        } catch (\Exception $exception) {
            Log::error("FetchS3FolderCommand error in folder: $folder", [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
        }
    }

    public function getFileFromS3($folder)
    {
        try {
            $dates = [
                Carbon::now()->format('Ymd'),
                Carbon::yesterday()->format('Ymd')
            ];
            foreach ($dates as $date) {
                $result = $this->s3Client->listObjectsV2([
                    'Bucket' => config('filesystems.disks.s3_itp.bucket'),
                    'Prefix' => "$folder",
                ]);
                if (!empty($result['Contents'])) {
                    $objects = $result['Contents'];
                    // Lọc chỉ giữ lại file có chứa "Report" trong tên
                    $filtered = array_filter($objects, function ($object) {
                        return strpos($object['Key'], 'Report') !== false;
                    });
                    return $filtered;
                }
            }
            return false;
        } catch (\Exception $exception) {
            Log::error("Error fetching last file from S3 for folder: $folder", [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
        }
        return false;
    }

    private function getS3Client(): S3Client
    {
        if ($this->s3Client !== null) {
            return $this->s3Client;
        }

        $config = [
            'version' => 'latest',
            'region' => config('filesystems.disks.s3_itp.region'),
        ];

        $key = config('filesystems.disks.s3_itp.key');
        $secret = config('filesystems.disks.s3_itp.secret');
        if ($key !== null && $key !== '' && $secret !== null && $secret !== '') {
            $config['credentials'] = [
                'key' => $key,
                'secret' => $secret,
            ];
        }

        $this->s3Client = new S3Client($config);

        return $this->s3Client;
    }
}
