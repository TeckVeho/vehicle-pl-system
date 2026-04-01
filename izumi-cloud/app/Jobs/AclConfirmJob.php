<?php

namespace App\Jobs;

use App\Models\DataConnection;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Contracts\AlcoholConfirmRepositoryInterface;
use Repository\AlcoholConfirmRepository;

class AclConfirmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $aclCheckRepository;
    public $timeout = 60000;

    public function __construct()
    {
        $this->aclCheckRepository = new AlcoholConfirmRepository();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->aclCheckRepository->mails();
        $date = Carbon::now()->format('Y-m-d');
        $dataConnection = DataConnection::query()->where('data_code', 'ICL_1011')->first();
        if ($dataConnection) {
            DataConnectionJob::dispatch($dataConnection->id, $date);
        }
    }
}
