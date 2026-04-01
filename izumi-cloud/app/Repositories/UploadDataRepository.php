<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-10-07
 */

namespace Repository;

use App\Events\LWSendMsEvent;
use App\Events\MessageSentEvent;
use App\Imports\StoreExcelImport;
use App\Imports\VehicleITPImport;
use App\Jobs\ExecuteCalculateMaintJob;
use App\Jobs\ImportDataToTableJob;
use App\Jobs\ImportMahoujinData;
use App\Jobs\ImportPCAData;
use App\Jobs\ImportStoreJob;
use App\Jobs\ImportVehicleCostJob;
use App\Jobs\SendMailLingKingShakenshoJob;
use App\Models\DataConnection;
use App\Models\DataItem;
use App\Models\Department;
use App\Models\File;
use App\Models\PlateHistory;
use App\Models\UploadData;
use App\Models\Vehicle;
use App\Models\VehicleDepartmentHistory;
use App\Models\VehicleInspecExpDateHistory;
use App\Models\VehicleInspectionCert;
use App\Models\VehiclePdfHistory;
use App\Repositories\Contracts\UploadDataRepositoryInterface;
use Carbon\Carbon;
use Helper\Common;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class UploadDataRepository extends BaseRepository implements UploadDataRepositoryInterface
{
    protected $dataConnection;
    protected $dataItem;
    protected $dataConnectionArr;
    protected $dataItemArr;
    protected $pathFileJson;
    protected $errorMsgPca;
    protected $filePdfId;

    protected $department_id;
    protected $flagCheckFileJson;

    protected $disk;

    protected $datePdf; //date lấy từ name file pdf
    protected $dateJson; //date lấy từ name file pdf
    protected $carNo; //car_no lấy từ name file pdf

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param UploadData $model
     */

    public function model()
    {
        $this->disk = Common::checkS3Conn() ? 's3' : 'public';
        return UploadData::class;
    }

    public function upload($request)
    {
        $file = $request->file('file') ? $request->file('file') : $request->get('file');
        $date = $request->get('date');
        $dataConnection = DataConnection::find($request->get('data_connection_id'));
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataConnection->dataItem()->create(["data_connection_id" => $dataConnection->id]);

        $this->changeStatus('excluding');
        $envBasePath = Common::getEnvBasePath();

        $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        $fileName = md5(Str::uuid()->toString()) . $file->getClientOriginalName();

        try {
            $path_file = $file->storeAs($path, $fileName, $this->disk);
            $fileData = File::create([
                'file_path' => $path_file,
                'file_name' => $fileName,
                "file_extension" => $file->getClientOriginalExtension(),
                "file_size" => $file->getSize(),
                "file_url" => Storage::disk($this->disk)->url($path_file),
                "file_sys_disk" => $this->disk,
            ]);

            $this->changeStatus('success', null, null, $fileData->id);
            if ($this->dataConnection->data_code = 'ICL_1025') {
                ImportVehicleCostJob::dispatch($path . '/' . $fileName, $date);
            }
            return $this->dataItem;

        } catch (\Exception $e) {
            $this->changeStatus('fail', $e->getMessage());
            return ["error" => "true", "message" => 'Internal error'];
        }
    }

    public function receiveDataJinziBugyo($request)
    {
        $file = $request->file('file') ? $request->file('file') : $request->get('file');

        $this->dataConnection = DataConnection::query()->where("data_code", 'ICL_1006')->whereNotNull('file_name_map')->first();
        $this->dataItem = $this->dataConnection->dataItem()->create(["data_connection_id" => $this->dataConnection->id]);
        $this->changeStatus('excluding');

        //'ICL_1000', 'ICL_1001', 'ICL_1002', 'ICL_1003', 'ICL_1004', 'ICL_1005', 'ICL_1006', 'ICL_1007', 'ICL_1008' Không dùng các connect này nữa nhưng chưa xoá khỏi db
        if (!$request->has('file') && !$file) {
            return ["error" => true, "message" => "File zip not exit", "code" => 422];
        }

        $path_file_unzip = null;

        try {
            $zip = new ZipArchive();
            if ($zip->open($file) === TRUE) {
                $checkNameExit = false;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileNameInZip = $zip->getNameIndex($i);
                    if (Str::contains($fileNameInZip, $this->dataConnection->file_name_map)) {

                        $fileContent = $zip->getStreamIndex($i);
                        $fileNameUnzip = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . $this->dataConnection->file_name_map;
                        $pathUnzip = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd') . '/unzip';
                        if (!Storage::exists($pathUnzip)) {
                            Storage::makeDirectory($pathUnzip);
                        }
                        Storage::writeStream($pathUnzip . '/' . $fileNameUnzip, $fileContent);
                        $path_file_unzip = $pathUnzip . '/' . $fileNameUnzip;
                        $checkNameExit = true;
                        break;
                    }
                }
                $zip->close();

                if ($checkNameExit) {
                    $fileName = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_' . $this->dataItem->id . '_' . $file->getClientOriginalName();
                    $envBasePath = Common::getEnvBasePath();
                    $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
//                    if (!Storage::disk('s3')->exists($path)) {
//                        Storage::disk('s3')->makeDirectory($path);
//                    }
                    Storage::disk($this->disk)->put($path . '/' . $fileName, $file->getContent());
                    $fileData = File::create([
                        'file_path' => $path . '/' . $fileName,
                        'file_name' => $fileName,
                        "file_extension" => pathinfo($fileName, PATHINFO_EXTENSION),
                        "file_size" => Storage::disk($this->disk)->size($path . '/' . $fileName),
                        "file_url" => Storage::disk($this->disk)->url($path . '/' . $fileName),
                        "file_sys_disk" => $this->disk,
                    ]);

                    $this->changeStatus('success', null, null, $fileData->id);

                    //save to database
                    if ($this->dataConnection->import_to_table) {
                        ImportDataToTableJob::dispatch($this->dataConnection->import_to_table, $path_file_unzip, $this->dataItem);
                    }
                    return ["message" => "Receive data success"];
                }
            } else {
                $this->changeStatus('fail', 'File data not exist');
                return ["error" => true, "message" => "ZipArchive open false", "code" => 500];
            }
        } catch (\Exception $exception) {
            $this->changeStatus('fail', $exception->getMessage());
            return ["error" => true, "message" => "Receive data fail", "code" => 500];
        }
    }

// Old logic array
//    public function receiveDataJinziBugyo($request)
//    {
//        $file = $request->file('file') ? $request->file('file') : $request->get('file');
//
//        $this->dataConnectionArr = DataConnection::whereIn("data_code", ['ICL_1006'])->whereNotNull('file_name_map')->get();
//        //'ICL_1000', 'ICL_1001', 'ICL_1002', 'ICL_1003', 'ICL_1004', 'ICL_1005', 'ICL_1006', 'ICL_1007', 'ICL_1008' Không dùng các connect này nữa nhưng chưa xoá khỏi db
//        if (!$request->has('file') && !$file) {
//            return ["error" => true, "message" => "File zip not exit", "code" => 422];
//        }
//
//        foreach ($this->dataConnectionArr as $key => $data) {
//            $this->dataConnection = $data;
//            $this->changeStatus('excluding');
//        }
//
//        try {
//            $zip = new ZipArchive();
//            if ($zip->open($file) == TRUE) {
//                foreach ($this->dataConnectionArr as $key => $data) {
//                    $this->dataConnection = $data;
//                    $this->dataItem = $data->dataItem()->create(["data_connection_id" => $data->id]);
//                    $this->dataItemArr[] = $this->dataItem;
//                    if ($zip->getFromName($data->file_name_map)) {
//                        $this->storeFileAndData($data, $zip, $data->file_name_map);
//                    } else {
//                        $this->changeStatus('fail', 'File data not exist');
//                    }
//                }
//                return ["message" => "Receive data success"];
//            } else {
//                foreach ($this->dataConnectionArr as $key => $data) {
//                    $this->dataConnection = $data;
//                    $this->dataItem = $data->dataItem()->create(["data_connection_id" => $data->id]);
//                    $this->dataItemArr[] = $this->dataItem;
//                    $this->changeStatus('fail', 'File data not exist');
//                }
//                return ["error" => true, "message" => "ZipArchive open false", "code" => 500];
//            }
//        } catch (\Exception $exception) {
//            foreach ($this->dataConnectionArr as $key => $data) {
//                $this->dataConnection = $data;
//                foreach ($this->dataItemArr as $key1 => $data1) {
//                    if ($data1->data_connection_id == $data->id) {
//                        $this->dataItem = $data1;
//                        break;
//                    }
//                }
//                $this->changeStatus('fail', $exception->getMessage());
//            }
//            return ["error" => true, "message" => "Receive data fail", "code" => 500];
//        }
//    }


    public function receiveDataKyuyoBugyo($request)
    {
        $file = $request->file('file') ? $request->file('file') : $request->get('file');

        $this->dataConnection = DataConnection::whereIn("data_code", ['ICL_1009'])->whereNotNull('file_name_map')->first();
        if (!$request->has('file') && !$file) {
            return ["error" => true, "message" => "File zip not exit", "code" => 422];
        }

        $this->changeStatus('excluding');
        $envBasePath = Common::getEnvBasePath();

        $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        $fileName = md5(Str::uuid()->toString()) . '_' . $file->getClientOriginalName();

        try {
            $zip = new ZipArchive();
            if ($zip->open($file) === TRUE) {
                $checkNameExit = false;
                $arrName = explode(',', $this->dataConnection->file_name_map);
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileNameInZip = $zip->getNameIndex($i);
                    if (in_array($fileNameInZip, $arrName)) {
                        $checkNameExit = true;
                        break;
                    }
                }
                $zip->close();
                if ($checkNameExit) {
                    $this->dataItem = $this->dataConnection->dataItem()->create(["data_connection_id" => $this->dataConnection->id]);
                    $path_file = $file->storeAs($path, $fileName, $this->disk);
                    $fileData = File::create([
                        'file_path' => $path_file,
                        'file_name' => $fileName,
                        "file_extension" => $file->getClientOriginalExtension(),
                        "file_size" => $file->getSize(),
                        "file_url" => Storage::disk($this->disk)->url($path_file),
                        "file_sys_disk" => $this->disk,
                    ]);

                    $this->changeStatus('success', null, null, $fileData->id);
                    return ["message" => "Receive data success"];
                } else {
                    $this->dataItem = $this->dataConnection->dataItem()->create(["data_connection_id" => $this->dataConnection->id]);
                    $this->changeStatus('fail', 'File ' . $this->dataConnection->file_name_map . ' not exist');
                    return ["message" => "Receive data success"];
                }
            } else {
                $this->dataItem = $this->dataConnection->dataItem()->create(["data_connection_id" => $this->dataConnection->id]);
                $this->changeStatus('fail', 'File data not exist');
                return ["error" => true, "message" => "ZipArchive open false", "code" => 500];
            }
        } catch (\Exception $exception) {
            $this->dataItem = $this->dataConnection->dataItem()->create(["data_connection_id" => $this->dataConnection->id]);
            $this->changeStatus('fail', $exception->getMessage());
            return ["error" => true, "message" => "Receive data fail", "code" => 500];
        }
    }

    //
    public function receiveDataMahojin($request)
    {
        $file = $request->file('file') ? $request->file('file') : $request->get('file');
        $dataConnection = DataConnection::where('data_code', 'ICL_1016')->first();
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataConnection->dataItem()->create(["data_connection_id" => $dataConnection->id]);

        $this->changeStatus('excluding');
        $envBasePath = Common::getEnvBasePath();

        $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        $fileName = md5(Str::uuid()->toString()) . $file->getClientOriginalName();

//        if ($dataConnection->file_name_map && ($dataConnection->file_name_map !== $file->getClientOriginalName())) {
//            $this->changeStatus('fail', 'The file name must be ' . $dataConnection->file_name_map);
//            return ["error" => true, "message" => "The file name must be " . $dataConnection->file_name_map, "code" => 500];
//        }

        try {
            $path_file = $file->storeAs($path, $fileName, $this->disk);
            $fileData = File::create([
                'file_path' => $path_file,
                'file_name' => $fileName,
                "file_extension" => $file->getClientOriginalExtension(),
                "file_size" => $file->getSize(),
                "file_url" => Storage::disk($this->disk)->url($path_file),
                "file_sys_disk" => $this->disk,
            ]);

            if ($fileData) {
                if ($file->getClientOriginalExtension() == 'csv') {
                    ImportMahoujinData::dispatch($fileData->file_path);
                } else {
                    $this->openFileZip(Storage::path($fileData->file_path));
                }
            }

            $this->changeStatus('success', null, null, $fileData->id);
            return $this->dataItem;
        } catch (\Exception $exception) {
            $this->changeStatus('fail', $exception->getMessage());
            return ["error" => true, "message" => "Receive data fail", "code" => 500];
        }
    }

    public function getDataItemDownload($id, $isHistory = false)
    {
        if ($isHistory) {
            $dataItem = DataItem::find($id);
            $dataHistory = $dataItem->data_connection_history;
            return [
                'final_connect_time' => Arr::get($dataHistory, 'final_connect_time'),
                'data_name' => Arr::get($dataHistory, 'name'),
                'status' => Arr::get($dataHistory, 'final_status'),
                'error' => $dataItem->msg_error,
                'response_body' => $dataItem->response_body,
            ];
        } else {
            return DataItem::find($id);
        }
    }


    public function receiveVIC($request)
    {
        $params = $request->all();
        $dataConnection = DataConnection::where('data_code', 'ICL_1022')->first();
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataConnection->dataItem()->create(["data_connection_id" => $dataConnection->id]);
        $this->changeStatus('excluding');
        $file = $request->file('file') ? $request->file('file') : $request->get('file');
        $file_pdf = $request->file('file_pdf') ? $request->file('file_pdf') : $request->get('file_pdf');
        $fileClientOriginalName = Common::getFileNameCheckEncode($file->getClientOriginalName());
        $fileClientOriginalNamePdf = Common::getFileNameCheckEncode($file_pdf->getClientOriginalName());
        $this->filePdfId = null;
        $this->department_id = Arr::get($params, 'department_id');

        // kiểm tra xem data liên kết có cùng date hay không
        $this->datePdf = Arr::get($params, 'date_pdf');
        $this->dateJson = Arr::get($params, 'date_json');
        $this->carNo = Arr::get($params, 'car_no');

        $vehiclePdfHistory = VehiclePdfHistory::where('car_no', $this->carNo)
            ->orderBy('id', 'DESC')
            ->first();
        if ($vehiclePdfHistory) {
            if (
                $this->datePdf &&
                $this->dateJson &&
                $vehiclePdfHistory->date_pdf &&
                $vehiclePdfHistory->date_json
            ) {
                if (
                    Carbon::hasFormat($vehiclePdfHistory->date_pdf, 'YmdHis') &&
                    Carbon::hasFormat($vehiclePdfHistory->date_json, 'YmdHis') &&
                    Carbon::hasFormat($this->datePdf, 'YmdHis') &&
                    Carbon::hasFormat($this->dateJson, 'YmdHis')
                ) {
                    $datePdfHistory = Carbon::createFromFormat('YmdHis', $vehiclePdfHistory->date_pdf);
                    $dateJsonHistory = Carbon::createFromFormat('YmdHis', $vehiclePdfHistory->date_json);
                    $datePdfNow = Carbon::createFromFormat('YmdHis', $this->datePdf);
                    $dateJsonNow = Carbon::createFromFormat('YmdHis', $this->dateJson);

                    if ($datePdfNow->lt($datePdfHistory) || $dateJsonNow->lt($dateJsonHistory)) {
                        Log::info("Case 1: Incoming date is older");
                        return [
                            'status' => 'fail',
                            'message' => '既に連携されている最新のファイルよりも過去の日時となっているため、連携できません。',
                        ];
                    } elseif ($datePdfNow->equalTo($datePdfHistory) || $dateJsonNow->equalTo($dateJsonHistory)) {
                        Log::info("Case 2: Incoming date is same");
                        return [
                            'status' => 'fail',
                            'message' => '同一の車両番号・日時で既に連携済みのため、連携できません。別のファイルをご使用ください。',
                        ];
                    }
                } else {
                    Log::info("Invalid date format detected in one or more fields.");
                    return [
                        'status' => 'fail',
                        'message' => '日付形式が正しくありません。ファイルの日時をご確認ください。',
                    ];
                }
            } else {
                Log::info("Invalid date format detected in one or more fields.");
                return [
                    'status' => 'fail',
                    'message' => '日付形式が正しくありません。ファイルの日時をご確認ください。',
                ];
            }
        }

        if ($dataConnection && $file_pdf) {
            Log::info("update or create new data pdf");
            $envBasePath = Common::getEnvBasePath();
            $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
            $fileName = md5(Str::uuid()->toString()) . '_' . $fileClientOriginalNamePdf;
            $path_file = $file_pdf->storeAs($path, $fileName, $this->disk);
            $filePdfData = File::create([
                'file_path' => $path_file,
                'file_name' => $fileClientOriginalNamePdf,
                "file_extension" => $file_pdf->getClientOriginalExtension(),
                "file_size" => $file_pdf->getSize(),
                "file_url" => Storage::disk($this->disk)->url($path_file),
                "file_sys_disk" => $this->disk,
            ]);
            $filePdf = json_encode(['file_id' => $filePdfData->id]);
            $this->filePdfId = $filePdfData->id;


            $this->changeStatus('success', null, null, null, $filePdf);
        }

        if ($dataConnection && $file) {
            Log::info("update or create new data json");
            $envBasePath = Common::getEnvBasePath();
            $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
            $fileName = md5(Str::uuid()->toString()) . '_' . $fileClientOriginalName;
            $path_file = $file->storeAs($path, $fileName, $this->disk);
            $fileData = File::create([
                'file_path' => $path_file,
                'file_name' => $fileName,
                "file_extension" => $file->getClientOriginalExtension(),
                "file_size" => $file->getSize(),
                "file_url" => Storage::disk($this->disk)->url($path_file),
                "file_sys_disk" => $this->disk,
            ]);
            $this->pathFileJson = $path . '/' . $fileName;
            $this->saveDataVIC($filePdfData);
            if ($this->flagCheckFileJson == 1) {
                $this->changeStatus('fail', 'File not null', null);
                return [
                    'status' => 'fail',
                    'message' => 'File not null',
                ];
            }
            $this->changeStatus('success', null, null, $fileData->id);
            return true;
        }
        $this->changeStatus('fail', 'File not null', null);

        return [
            'status' => 'fail',
            'message' => 'File not null',
        ];
    }

    private function saveDataVIC($filePdfData)
    {
        try {
            $flagCheckUpdate = false;
            $resultJson = Storage::disk($this->disk)->get($this->pathFileJson);
            $readingParts = data_get(json_decode($resultJson), 'CertInfo');
            $CarNo = data_get($readingParts, 'CarNo');
            if ($CarNo) {
                $this->flagCheckFileJson = 0;
            } else {
                $this->flagCheckFileJson = 1;
            }
            $now = Carbon::now()->format('Y-m-d');

            $env = App::environment();

            if ($readingParts && $CarNo) {
                $EntryNoCarNo = data_get($readingParts, 'EntryNoCarNo');
                $EntryNoCarNoConvert = mb_convert_kana($EntryNoCarNo, "ra");
                $CarNoConvert = mb_convert_kana($CarNo, "ra");
                $checkVhc = Vehicle::query()->where('vehicle_identification_number', $CarNoConvert)->first();
                $ValidPeriodExpirdateE = data_get($readingParts, 'ValidPeriodExpirdateE');
                $ValidPeriodExpirdateY = data_get($readingParts, 'ValidPeriodExpirdateY');
                $ValidPeriodExpirdateM = data_get($readingParts, 'ValidPeriodExpirdateM');
                $ValidPeriodExpirdateD = data_get($readingParts, 'ValidPeriodExpirdateD');
                $inspection_expiration_date = Common::japanDateToDate($ValidPeriodExpirdateE . trim($ValidPeriodExpirdateY) . '年' . trim($ValidPeriodExpirdateM) . '月' . trim($ValidPeriodExpirdateD) . '日');

                $FirstregistdateE = data_get($readingParts, 'FirstregistdateE');
                $FirstregistdateY = data_get($readingParts, 'FirstregistdateY');
                $FirstregistdateM = data_get($readingParts, 'FirstregistdateM');
                $first_registration = Common::japanDateToDate($FirstregistdateE . trim($FirstregistdateY) . '年' . trim($FirstregistdateM) . '月' . 1 . '日');
                $noteInfo = data_get($readingParts, 'NoteInfo');

                $dataVIC = [
                    'ElectCertMgNo' => data_get($readingParts, 'ElectCertMgNo'),
                    'FormType' => data_get($readingParts, 'FormType'),
                    'CarId' => data_get($readingParts, 'CarId'),
                    'TransportationBureauChiefName' => data_get($readingParts, 'TranspotationBureauchiefName'),
                    'EntryNoCarNo' => data_get($readingParts, 'EntryNoCarNo'),
                    'GrantdateE' => data_get($readingParts, 'GrantdateE'),
                    'GrantdateY' => data_get($readingParts, 'GrantdateY'),
                    'GrantdateM' => data_get($readingParts, 'GrantdateM'),
                    'GrantdateD' => data_get($readingParts, 'GrantdateD'),
                    'RegGrantDateE' => data_get($readingParts, 'ReggrantdateE'),
                    'RegGrantDateY' => data_get($readingParts, 'ReggrantdateY'),
                    'RegGrantDateM' => data_get($readingParts, 'ReggrantdateM'),
                    'RegGrantDateD' => data_get($readingParts, 'ReggrantdateD'),
                    'FirstRegDateE' => data_get($readingParts, 'FirstregistdateE'),
                    'FirstRegDateY' => data_get($readingParts, 'FirstregistdateY'),
                    'FirstRegDateM' => data_get($readingParts, 'FirstregistdateM'),
                    'CarName' => data_get($readingParts, 'CarName'),
                    'CarNameCode' => data_get($readingParts, 'CarNameCode'),
                    'CarNo' => data_get($readingParts, 'CarNo'),
                    'CarNoConvert' => $CarNoConvert,
                    'Model' => data_get($readingParts, 'Model'),
                    'EngineModel' => data_get($readingParts, 'EngineModel'),
                    'OwnerNameLowLevelChar' => data_get($readingParts, 'OwnernameLowLevelChar'),
                    'OwnerNameHighLevelChar' => data_get($readingParts, 'OwnernameHighLevelChar'),
                    'OwnerAddressChar' => data_get($readingParts, 'OwnerAddressChar'),
                    'OwnerAddressNumValue' => data_get($readingParts, 'OwnerAddressNumValue'),
                    'OwnerAddressCode' => data_get($readingParts, 'OwnerAddressCode'),
                    'UsernameLowLevelChar' => data_get($readingParts, 'UsernameLowLevelChar'),
                    'UsernameHighLevelChar' => data_get($readingParts, 'UsernameHighLevelChar'),
                    'UserAddressChar' => data_get($readingParts, 'UserAddressChar'),
                    'UserAddressNumValue' => data_get($readingParts, 'UserAddressNumValue'),
                    'UserAddressCode' => data_get($readingParts, 'UserAddressCode'),
                    'UseHeadquarterChar' => data_get($readingParts, 'UseheadqrterChar'),
                    'UseHeadquarterNumValue' => data_get($readingParts, 'UseheadqrterNumValue'),
                    'UseHeadquarterCode' => data_get($readingParts, 'UseheadqrterCode'),
                    'CarKind' => data_get($readingParts, 'CarKind'),
                    'Use' => data_get($readingParts, 'Use'),
                    'PrivateBusiness' => data_get($readingParts, 'PrivateBusiness'),
                    'CarShape' => data_get($readingParts, 'CarShape'),
                    'CarShapeCode' => data_get($readingParts, 'CarShapeCode'),
                    'Cap' => data_get($readingParts, 'Cap'),
                    'MaxLoadAge' => data_get($readingParts, 'Maxloadage'),
                    'CarWgt' => data_get($readingParts, 'CarWgt'),
                    'CarTotalWgt' => data_get($readingParts, 'CarTotalWgt'),
                    'Length' => data_get($readingParts, 'Length'),
                    'Width' => data_get($readingParts, 'Width'),
                    'Height' => data_get($readingParts, 'Height'),
                    'FfAxWgt' => data_get($readingParts, 'FfAxWgt'),
                    'FrAxWgt' => data_get($readingParts, 'FrAxWgt'),
                    'RfAxWgt' => data_get($readingParts, 'RfAxWgt'),
                    'RrAxWgt' => data_get($readingParts, 'RrAxWgt'),
                    'Displacement' => data_get($readingParts, 'Displacement'),
                    'FuelClass' => data_get($readingParts, 'FuelClass'),
                    'ModelSpecifyNo' => data_get($readingParts, 'ModelSpecifyNo'),
                    'ClassifyAroundNo' => data_get($readingParts, 'ClassifyAroundNo'),
                    'ValidPeriodExpDateE' => data_get($readingParts, 'ValidPeriodExpirdateE'),
                    'ValidPeriodExpDateY' => data_get($readingParts, 'ValidPeriodExpirdateY'),
                    'ValidPeriodExpDateM' => data_get($readingParts, 'ValidPeriodExpirdateM'),
                    'ValidPeriodExpDateD' => data_get($readingParts, 'ValidPeriodExpirdateD'),
                    'NoteInfo' => data_get($readingParts, 'NoteInfo'),
                    'ElectCertPublishDateE' => data_get($readingParts, 'ElectCertPublishdateE'),
                    'ElectCertPublishDateY' => data_get($readingParts, 'ElectCertPublishdateY'),
                    'ElectCertPublishDateM' => data_get($readingParts, 'ElectCertPublishdateM'),
                    'ElectCertPublishDateD' => data_get($readingParts, 'ElectCertPublishdateD'),
                ];
                $no_number_plate = preg_replace('/\s+/', '', str_replace('　', '', $EntryNoCarNoConvert));
                if ($checkVhc) {
                    $checkVhc->first_registration = Carbon::parse($first_registration)->format('Y-m');
                    $checkVhc->manufactor = data_get($readingParts, 'CarName');
                    $checkVhc->type = data_get($readingParts, 'Model');
                    $checkVhc->motor = data_get($readingParts, 'EngineModel');
                    $checkVhc->maximum_loading_capacity = data_get($readingParts, 'Maxloadage');
                    $checkVhc->vehicle_total_weight = data_get($readingParts, 'CarTotalWgt');
                    $checkVhc->length = data_get($readingParts, 'Length');
                    $checkVhc->width = data_get($readingParts, 'Width');
                    $checkVhc->height = data_get($readingParts, 'Height');
                    $checkVhc->displacement = data_get($readingParts, 'Displacement');

                    if ($this->department_id == $checkVhc->department_id ) {
                        if (Carbon::parse($inspection_expiration_date)->gt($checkVhc->inspection_expiration_date)) {
                            $checkVhc->inspection_expiration_date = Carbon::parse($inspection_expiration_date)->format('Y-m-d');
                            $flagCheckUpdate = true;
                        }
                    }

                    if ($this->filePdfId) {
                        Log::info("update vehicle from izumi-shakensho " . $CarNoConvert . "department: " . $this->department_id);
                        $checkVhc->remark_old_car_1 = $noteInfo;
                        $checkVhc->file_pdf_id = $this->filePdfId;
                        $vehiclePdfHistory = VehiclePdfHistory::where('vehicle_id', $checkVhc->id)
                            ->whereRaw("DATE(created_at) = ?", [now()->format('Y-m-d')])
                            ->first();

                        VehiclePdfHistory::create([
                            'file_id' => $this->filePdfId,
                            'vehicle_id' => $checkVhc->id,
                            'date_pdf' => $this->datePdf,
                            'date_json' => $this->dateJson,
                            'car_no' => $this->carNo,
                        ]);

                        $vehicleDepartmentHistory = VehicleDepartmentHistory::where('date', $now)
                            ->where('vehicle_id', $checkVhc->id)
                            ->orderBy('id', 'DESC')
                            ->first();
                        if ($checkVhc->department_id != $this->department_id) {
                            $checkVhc->department_id = $this->department_id;
                            if (!$vehicleDepartmentHistory) {
                                $plate = VehicleDepartmentHistory::create([
                                    'vehicle_id' => $checkVhc->id,
                                    'date' => $now,
                                    'department_id' => $this->department_id
                                ]);
                            } else if ($vehicleDepartmentHistory->department_id != $this->department_id) {
                                $plate = VehicleDepartmentHistory::create([
                                    'vehicle_id' => $checkVhc->id,
                                    'date' => $now,
                                    'department_id' => $this->department_id
                                ]);
                            }
                        }
                        if ($env == 'dev') {
                            // tạm thời dừng send LWM ở môi trường stage
                            event(new LWSendMsEvent($no_number_plate));
                        }
                    }
                    $dataVIC['vehicle_id'] = $checkVhc->id;
                    $checkVhc->save();
                    $plateHistory = PlateHistory::query()->where('vehicle_id', $checkVhc->id)
                        ->orderBy('id', 'DESC')
                        ->first();
                    if ($plateHistory->no_number_plate != $no_number_plate) {
                        PlateHistory::query()->create([
                            'vehicle_id' => $checkVhc->id,
                            'no_number_plate' => $no_number_plate,
                            'date' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }


                    if ($flagCheckUpdate) {
                        VehicleInspectionCert::query()->create($dataVIC);
                    } else {
                        $dataVIC['updated_at'] = Carbon::now();
                        $vehicleInspectionCert = VehicleInspectionCert::query()
                            ->where('vehicle_id', $checkVhc->id)
                            ->orderBy('updated_at', 'DESC')
                            ->first();
                        if ($vehicleInspectionCert) {
                            $dataVIC['ValidPeriodExpDateE'] =  $vehicleInspectionCert->ValidPeriodExpDateE;
                            $dataVIC['ValidPeriodExpDateY'] = $vehicleInspectionCert->ValidPeriodExpDateY;
                            $dataVIC['ValidPeriodExpDateM'] = $vehicleInspectionCert->ValidPeriodExpDateM;
                            $dataVIC['ValidPeriodExpDateD'] = $vehicleInspectionCert->ValidPeriodExpDateD;
                            $vehicleInspectionCert->update($dataVIC);
                        } else {
                            VehicleInspectionCert::query()->create($dataVIC);
                        }
                    }
                    if ($inspection_expiration_date && $checkVhc->wasChanged('inspection_expiration_date')) {
                        VehicleInspecExpDateHistory::query()->create([
                            'vehicle_id' => $checkVhc->id,
                            'inspection_expiration_date' => Carbon::parse($inspection_expiration_date)->format('Y-m-d'),
                        ]);
                        ExecuteCalculateMaintJob::dispatch($checkVhc->id, true);
                    }
                } elseif ($this->filePdfId && $this->department_id) {
                    //trường nào ko có thì để null
                    Log::info("create vehicle from izumi-shakensho");
                    $department = Department::query()->where('id', $this->department_id)->first();
                    if ($department) {
                        $vehicle = Vehicle::query()->create([
                            'first_registration' => Carbon::parse($first_registration)->format('Y-m'),
                            'manufactor' => data_get($readingParts, 'CarName'),
                            'type' => data_get($readingParts, 'Model'),
                            'motor' => data_get($readingParts, 'EngineModel'),
                            'maximum_loading_capacity' => data_get($readingParts, 'Maxloadage'),
                            'vehicle_total_weight' => data_get($readingParts, 'CarTotalWgt'),
                            'length' => data_get($readingParts, 'Length'),
                            'width' => data_get($readingParts, 'Width'),
                            'height' => data_get($readingParts, 'Height'),
                            'displacement' => data_get($readingParts, 'Displacement'),
                            'inspection_expiration_date' => Carbon::parse($inspection_expiration_date)->format('Y-m-d'),
                            'department_id' => $this->department_id,
                            'vehicle_identification_number' => $CarNoConvert,
                            'file_pdf_id' => $this->filePdfId,
                            'remark_old_car_1' => $noteInfo,
                            'vehicle_delivery_date' => Carbon::now()->format('Y-m-d'),
                        ]);

                        if ($vehicle) {
                            $vehicle->vehicle_department_history()->create([
                                "date" => \Illuminate\Support\Carbon::now()->format('Y-m-d'),
                                "department_id" => $this->department_id,
                                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                            ]);
                            $dataVIC['vehicle_id'] = $vehicle->id;
                            PlateHistory::query()->firstOrCreate([
                                'vehicle_id' => $vehicle->id,
                                'no_number_plate' => $no_number_plate,
                            ], [
                                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                            ]);
                            VehiclePdfHistory::create([
                                'file_id' => $this->filePdfId,
                                'vehicle_id' => $vehicle->id,
                                'date_pdf' => $this->datePdf,
                                'date_json' => $this->dateJson,
                                'car_no' => $this->carNo,
                            ]);
                            VehicleInspectionCert::query()->create($dataVIC);
                            // tạm thời dừng send LWM ở môi trường stage
                            if ($env == 'dev') {
                                event(new LWSendMsEvent($no_number_plate));
                            }
                            if ($inspection_expiration_date) {
                                VehicleInspecExpDateHistory::query()->create([
                                    'vehicle_id' => $vehicle->id,
                                    'inspection_expiration_date' => Carbon::parse($inspection_expiration_date)->format('Y-m-d'),
                                ]);
                                ExecuteCalculateMaintJob::dispatch($vehicle->id);
                            }
                        }
                    }
                }
                $department_name = '';
                if($this->department_id) {
                    $department = Department::query()->where('id', $this->department_id)->first();
                    if($department) {
                        $department_name = $department->name;
                    }
                }

                $sendMailShakensho['department_name'] = $department_name;
                $sendMailShakensho['vehicleNoPlate'] = $no_number_plate;
                $sendMailShakensho['dateTime'] = Carbon::now()->format('Y年m月d日 H:i');
                $sendMailShakensho['urlPdf'] = $filePdfData->url_view_file;
                $sendMailShakensho['filePdfName'] = $filePdfData->file_name;
                if($checkVhc) {
                    $sendMailShakensho['action'] = 'update';
                    SendMailLingKingShakenshoJob::dispatch($sendMailShakensho);

                } else {
                    $sendMailShakensho['action'] = 'create';
                    SendMailLingKingShakenshoJob::dispatch($sendMailShakensho);
                }
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }


    private function openFileZip($pathZipFile)
    {
        $zip = new ZipArchive();
        if ($zip->open($pathZipFile) === TRUE) {
            $fileContent = $zip->getFromName('資産明細.csv');
            $fileName = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_mahojin.csv';
            $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd') . '/unzip';
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path);
            }
            Storage::put($path . '/' . $fileName, $fileContent);
            try {
                ImportMahoujinData::dispatch($path . '/' . $fileName);
            } catch (\Exception $e) {
                Log::info($e->getMessage());
                error_log($e->getMessage());
            }
            $zip->close();
        }
    }


    public function receiveDataPCA($request)
    {
        $file = $request->file('file') ? $request->file('file') : $request->get('file');
        $dataConnection = DataConnection::where('data_code', 'ICL_1027')->first();
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataConnection->dataItem()->create(["data_connection_id" => $dataConnection->id]);

        $this->changeStatus('excluding');
        $envBasePath = Common::getEnvBasePath();
        $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        $fileName = md5(Str::uuid()->toString()) . $file->getClientOriginalName();
//        if (!Storage::exists($path)) {
//            Storage::makeDirectory($path);
//        }
        try {
            $path_file = $file->storeAs($path, $fileName);
            $fileData = File::create([
                'file_path' => $path_file,
                'file_name' => $fileName,
                "file_extension" => $file->getClientOriginalExtension(),
                "file_size" => $file->getSize(),
                "file_url" => Storage::url($path_file),
            ]);

            if ($fileData) {
                if ($file->getClientOriginalExtension() == 'zip') {
                    $this->openFileZipPCA($fileData->file_path, $fileData);
                }
            }

            if ($this->errorMsgPca && count($this->errorMsgPca) > 0) {
                $this->changeStatus('fail', json_encode($this->errorMsgPca), null, $fileData->id);
            } else {
                $this->changeStatus('excluding', null, null, $fileData->id);
            }

            return $this->dataItem;
        } catch (\Exception $exception) {
            $this->changeStatus('fail', $exception->getMessage());
            Log::info($exception->getMessage());
            Log::info($exception->getTraceAsString());
            return ["error" => true, "message" => "Receive data fail", "code" => 500];
        }
    }


    private function openFileZipPCA($pathZipFile, $fileData)
    {
        $zip = new ZipArchive();
        if ($zip->open(Storage::path($pathZipFile)) === TRUE) {
            $fileContent = $zip->getStream('pca.csv');
            if ($fileContent) {
                $fileName = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_pca.csv';
                $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd') . '/unzip';
                if (!Storage::exists($path)) {
                    Storage::makeDirectory($path);
                }
                Storage::put($path . '/' . $fileName, $fileContent);

                ImportPCAData::dispatch($path . '/' . $fileName, $this->dataConnection, $this->dataItem);

                $envBasePath = Common::getEnvBasePath();
                $pathS3 = $envBasePath . $pathZipFile;
                Storage::disk($this->disk)->put($pathS3, Storage::disk()->get($pathZipFile));
                $fileData->update([
                    'file_path' => $pathS3,
                    "file_size" => Storage::disk($this->disk)->size($pathS3),
                    "file_url" => Storage::disk($this->disk)->url($pathS3),
                    "file_sys_disk" => $this->disk,
                ]);
                if (Storage::exists($pathZipFile)) {
                    Storage::delete($pathZipFile);
                }
            } else {
                $this->errorMsgPca[] = 'File name pca.csv not exit in zip file';
            }
            $zip->close();
        }
    }

    public function uploadAndImportStore($request)
    {
        $file = $request->file('file') ? $request->file('file') : $request->get('file');
        $file_extension = $file->getClientOriginalExtension();

        if ($file_extension == 'xlsx') {
            Excel::import(new StoreExcelImport, $file);
        } elseif ($file_extension == 'zip') {
            $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
            $fileName = md5(Str::uuid()->toString()) . $file->getClientOriginalName();
            $file_extension = $file->getClientOriginalExtension();

            $path_file = $file->storeAs($path, $fileName);
            $fileData = File::create([
                'file_path' => $path_file,
                'file_name' => $fileName,
                "file_extension" => $file_extension,
                "file_size" => $file->getSize(),
                "expired_at" => Carbon::now()->addDays(15)
            ]);
            if ($fileData) {
                $zip = new ZipArchive();
                if ($zip->open(Storage::path($path_file)) === TRUE) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $fileNameInZip = $zip->getNameIndex($i);
                        $store_name = Str::replace('.xlsx', '', $fileNameInZip);
                        $fileContent = $zip->getStreamIndex($i);
                        $fileNameUnzip = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_' . $fileNameInZip;
                        $path_unzip = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd') . '/unzip';
                        if (!Storage::exists($path_unzip)) {
                            Storage::makeDirectory($path_unzip);
                        }
                        Storage::writeStream($path_unzip . '/' . $fileNameUnzip, $fileContent);
                        ImportStoreJob::dispatch($store_name, $path_unzip . '/' . $fileNameUnzip);
                    }
                    $zip->close();
                }
            }
        }
        return true;
    }

    private function changeStatus($status, $msgError = null, $msgRes = null, $fileId = null, $filePdf = null)
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
            if ($filePdf) {
                $this->dataItem->content = $filePdf;
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
