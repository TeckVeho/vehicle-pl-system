<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Imports\LineworkBotMessageImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportLineworkBotMessage implements ShouldQueue
{
    use Queueable;

    protected $file;
    /**
     * Create a new job instance.
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Excel::import(new LineworkBotMessageImport(), $this->file);
    }
}
