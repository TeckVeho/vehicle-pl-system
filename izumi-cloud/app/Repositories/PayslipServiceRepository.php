<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace Repository;

use App\Events\MessageSentEvent;
use App\Jobs\ImportDataToTableJob;
use App\Models\DataConnection;
use App\Models\DataItem;
use App\Models\File;
use Carbon\Carbon;
use Helper\Common;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class PayslipServiceRepository extends BaseRepository
{

    protected $dataConnection;
    protected $dataItem;
    protected $dataContent;

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        return DataConnection::class;
    }

    public function SendPayslipShainData($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');
        $this->sendContentBody();
    }

    public function sendPaymentData($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');
        $this->sendContentPaymentDataBody();
    }

    private function sendContentBody()
    {
        $urlCallApi = API_SEND_TO_PAYSLIP;
        if (App::environment('staging')) {
            $urlCallApi = API_SEND_TO_PAYSLIP_STAGING;
        }
        if (App::environment('production')) {
            $urlCallApi = API_SEND_TO_PAYSLIP_PRODUCTION;
        }

        $dataShain = DataConnection::where('data_code', 'ICL_1006')->first();

        if (!$dataShain) {
            $this->changeStatus('fail', "Data connection 'data_code' not exists");
            return;
        }
        $dataItemFile = DataItem::query()->with('file')
            ->where('status', 'success')
            ->where('created_at', '<=', Carbon::today()->subMonth()->endOfMonth())
            ->where('data_connection_id', $dataShain->id)
            ->orderBy('id', 'desc')->first();

        if (!$dataItemFile) {
            $this->changeStatus('fail', "Data item not exists");
            return;
        }

        if (!$dataItemFile->file) {
            $this->changeStatus('fail', "File not found");
            return;
        }

        if ($dataItemFile->status !== 'success') {
            $this->changeStatus('fail', "Connection time final status data mismatch");
            return;
        }
        $pathFileZipOpen = Storage::disk($dataItemFile->file->file_sys_disk)->path($dataItemFile->file->file_path);
        if ($dataItemFile->file->file_sys_disk === 's3') {
            $pathF = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
            if (!Storage::exists($pathF)) {
                Storage::makeDirectory($pathF);
            }
            Storage::writeStream($pathF . '/' . $dataItemFile->file->file_name, Storage::disk($dataItemFile->file->file_sys_disk)->readStream($dataItemFile->file->file_path));
            $pathFileZipOpen = Storage::path($pathF . '/' . $dataItemFile->file->file_name);
        }

        $zip = new ZipArchive();
        $path_file_unzip = $dataItemFile->file->file_path;
        $file_name_send = $dataItemFile->file->file_name;
        if (($zip->open($pathFileZipOpen) === TRUE) && $dataItemFile->file->file_extension === 'zip') {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileNameInZip = $zip->getNameIndex($i);
                if (Str::contains($fileNameInZip, 'shain.csv')) {
                    $fileContent = $zip->getStreamIndex($i);
                    $fileNameUnzip = $file_name_send = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . 'shain.csv';
                    $pathUnzip = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd') . '/unzip';
                    if (!Storage::exists($pathUnzip)) {
                        Storage::makeDirectory($pathUnzip);
                    }
                    Storage::writeStream($pathUnzip . '/' . $fileNameUnzip, $fileContent);
                    $path_file_unzip = $pathUnzip . '/' . $fileNameUnzip;
                    break;
                }
            }
            $zip->close();
        }
        $file = Storage::get($path_file_unzip);

        $dataContent = $this->dataItem->content;
        $dataContent['file'] = $dataItemFile->file->toArray();
        $this->dataContent = $dataContent;

        $this->file_id = $dataItemFile->file_id;
        $response = Http::timeout(3600)->attach('file', $file, $file_name_send)->withoutVerifying()->post($urlCallApi, [
            'item_id' => $this->dataItem->id
        ]);

        $body = json_decode($response->getBody());
        if (!$response->getBody()) {
            $body = json_decode($response->json());
        }
        if ($response->getStatusCode() !== 200) {
            $this->changeStatus('fail', $response->getStatusCode(), $body, 'Connection API error');
        } else {
            if (isset($body->error)) {
                $this->changeStatus('fail', $body->error, $body);
            } else {
                //comment and change status with api change_status
                $this->changeStatus('excluding', null, $body, null, $dataItemFile->file_id);

            }
        }
        $response->close();
        if (Storage::exists($pathFileZipOpen)) {
            Storage::delete($pathFileZipOpen);
        }
        if (Storage::exists($path_file_unzip)) {
            Storage::delete($path_file_unzip);
        }
    }

    private function sendContentPaymentDataBody()
    {
        $urlCallApi = API_PAYSLIP_PAYMENT;
        if (App::environment('staging')) {
            $urlCallApi = API_PAYSLIP_PAYMENT_STAGING;
        }
        if (App::environment('production')) {
            $urlCallApi = API_PAYSLIP_PAYMENT_PRODUCTION;
        }

        $dataPayment = DataConnection::where('data_code', 'ICL_1009')->first();

        if (!$dataPayment) {
            $this->changeStatus('fail', "Data connection 'data_code' not exists");
            return;
        }

        $dataItemFile = DataItem::with('file')
            ->where('status', 'success')
            ->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()])
            ->where('data_connection_id', $dataPayment->id)
            ->orderBy('id', 'desc')->first();

        if (!$dataItemFile) {
            $this->changeStatus('fail', "Connection time final status data mismatch");
            return;
        }

        if (!$dataItemFile->file) {
            $this->changeStatus('fail', "File not found");
            return;
        }

        $file = Storage::disk($dataItemFile->file->file_sys_disk)->get($dataItemFile->file->file_path);

        $dataContent = $this->dataItem->content;
        $dataContent['file'] = $dataItemFile->file->toArray();
        $this->dataContent = $dataContent;

        $response = Http::timeout(60)->attach('file', $file, $dataItemFile->file->file_name)->withoutVerifying()->post($urlCallApi, [
            'item_id' => $this->dataItem->id
        ]);

        $body = json_decode($response->getBody());
        if (!$response->getBody()) {
            $body = json_decode($response->json());
        }
        if ($response->getStatusCode() !== 200) {
            $this->changeStatus('fail', $response->getStatusCode(), $body, 'Connection API error');
        } else {
            if (isset($body->error)) {
                $this->changeStatus('fail', $body->error, $body);
            } else {
                //comment and change status with api change_status
                $this->changeStatus('excluding', null, $body, null, $dataItemFile->file_id);
            }
        }
        $response->close();
    }

    private function changeStatus($status, $msgError = null, $msgRes = null, $msg = 'Internal error', $fileId = null)
    {
        if ($this->dataConnection) {
            $this->dataConnection->final_status = $status;
            $this->dataConnection->save();
        }

        $this->dataItem->status = $status;
        $this->dataItem->type = 'active';
        $this->dataItem->data_connection_history = $this->dataConnection->toArray();
        if ($this->dataContent) {
            $this->dataItem->content = $this->dataContent;
        }
        if ($fileId) {
            $this->dataItem->file_id = $fileId;
        }
        if ($msgError) {
            $this->dataItem->msg_error = ["message" => $msg, "message_detail" => $msgError];
        }
        if ($msgRes) {
            $this->dataItem->response_body = $msgRes;
        }
        $this->dataItem->save();
        event(new MessageSentEvent($this->dataConnection, $this->dataItem));
    }
}
