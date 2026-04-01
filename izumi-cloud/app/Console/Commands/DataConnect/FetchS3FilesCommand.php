<?php

namespace App\Console\Commands\DataConnect;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class FetchS3FilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:s3-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all file all folder from s3';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->getFileS3();
    }

    public function getFileS3()
    {
        $start = microtime(true);
        $this->info("Start Time: " . (microtime(true) - $start));

        $rootDir = 'itp_upstream/';
        $s3 = Storage::disk('s3');
        $listFolder = $s3->directories($rootDir);

        $processes = [];
        $maxProcesses = 2; // Số luồng tối đa chạy đồng thời
        $runningProcesses = 0;

        foreach ($listFolder as $folder) {
            while ($runningProcesses >= $maxProcesses) {
                foreach ($processes as $key => $process) {
                    if (!$process->isRunning()) {
                        unset($processes[$key]);
                        $runningProcesses--;
                    }
                }
                usleep(50000); // Chờ 50ms để giảm tải CPU
            }

            $this->info("Start folder $folder Time: " . date('Y-m-d H:i:s'));

            $process = new Process(['php', 'artisan', 'fetch:s3-folder', $folder]);
            $process->start();

            $processes[] = $process;
            $runningProcesses++;
        }

        // Chờ tất cả tiến trình hoàn thành
        foreach ($processes as $process) {
            $process->wait();
        }

        $end = microtime(true);
        $this->info("All files downloaded successfully!\n");
        $this->info("Time: " . ($end - $start) . "s");
    }
}
