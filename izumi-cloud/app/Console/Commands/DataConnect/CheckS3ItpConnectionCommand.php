<?php

namespace App\Console\Commands\DataConnect;

use Aws\S3\S3Client;
use Illuminate\Console\Command;

class CheckS3ItpConnectionCommand extends Command
{
    protected $signature = 's3-itp:check';

    protected $description = 'Kiểm tra kết nối và credentials S3 ITP (bucket cấu hình trong .env)';

    public function handle(): int
    {
        $this->info('Đang kiểm tra cấu hình S3 ITP...');

        $bucket = config('filesystems.disks.s3_itp.bucket');
        $region = config('filesystems.disks.s3_itp.region');
        $key = config('filesystems.disks.s3_itp.key');
        $secret = config('filesystems.disks.s3_itp.secret');

        if (empty($bucket) || empty($region)) {
            $this->error('Thiếu AWS_BUCKET_ITP hoặc AWS_DEFAULT_REGION_ITP trong .env');

            return self::FAILURE;
        }

        $this->line("  Bucket: {$bucket}");
        $this->line("  Region: {$region}");
        $this->line('  Credentials: '.(($key && $secret) ? 'đã cấu hình (key/secret)' : 'chưa cấu hình (sẽ dùng instance profile nếu chạy trên EC2)'));

        try {
            $client = $this->createS3Client();
            $client->headBucket(['Bucket' => $bucket]);
            $this->newLine();
            $this->info('Kết nối S3 ITP thành công. Credentials và quyền truy cập bucket ổn.');

            $result = $client->listObjectsV2([
                'Bucket' => $bucket,
                'MaxKeys' => 1,
            ]);
            $this->info('Thao tác list objects (listObjectsV2) thực hiện được.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Lỗi kết nối S3 ITP:');
            $this->error($e->getMessage());

            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    private function createS3Client(): S3Client
    {
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

        return new S3Client($config);
    }
}
