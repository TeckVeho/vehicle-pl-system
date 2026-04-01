<?php

namespace App\Jobs;

use App\Models\S3File;
use Aws\S3\S3Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchS3FolderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $folder;
    private $s3Client = null;

    public function __construct($folder)
    {
        $this->folder = $folder;
    }

    public function handle()
    {
        try {
            $this->getS3Client();
            Log::info("Start processing folder: {$this->folder}");

            $continuationToken = null;
            $totalProcessed = 0;
            $pageNumber = 1;

            do {
                $params = [
                    'Bucket' => config('filesystems.disks.s3_itp.bucket'),
                    'Prefix' => "{$this->folder}",
                ];

                if ($continuationToken) {
                    $params['ContinuationToken'] = $continuationToken;
                }

                $result = $this->s3Client->listObjectsV2($params);

                if (!empty($result['Contents'])) {
                    $objects = $result['Contents'];

                    $filtered = array_filter($objects, function ($object) {
                        return strpos($object['Key'], 'Report') !== false;
                    });

                    if (!empty($filtered)) {
                        $processedCount = $this->processAndInsertFiles($filtered);
                        $totalProcessed += $processedCount;
                        Log::info("Page {$pageNumber}: Processed {$processedCount} files from folder: {$this->folder}");
                    }
                }

                $continuationToken = $result['IsTruncated'] ? $result['NextContinuationToken'] : null;
                $pageNumber++;

                if ($continuationToken) {
                    sleep(5);
                    Log::info('Pausing 5 seconds before processing next page...');
                }

            } while ($continuationToken);

            Log::info("Completed folder: {$this->folder}. Total processed: {$totalProcessed} files");
        } catch (\Exception $exception) {
            Log::error("FetchS3FolderJob error in folder: {$this->folder}", [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            throw $exception;
        }
    }

    private function processAndInsertFiles($files)
    {
        $allKeys = array_map(fn($file) => $file['Key'], $files);
        $existingPaths = S3File::whereIn('path', $allKeys)->pluck('path')->toArray();
        $newFiles = array_filter($files, function ($file) use ($existingPaths) {
            return !in_array($file['Key'], $existingPaths);
        });

        if (empty($newFiles)) {
            return 0;
        }

        $recordsToInsert = [];

        foreach ($newFiles as $file) {
            $recordsToInsert[] = [
                'folder' => basename($this->folder),
                'name' => basename($file['Key']),
                'path' => $file['Key'],
                'status' => S3File::STATUS_PENDING,
                'last_modified' => $file['LastModified']->format('Y-m-d H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($recordsToInsert, 1000) as $chunk) {
            S3File::insert($chunk);
        }

        return count($newFiles);
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

    public function failed(Throwable $exception)
    {
        Log::error("FetchS3FolderJob failed for folder: {$this->folder}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
