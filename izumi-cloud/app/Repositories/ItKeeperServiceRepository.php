<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace Repository;

use App\Events\MessageSentEvent;
use App\Models\DataConnection;
use App\Models\DataItem;
use App\Models\File;
use Carbon\CarbonPeriod;
use Helper\Common;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Repository\ItKeeperServiceWorkShifRepository;

class ItKeeperServiceRepository extends BaseRepository
{

    protected $dataConnection;
    protected $dataItem;
    protected $dataContent;
    protected $type = 'active';
    protected $dateKeeper;
    protected $dateKeeperStart;
    protected $updateItDate = true;
    protected $errorIT = null;
    protected $previousConnectionData = [];
    protected $itKeeperServiceWorkShifRepository;
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->itKeeperServiceWorkShifRepository = new ItKeeperServiceWorkShifRepository($app);
    }

    public function model()
    {
        return DataConnection::class;
    }

    public function AlcV2GetByApi($dataConnection, $dataItem)
    {
        Log::info("AlcV2 started at: " . Carbon::now()->toDateTimeString());
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');
        $this->initPreviousDataCheck();
        $this->ItV2();
        $date = Carbon::now();
        $this->itKeeperServiceWorkShifRepository->AlcV2GetByApi($date);
        if (!$this->errorIT) {
            $this->changeStatus('success');
        }

        Log::info("AlcV2 End at: " . Carbon::now()->toDateTimeString());
    }


    private function ItV2($date = null)
    {
        Log::info("IT V2 started at: " . Carbon::now()->toDateTimeString());
        if (!$date) {
            $date = @trim(file_get_contents(storage_path("itv2-date.txt")));
            $this->dateKeeperStart = $date;
            $startDate = Carbon::parse($this->dateKeeperStart)->subDays(2);
            $dateCf = Carbon::now();
        } else {
            $this->dateKeeperStart = $date;
            $startDate = Carbon::parse($this->dateKeeperStart)->subDays(2);
            $dateCf = Carbon::parse($this->dateKeeperStart);
        }

        $result = CarbonPeriod::create($startDate, '1 day', $dateCf);

        foreach ($result as $dt) {
            $keySigna = Config::get('common.itv2_keeper_key');
            $domain_it_keeper = ITV2_KEEPER_URL_API;
            $dateStr = $dt->format('Y/m/d');
            $time = Carbon::now()->timestamp;
            $env = App::environment();
            $urlSigna = "/apitenkos/get?action=1&time=$time&company=hmftphYm&office=&employee=&tenkoDate=$dateStr&env=$env";
            $signature = hash_hmac('sha256', $urlSigna, $keySigna);

            $url = $domain_it_keeper . $urlSigna . "&signature=$signature";
            $response = Http::timeout(60)->get($url)->json();

            if (Arr::get($response, 'dataCount') > 0) {
                foreach (Arr::get($response, 'data') as $key => $value) {
                    if (Arr::get($value, 'mae_tenkoResult', 'NG') == 'OK' || Arr::get($value, 'ato_tenkoResult', 'NG') == 'OK') {
                        $employeeCd = intval($value['employeeCd']);
                        if (!$employeeCd || $employeeCd <= 0) {
                            $employeeCd = (int)Arr::get(explode('_', $value['employeeName']), 1);
                        }

                        if (!empty(Arr::get($value, 'mae_tenkoTime', '')) && $employeeCd) {
                            $dataDate = Carbon::parse($value['mae_tenkoTime'])->format('Y-m-d H:i:s');
                            $codeCheck = $employeeCd . Carbon::parse($dataDate)->timestamp;
                            if (!in_array($codeCheck, $this->previousConnectionData)) {
                                $this->dataContent['keeper'][] = [
                                    "employee_code" => $employeeCd,
                                    "employee_name" => $value['employeeName'],
                                    "type" => 0,
                                    "date" => $dataDate,
                                ];
                            }
                        }
                        if (!empty(Arr::get($value, 'ato_tenkoTime', '')) && $employeeCd) {
                            $dataDate = Carbon::parse($value['ato_tenkoTime'])->format('Y-m-d H:i:s');
                            $codeCheck = $employeeCd . Carbon::parse($dataDate)->timestamp;
                            if (!in_array($codeCheck, $this->previousConnectionData)) {
                                $this->dataContent['keeper'][] = [
                                    "employee_code" => $employeeCd,
                                    "employee_name" => $value['employeeName'],
                                    "type" => 1,
                                    "date" => $dataDate,
                                ];
                            }
                        }
                    } else {
                        $dataNg = [
                            "employee_code" => $value['employeeCd'],
                            "employee_name" => $value['employeeName'],
                            "mae_tenkoTime" => $value['mae_tenkoTime'],
                            "ato_tenkoTime" => $value['ato_tenkoTime'],
                        ];
                        Log::info("Data NG: " . json_encode($dataNg));
                    }
                }

            } else {
                $this->updateItDate = false;
                $this->errorIT[] = Arr::get($response, 'error');
                $this->changeStatus('fail', json_encode(Arr::get($response, 'error')));
                break;
            }
            $this->dateKeeper = $dateStr;
        }

        Log::info("IT V2 End at: " . Carbon::now()->toDateTimeString());
    }

    private function changeStatus($status, $msgError = null, $msgRes = null)
    {
        $contentMailCountDateKeeper = null;
        if ($this->dataConnection) {
            $this->dataConnection->final_status = $status;
            $this->dataConnection->save();
        }

        $this->dataItem->status = $status;
        $this->dataItem->type = $this->type;
        $this->dataItem->data_connection_history = $this->dataConnection->toArray();
        if ($this->dataContent) {
            if ($this->dateKeeper && $status == 'success') {
                $this->dataContent['date_keeper'] = $this->dateKeeperStart . '=>' . $this->dateKeeper;
                $contentMailCountDateKeeper['date_keeper'] = $this->dateKeeperStart . '=>' . $this->dateKeeper;
                if ($this->updateItDate)
                    file_put_contents(storage_path("itv2-date.txt"), $this->dateKeeper);
            }
            if ($contentMailCountDateKeeper) {
                $this->dataItem->content = $contentMailCountDateKeeper;
            }
            $this->storeFileContentData();
        }
        if ($msgError) {
            $this->dataItem->msg_error = ["message" => 'Internal error', "message_detail" => $msgError];
        }
        if ($msgRes) {
            $this->dataItem->response_body = $msgRes;
        }
        $this->dataItem->save();
        event(new MessageSentEvent($this->dataConnection, $this->dataItem));
    }

    private function storeFileContentData($fileNameSrc = 'itv2_data_connection_content.txt')
    {
        $fileName = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_' . $this->dataItem->id . '_' . $fileNameSrc;
        $envBasePath = Common::getEnvBasePath();
        $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        $disk = Common::checkS3Conn() ? 's3' : 'public';

        if (!Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->makeDirectory($path);
        }

        Storage::disk($disk)->put($path . '/' . $fileName, json_encode($this->dataContent));

        $fileData = File::create([
            'file_path' => $path . '/' . $fileName,
            'file_name' => $fileName,
            "file_extension" => pathinfo($fileName, PATHINFO_EXTENSION),
            "file_size" => Storage::disk($disk)->size($path . '/' . $fileName),
            "file_url" => Storage::disk($disk)->url($path . '/' . $fileName),
            "file_sys_disk" => $disk,
        ]);
        if ($fileData) {
            $this->dataItem->file_id = $fileData->id;
        }
    }

    private function initPreviousDataCheck()
    {
        $dataAlcV2 = DataConnection::where('data_code', 'ICL_1034')->first();
        $dataItemContentV2s = DataItem::query()
            ->where('status', 'success')
            ->where('data_connection_id', $dataAlcV2->id)
            ->orderBy('id', 'desc')->take(3)->get();

        foreach ($dataItemContentV2s as $dataItemContentV2) {
            if ($dataItemContentV2 && $dataItemContentV2->file) {
                $dataContentV2 = json_decode(Storage::disk($dataItemContentV2->file->file_sys_disk)->get($dataItemContentV2->file->file_path), true);
                foreach (data_get($dataContentV2, 'keeper') as $item) {
                    $this->previousConnectionData[] = $item['employee_code'] . Carbon::parse($item['date'])->timestamp;
                }
            }
        }
    }
}
