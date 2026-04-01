<?php

namespace App\Jobs;

use App\Events\MessageSentEvent;
use App\Imports\MahoujinDataImport;
use App\Imports\PCADataImport;
use App\Models\Department;
use Carbon\Carbon;
use Helper\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportPCAData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $memory_limit;
    protected $filePathPcaTmpName;
    protected $dataConnection;
    protected $dataItem;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filePath, $dataConnection, $dataItem)
    {
        $this->filePath = $filePath;
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
        $this->changeStatus('fail', $exception->getMessage());
        unlink(Storage::path($this->filePath));
        unlink(Storage::path($this->filePathPcaTmpName));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $filePcaTmpName = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_pca_tmp.json';
        $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
        }
        Storage::put($path . '/' . $filePcaTmpName, "");
        $this->filePathPcaTmpName = $path . '/' . $filePcaTmpName;

//        ini_set('memory_limit', '-1');
        $allDp = Department::query()->get();
        Common::setInputEncoding(Storage::path($this->filePath));
        Excel::import(new PCADataImport($allDp, $this->filePathPcaTmpName), $this->filePath);
        unlink(Storage::path($this->filePath));
        unlink(Storage::path($this->filePathPcaTmpName));

        $this->changeStatus('success');
    }

    private function changeStatus($status, $msgError = null, $msgRes = null, $fileId = null)
    {
        if ($this->dataConnection) {
            if (in_array($status, ['excluding', 'waiting'])) {
                $this->dataConnection->final_connect_time = Carbon::now();
            }
            $this->dataConnection->final_status = $status;
            $this->dataConnection->save();
        }

        if ($this->dataItem) {
            $this->dataItem->status = $status;
            $this->dataItem->type = $this->dataConnection->type;
            $this->dataItem->data_connection_history = $this->dataConnection->toArray();
            if ($fileId) {
                $this->dataItem->file_id = $fileId;
            }
            if ($msgError) {
                $this->dataItem->msg_error = ["message" => 'Internal error', "message_detail" => $msgError];
            }
            if ($msgRes) {
                $this->dataItem->response_body = $msgRes;
            }
            $this->dataItem->save();
        }
        event(new MessageSentEvent($this->dataConnection, $this->dataItem));
    }
}
