<?php

namespace App\Console\Commands;

use App\Jobs\AclConfirmJob;
use Illuminate\Console\Command;

class CheckAclCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:re-check-acl';

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
        AclConfirmJob::dispatch();
    }
}
