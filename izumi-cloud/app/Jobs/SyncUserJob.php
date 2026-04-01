<?php

namespace App\Jobs;


use App\Imports\EmployeeImport;
use App\Models\Employee;
use Helper\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Repository\UserServiceRepository;
use Throwable;

class SyncUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_code;
    protected $repository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_code = null)
    {
        $this->user_code = $user_code;
        $this->repository = new UserServiceRepository();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->repository->syncUser($this->user_code);
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

}
