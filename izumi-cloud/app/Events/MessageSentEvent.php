<?php

namespace App\Events;

use App\Models\DataItem;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use App\Jobs\MailJob;
use App\Models\MailLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $content;
    public $app_env;
    public $data_log;

    public function __construct($content = null, $dataLog = null)
    {
        $this->app_env = App::environment();
        if ($content) {
            if (in_array($content->final_status, ['waiting', 'excluding'])) {
                $dataItemChk = DataItem::select('data_connection_id as id', 'status as final_status', 'created_at as final_connect_time')
                    ->where('data_connection_id', $content->id)->whereNull('msg_error')
                    ->whereIn('status', ['waiting', 'excluding'])
                    ->orDerby('id', 'desc')->first();
                if ($dataItemChk) {
                    $content = $dataItemChk;
                }
            } else if ($content->final_status == "fail") {
                $user = $content->supervisor;
                $from = $content->fromSystem;
                $to = $content->toSystem;
                $dataLogSaved = null;
                if ($dataLog) {
                    $dataLogSaved = json_encode($dataLog->msg_error);
                }
                $mailLog = MailLog::create([
                    'data_name' => null,
                    'from_name' => null,
                    'to_name' => null,
                    'supervior_email' => null,
                    'exception' => $dataLogSaved
                ]);
                if ($user && $from && $to) {
                    $emailJob = MailJob::dispatch($user->supervisor_email, $from->name, $to->name, $content->updated_at, $dataLogSaved, $content->name, $mailLog->id);
                    // $emailJob = new MailJob($user->supervisor_email, $from->name, $to->name, $content->updated_at);
                    // $job = $this->dispatch($emailJob);
                    // return $job;
                }
            }
            $contentArr = [
                'id' => $content->id,
                'final_status' => $content->final_status,
                'final_connect_time' => Carbon::parse($content->final_connect_time)->format('Y/m/d H:i'),
            ];
            $this->content = $contentArr;
        }
        if ($dataLog) {
            $dataLogArr = [
                'id' => $dataLog->id,
                'status' => $dataLog->status,
                'created_at' => Carbon::parse($dataLog->created_at)->format('Y/m/d H:i'),
            ];
            $this->data_log = $dataLogArr;
        }

        if ($content->final_status && $dataLog && $dataLog->url_callback && in_array($content->final_status, ['fail', 'success'])) {
            $urlCallBack = $dataLog->url_callback . '&status=' . $dataLog->status . '&type=' . $content->data_code;
            try {
                $res = Http::timeout(60)->withoutVerifying()->get($urlCallBack);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function broadcastOn()
    {
        return ['data_connection_channel'];
    }

    public function broadcastAs()
    {
        return 'data_connection_event';
    }
}
