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
use App\Repositories\Contracts\KingOfTimeServiceRepositoryInterface;
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
use Illuminate\Support\Facades\Mail;

class KingOfTimeServiceRepository extends BaseRepository implements KingOfTimeServiceRepositoryInterface
{
    protected $dataConnection;
    protected $dataItem;
    protected $dataContent;
    protected $type = 'active';
    protected $mailCount;
    protected $mailCountStart;
    protected $dateKeeper;
    protected $dateKeeperStart;
    protected $updateItDate = true;
    protected $date;

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        return DataConnection::class;
    }

    public function AlcGetEmployeeKingOfTime($dataConnection, $dataItem, $date)
    {
        Log::info("AlcGetEmployeeKingOfTime started at: " . Carbon::now()->toDateTimeString());
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->date = $date;
        $this->changeStatus('excluding');
        $this->getEmployeeKingOfTime();
        $this->changeStatus('success', null, null, 'dtc_king_time.txt');
        Log::info("Crawling End at: " . Carbon::now()->toDateTimeString());
    }

    public function sendDataKingOfTimeToTimesheet($dataConnection, $dataItem, $date)
    {
        Log::info("sendDataKingOfTimeToTimesheet started at: " . Carbon::now()->toDateTimeString());
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;

        $urlCallApi = BASE_URL_IZUMI;
        if (App::environment('staging')) {
            $urlCallApi = BASE_URL_IZUMI_STAGE;
        }
        if (App::environment('production')) {
            $urlCallApi = BASE_URL_IZUMI_PRODUCTION;
        }
        $urlCallApi = $urlCallApi . TIMESHEET_API_SYNC_KING_TIME;

        $dataAlcKingTime = DataConnection::where('data_code', 'ICL_1030')->first();

        if (!$dataAlcKingTime) {
            $this->changeStatus('fail', "Data connection 'data_code' not exists");
            return;
        }

        $dataItemContent = DataItem::query()
            ->where('status', 'success')
            ->where('data_connection_id', $dataAlcKingTime->id)
            ->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()])
            ->orderBy('id', 'desc')->first();

        if (!$dataItemContent) {
            $this->changeStatus('fail', "Data item not exists");
            return;
        }

        if ($dataItemContent->status !== 'success') {
            $this->changeStatus('fail', "Connection time final status data mismatch");
            return;
        }

        if ($dataItemContent->file) {
            $dataContent = json_decode(Storage::disk($dataItemContent->file->file_sys_disk)->get($dataItemContent->file->file_path), true);
            $this->dataItem->file_id = $dataItemContent->file->id;
        } else {
            $dataContent = $dataItemContent->content;
        }

        $response = Http::timeout(3600)->withoutVerifying()
            ->post($urlCallApi,
                [
                    'king_time' => json_encode(Arr::get($dataContent, 'king_time')),
                ]
            );
        $body = json_decode($response->getBody());
        if ($response->getStatusCode() !== 200) {
            $this->changeStatus('fail', $response->getStatusCode() . ':' . @$response->throw()->json(), $body);
        } else {
            if (isset($body->error)) {
                $this->changeStatus('fail', $body->error, $body);
            } else {
                $this->changeStatus('success', null, $body);
            }
        }
    }

    private function getEmployeeKingOfTime()
    {
        $confKingOfTime = Config::get('king_of_time_conf');
        $headers = ['Authorization' => 'Bearer ' . Arr::get($confKingOfTime, 'token')];
        $listEmpInKingOfTime = Http::timeout(60)->withHeaders($headers)->get(KINGTIME_API_DAILY_WORKINGS_TIMERECORD, [
            'additionalFields' => 'currentDateEmployee',
            'start' => Carbon::now()->subDay()->format('Y-m-d'),
            'end' => Carbon::now()->format('Y-m-d'),
        ])->json();
        $error = data_get($listEmpInKingOfTime, 'errors', null);
        if ($error) {
            $this->changeStatus('fail', $error, $listEmpInKingOfTime);
        } elseif ($listEmpInKingOfTime && (count($listEmpInKingOfTime) <= 0 || count(data_get($listEmpInKingOfTime, '0.dailyWorkings')) <= 0)) {
            $this->changeStatus('fail', 'Data king of time is empty', $listEmpInKingOfTime);
        } else {
            foreach ($listEmpInKingOfTime as $datas) {
                foreach (data_get($datas, 'dailyWorkings') as $data) {
                    $emp_code = data_get($data, 'currentDateEmployee.code');
                    $lastName = data_get($data, 'currentDateEmployee.lastName');
                    $firstName = data_get($data, 'currentDateEmployee.firstName');
                    $lsTimeRecord = data_get($data, 'timeRecord', []);
                    foreach ($lsTimeRecord as $timeRc) {
                        $this->dataContent['king_time'][] = [
                            'employee_code' => $emp_code,
                            'employee_name' => $lastName . '　' . $firstName,
                            'date' => Carbon::parse(Arr::get($timeRc, 'time', null))->toDateTimeString(),
                            'type' => (int)Arr::get($timeRc, 'code', 1) - 1,
                        ];
                    }
                }
            }
        }
    }

    private function changeStatus($status, $msgError = null, $msgRes = null, $fileStore = null)
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
            if ($this->mailCount && $status == 'success') {
                $this->dataContent['mail_count'] = $this->mailCountStart . '=>' . $this->mailCount;
                $contentMailCountDateKeeper['mail_count'] = $this->mailCountStart . '=>' . $this->mailCount;
                file_put_contents(storage_path("mail-count.txt"), $this->mailCount);
            }
            if ($this->dateKeeper && $status == 'success') {
                $this->dataContent['date_keeper'] = $this->dateKeeperStart . '=>' . $this->dateKeeper;
                $contentMailCountDateKeeper['date_keeper'] = $this->dateKeeperStart . '=>' . $this->dateKeeper;
                if ($this->updateItDate)
                    file_put_contents(storage_path("it-date.txt"), $this->dateKeeper);
            }
            if ($contentMailCountDateKeeper) {
                $this->dataItem->content = $contentMailCountDateKeeper;
            }
            $this->storeFileContentData($fileStore);
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

    private function storeFileContentData($fileNameSrc = 'data_connection_content.txt')
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
}
