<?php

namespace App\Jobs;

use App\Events\MessageSentEvent;
use App\Models\DataConnection;
use App\Models\DataItem;
use App\Models\System;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use Throwable;

class SaveFileAndExecuteFromAIOCRJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idConnection;
    protected $idItem;
    protected $dataConnection;
    protected $dataItem;
    protected $type;
    public $timeout = 60000;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($idConnection, $idItem)
    {
        $this->idConnection = $idConnection;
        $this->idItem = $idItem;
        $this->dataConnection = DataConnection::find($this->idConnection);
        $this->dataItem = DataItem::find($idItem);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app()->call('\Repository\VehicleServiceRepository@saveFileAndExecuteFromAIOCR', ['dataConnection' => $this->dataConnection, 'dataItem' => $this->dataItem]);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->dataConnection->type = $this->type;
        $this->dataConnection->final_status = 'fail';
        $this->dataConnection->save();

        $this->dataItem->status = 'fail';
        $this->dataItem->type = $this->type;
        $this->dataItem->data_connection_history = $this->dataConnection->toArray();
        $this->dataItem->msg_error = [
            "message" => 'Internal error',
            "message_detail" => $exception->getMessage(),
        ];
        $this->dataItem->save();

        event(new MessageSentEvent($this->dataConnection, $this->dataItem));
    }
}
