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

class DataConnectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idConnection;
    protected $dataConnection;
    protected $dataItem;
    protected $type;
    public $timeout = 60000;
    protected $date;
    protected $department_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($idConnection, $date, $url_api_callback = null, $department_name = null, $type = 'active')
    {
        $this->idConnection = $idConnection;
        $this->type = $type;
        $this->date = $date;
        $this->department_name = $department_name;

        $this->dataConnection = DataConnection::find($this->idConnection);
        if ($this->dataConnection) {
            $this->dataConnection->final_connect_time = Carbon::now();
            $this->dataConnection->final_status = 'waiting';
            $this->dataConnection->save();
        }
        $this->dataItem = DataItem::create([
            "type" => $this->type,
            "data_connection_id" => $this->dataConnection->id,
            "url_callback" => $url_api_callback
        ]);
        event(new MessageSentEvent($this->dataConnection, $this->dataItem));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app()->call($this->dataConnection->service_class_name, ['dataConnection' => $this->dataConnection, 'dataItem' => $this->dataItem, 'date' => $this->date, 'department_name' => $this->department_name]);
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

    private function getAdminEmail($id)
    {
        $user = User::find($id);
        if ($user) {
            if ($user->email) return $user->email;
        }
        return null;
    }

    private function getSystem($id)
    {
        $system = System::find($id);
        if ($system) {
            return $system->name;
        }
        return null;
    }

}
