<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace Repository;

use App\Events\MessageSentEvent;
use App\Imports\MaintenanceCostImport;
use App\Jobs\ImportDataToTableJob;
use App\Jobs\ImportMaintCostTableJob;
use App\Models\DataConnection;
use App\Models\DataItem;
use App\Models\File;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Helper\Common;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class MaintenanceServiceRepository extends BaseRepository
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

    public function SendMaintenanceVehicleData($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');
        $this->sendContentBody();
    }

    public function GetMaintenanceVehicleData($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');
        $this->getMaintenanceCostData();
    }


    private function sendContentBody()
    {
        $urlCallApi = API_SEND_TO_MAINTENANCE;
        if (App::environment('staging')) {
            $urlCallApi = API_SEND_TO_MAINTENANCE_STAGING;
        }
        if (App::environment('production')) {
            $urlCallApi = API_SEND_TO_MAINTENANCE_PRODUCTION;
        }

        $dataVehicle = DataConnection::where('data_code', 'ICL_1012')->first();
        $dataLease = DataConnection::where('data_code', 'ICL_1013')->first();

        if (!$dataVehicle || !$dataLease) {
            $this->changeStatus('fail', "Data connection 'data_code' not exists");
            return;
        }

        $dataItemVehicle = DataItem::with('file')->where('status', 'success')
            ->where('data_connection_id', $dataVehicle->id)->orderBy('id', 'desc')->first();
        $dataItemLease = DataItem::with('file')->where('status', 'success')
            ->where('data_connection_id', $dataLease->id)->orderBy('id', 'desc')->first();

        if (!$dataItemVehicle || !$dataItemLease) {
            $this->changeStatus('fail', "Data item not exists");
            return;
        }

        if (!$dataItemVehicle->file || !$dataItemLease->file) {
            $this->changeStatus('fail', "File not found");
            return;
        }

        // Zip File Name
        $zipFileName = 'vehicle-data.zip';
        if (!Storage::disk()->exists(PATH_ZIP_FILE)) {
            Storage::disk()->makeDirectory(PATH_ZIP_FILE);
        } else {
            Storage::disk()->delete(Storage::path(PATH_ZIP_FILE . '/' . $zipFileName));
        }
        // Create ZipArchive Obj
        $zip = new ZipArchive;
        if ($zip->open(Storage::path(PATH_ZIP_FILE . '/' . $zipFileName), ZipArchive::CREATE) === TRUE) {
            $zip->addFile(Storage::path($dataItemVehicle->file->file_path), 'vehicle-list.csv');
            $zip->addFile(Storage::path($dataItemLease->file->file_path), 'maintenance-lease-data.csv');
            $zip->close();
        }

        $file = fopen(Storage::path(PATH_ZIP_FILE . '/' . $zipFileName), 'r');

        $dataContent['file'] = [
            $dataItemVehicle->file->file_path,
            $dataItemLease->file->file_path
        ];

        $this->dataContent = $dataContent;

        //call api
        $response = Http::timeout(3600)->attach('file', $file, $zipFileName)->withoutVerifying()->post($urlCallApi, [
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
                //$this->changeStatus('success', null, $body);
            }
        }
        $response->close();
    }

    private function getMaintenanceCostData()
    {
        $Ymd = Carbon::now()->format('Y-m-d');
        $urlCallApi = API_GET_MAINTENANCE . $Ymd;
        if (App::environment('staging')) {
            $urlCallApi = API_GET_MAINTENANCE_STAGING . $Ymd;
        }
        if (App::environment('production')) {
            $urlCallApi = API_GET_MAINTENANCE_PRODUCTION . $Ymd;
        }

        $envBasePath = Common::getEnvBasePath();
        $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        if (!Storage::disk()->exists($path)) {
            Storage::disk()->makeDirectory($path);
        }
        $fileName = md5(Str::uuid()->toString()) . 'maintenance_cost_data.zip';
        $pathZip = Storage::path($path) . '/' . $fileName;
        $response = Http::timeout(3600)->withoutVerifying()->sink($pathZip)->get($urlCallApi);

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
                $paths3 = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
                $disk = Common::checkS3Conn() ? 's3' : 'public';

                Storage::disk($disk)->writeStream($paths3 . '/' . $fileName, Storage::disk()->readStream($path . '/' . $fileName));
                $fileData = File::create([
                    'file_path' => $paths3 . '/' . $fileName,
                    'file_name' => $fileName,
                    "file_extension" => 'zip',
                    "file_size" => Storage::disk($disk)->size($paths3 . '/' . $fileName),
                    "file_url" => Storage::disk($disk)->url($paths3 . '/' . $fileName),
                    "file_sys_disk" => $disk,
                ]);
                ImportMaintCostTableJob::dispatch($path . '/' . $fileName);
                $this->changeStatus('success', null, $body, null, $fileData->id);
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
