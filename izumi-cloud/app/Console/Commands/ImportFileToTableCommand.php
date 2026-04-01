<?php

namespace App\Console\Commands;

use App\Jobs\DataConnectionJob;
use App\Jobs\ImportDataToTableJob;
use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Repository\DataConnectionRepository;

class ImportFileToTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:FileToTable {to_table} {path_file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        ImportDataToTableJob::dispatch($this->argument('to_table'), $this->argument('path_file'), null);
    }
}
