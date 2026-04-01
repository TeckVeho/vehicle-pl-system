<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace Repository;

use App\Events\MessageSentEvent;
use App\Exports\VehicleExportForItp;
use App\Helpers\CommonChromeDriver;
use App\Imports\VehicleITPImport;
use App\Models\DataConnection;
use App\Models\Department;
use App\Models\File;
use App\Models\Vehicle;
use App\Models\VehicleDataORCAI;
use App\Models\VehicleInspectionCert;
use App\Repositories\Contracts\AlcoholServiceRepositoryInterface;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Helper\Common;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Process\Process;
use ZipArchive;

class VehicleServiceRepository extends BaseRepository implements AlcoholServiceRepositoryInterface
{

    protected $dataConnection;
    protected $dataItem;
    protected $dataContent;
    protected $dataContentDecode;
    protected $errorMsg;
    protected $pathFileZip;
    protected $pathFileImage;
    protected $fileNameImage;
    protected $fileId;
    protected $fileDataOrcAi;
    protected $pathFileResult;
    protected $type = 'passive';
    protected $dateGetData;

    protected $keyList;

    protected $disk = 'public';
    protected $departmentMenu = [
        1 => "1",//'本社'
        2 => "112", // '横浜第１チーム'
        3 => "114", // '平塚チーム',
        5 => "117", // '静岡チーム',
        4 => "116", // '横浜第２、第３チーム',
        22 => "116", // '横浜第２、第３チーム',
//        13 => "115", // '三芳チーム',
        13 => "133", // '所沢チーム',
        7 => "119", // '東京チーム',
        8 => "118", // '八千代チーム',
        11 => "121", // '武蔵野チーム',
        12 => "120", // '埼玉チーム',
        6 => "123", // '千葉チーム',
        9 => "122", // '古河チーム',
        15 => "126", // '名古屋チーム',
        16 => "127", // '安城チーム',
        17 => "128", // '浜松チーム',
        18 => "129", // '富山チーム',
        14 => "130", // '新潟チーム',
        19 => "132", // '大阪チーム',
        20 => "131", // '神戸チーム',
    ];
    protected $departmentOptionEdit = [
        1 => '本社',
        2 => '横浜第１チーム',
        3 => '平塚チーム',
        5 => '静岡チーム',
        4 => '横浜第２チーム',
        22 => '横浜第３チーム',
//        13 => '三芳チーム',
        13 => '所沢チーム',
        7 => '東京チーム',
        8 => '八千代チーム',
        11 => '武蔵野チーム',
        12 => '埼玉チーム',
        6 => '千葉チーム',
        9 => '古河チーム',
        15 => '名古屋チーム',
        16 => '安城チーム',
        17 => '浜松チーム',
        18 => '富山チーム',
        14 => '新潟チーム',
        19 => '大阪チーム',
        20 => '神戸チーム',
    ];
    protected $listsCellMap = [
        'department_code' => '90px',//所属コード	[vehicle_department_history] / department_id
        'no_number_plate' => '240px',//車両名称	[vehicle_no_number_plate_history] / no_number_plate
        'width' => '840px', //車幅（mm）[vehicle] / width
        'height' => '990px',//車高（mm）[vehicle] / height
        'inspection_expiration_date' => '1890px', //契約終了年月日 [vehicle_data_orc_ai] / insurance_period_2
        'registration_date' => '2805px',//登録年月日 //[vehicle_inspection_cert] / RegGrantDateY・M・D
        '1st_registration' => '2970px', //初度登録年月 [vehicle] / 1st registration
        'body_shape' => '3120px',//車体の形状 [vehicle_inspection_cert] / Carshape
        'vehicle_name' => '3270px',//車名 [vehicle] /manufactor
        'maximum_loading_capacity' => '3570px', //最大積載量	[vehicle] / Maximum loading capacity
        'chassis_number' => '3720px',//車台番号[vehicle] / vehicle_identification_number
        'model' => '3870px',//型式[vehicle] / type
//        'owner' => '3870px',//所有者名称[vehicle] / Owner
        'user_name' => '4170px',//使用者名称[vehicle_inspection_cert] / UsernameLowLevelChar
        'validity_expiration_date' => '4520px',//有効期間満了日 [vehicle] / inspection expiration date
        'vehicle_total_weight' => '4685px',//車両総重量（kg）	[vehicle] / Vehicle total weight
        'riding_capacity' => '4835px',//乗車定員（人）[vehicle_inspection_cert] / Cap
    ];
    protected $listVehiclesScrape;
    protected $vehicle_identification_number_error;
    protected $isLoginItpSuccess = true;
    protected $downloadVehicleITPError;
    protected $scrapITPCurrentDpSelected = null;
    protected $errorITPSendMails = [
        'i.kohei2323@gmail.com',
        'phuong.codeunited@gmail.com',
        'izumi_kanou@izumilogi.co.jp',
        'izumi_k.suzuki@izumilogi.co.jp',
    ];
    protected $errorBodyITPSendMails = null;

    protected $dpMapZipItp = [
        '01.csv' => '本社',
        '06.csv' => '横浜第一',
        '08.csv' => '平塚',
        '010.csv' => '静岡',
        '011.csv' => '横浜第二,横浜第三',
        '012.csv' => '三芳',
        '013.csv' => '東京',
        '014.csv' => '八千',
        '015.csv' => '武蔵野',
        '016.csv' => '埼玉',
        '017.csv' => '千葉',
        '018.csv' => '古河',
        '021.csv' => '名古屋',
        '022.csv' => '安城',
        '023.csv' => '浜松',
        '024.csv' => '富山',
        '025.csv' => '新潟',
        '026.csv' => '大阪',
        '027.csv' => '神戸',
        '028.csv' => '所沢',
    ];

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        $this->disk = Common::checkS3Conn() ? 's3' : 'public';
        return DataConnection::class;
    }

    public function uploadVehicleDataToItp($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');
        $this->sendContentBody();
    }

    private function sendContentBody()
    {
        $dataConnect = DataConnection::where('data_code', 'ICL_1021')->first();

        if (!$dataConnect) {
            $this->changeStatus('fail', "Data connection 'data_code' not exists");
            return;
        }

        $dataVehicles = $this->getVehicleDataMap();
        if ($dataVehicles) {
            foreach ($dataVehicles as $keyList => $row) {
                $registration_date = Common::japanDateToDate(data_get($row, 'latest_vehicle_inspection_cert.RegGrantDateE') . trim(data_get($row, 'latest_vehicle_inspection_cert.RegGrantDateY')) . '年' . trim(data_get($row, 'latest_vehicle_inspection_cert.RegGrantDateM')) . '月' . trim(data_get($row, 'latest_vehicle_inspection_cert.RegGrantDateD')) . '日');
                $vehicle_identification_number = $row->vehicle_identification_number;
                $vehicle_identification_number2 = $row->vehicle_identification_number_2;
                if ($vehicle_identification_number2) {
                    $vehicle_identification_number = $row->vehicle_identification_number_2;
                }
                $listsData = [
                    "vehicle_identification_number" => Str::padLeft(Arr::get(explode('-', $vehicle_identification_number), 1), 8, '0'),
                    "department_code" => Arr::get($this->departmentOptionEdit, data_get($row, 'department.id')),
                    "no_number_plate" => $row->plate_history()->orderBy('date', 'DESC')->first()->no_number_plate,
                    "truck_classification_2" => $row->truck_classification_2,
//                    "inspection_expiration_date" => Carbon::parse($row->inspection_expiration_date)->format('Y/m/d'),
                    "1st_registration" => Carbon::parse($row->first_registration)->format('Y/m'),
                    "maximum_loading_capacity" => $row->maximum_loading_capacity,
                    "owner" => $row->owner,
                    "vehicle_total_weight" => $row->vehicle_total_weight,
                    "width" => $row->width,
                    "height" => $row->height,
                    'inspection_expiration_date' => Common::japanDateToDate(data_get($row, 'latest_data_orc_ai.insurance_period_2')),
                    'registration_date' => $registration_date,
                    'body_shape' => data_get($row, 'latest_vehicle_inspection_cert.CarShape'),
                    'vehicle_name' => $row->manufactor,
                    'chassis_number' => $vehicle_identification_number,
                    'model' => $row->type,
                    'user_name' => data_get($row, 'latest_vehicle_inspection_cert.UsernameLowLevelChar'),
                    'validity_expiration_date' => $row->inspection_expiration_date,
                    'riding_capacity' => data_get($row, 'latest_vehicle_inspection_cert.Cap'),
                    'itp_start_time' => Carbon::now()->format('Y-m-d H:i:s'),
                    'itp_end_time' => '',
                    'itp_is_updated' => '',
                    'itp_error_message' => '',
                ];
                $this->listVehiclesScrape[$keyList] = $listsData;
            }
            $this->scrapITP($dataVehicles);
            $this->storeFileCsvItp();
            $this->alert();
        }
    }

    public function saveFileAndExecuteFromAIOCR($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->dataContentDecode = json_decode($this->dataItem->content);
        $this->changeStatus('excluding');
        $this->saveFileImage();
        $this->saveFileResult();
        $this->saveDataToTable();
        if ($this->downloadVehicleITPError && count($this->downloadVehicleITPError) > 0) {
            $this->changeStatus('fail', $this->downloadVehicleITPError);
        } else {
            $this->changeStatus('success');
        }
    }

    public function downloadVehicleDataItp($dataConnection, $dataItem, $date)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->dateGetData = $date;
        $this->changeStatus('excluding');
        $this->downloadDataITP();
    }

    private function saveFileImage()
    {
        $readingUnitId = $this->dataContentDecode->unitId;
        $urlCallApi = API_AI_OCR_IMAGE;
        $envBasePath = Common::getEnvBasePath();
        $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        if (!Storage::disk()->exists($path)) {
            Storage::disk()->makeDirectory($path);
        }
        $fileName = md5(Str::uuid()->toString()) . '_' . $readingUnitId . '.zip';
        $pathZip = Storage::disk()->path($path) . '/' . $fileName;
        $response = Http::timeout(60)->withoutVerifying()->withHeaders(['X-ConsoleWeb-ApiKey' => API_CERT_KEY_AI_OCR])
            ->sink($pathZip)->get($urlCallApi, ['readingUnitId' => $readingUnitId]);

        $body = json_decode($response->getBody());
        if (!$response->getBody()) {
            $body = json_decode($response->json());
        }
        if ($response->getStatusCode() !== 200) {
            $this->errorMsg['errorSaveImage'] = $body;
        } else {
            if (isset($body->error)) {
                $this->changeStatus('fail');
            } else {
                $fileData = File::create([
                    'file_path' => $path . '/' . $fileName,
                    'file_name' => $fileName,
                    "file_extension" => 'zip',
                    "file_size" => Storage::size($path . '/' . $fileName),
//                    "file_url" => Storage::url($path . '/' . $fileName),
//                    "file_sys_disk" => 's3',
                ]);
                $zip = new ZipArchive();
                if ($zip->open($pathZip) === TRUE) {
                    $fileNameInZip = $zip->getNameIndex(0);
                    $fileNameImage = md5(Str::uuid()->toString()) . '_' . $fileNameInZip;
                    if ($fileNameInZip && Storage::put($path . '/' . $fileNameImage, $zip->getFromName($fileNameInZip))) {
                        $this->pathFileImage = $path . '/' . $fileNameImage;
                        $this->fileNameImage = $fileNameImage;
                    }
                    $zip->close();
                }
                $this->pathFileZip = $pathZip;
                $this->fileId = $fileData->id;
                $this->fileDataOrcAi = $fileData;
            }
        }
        $response->close();
    }

    private function saveFileResult()
    {
        $readingUnitId = $this->dataContentDecode->unitId;
        $urlCallApi = API_AI_OCR_PARTS;
        $envBasePath = Common::getEnvBasePath();
        $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
        }
        $fileName = md5(Str::uuid()->toString()) . '_' . $readingUnitId . '.txt';
        $response = Http::timeout(60)->withoutVerifying()->withHeaders(['X-ConsoleWeb-ApiKey' => API_CERT_KEY_AI_OCR])
            ->get($urlCallApi, ['readingUnitId' => $readingUnitId]);

        $body = json_decode($response->getBody());
        if (!$response->getBody()) {
            $body = json_decode($response->json());
        }
        if ($response->getStatusCode() !== 200) {
            $this->errorMsg['errorSaveResult'] = $body;
        } else {
            Storage::put($path . '/' . $fileName, json_encode($response->json()));
            $this->pathFileResult = $path . '/' . $fileName;
            $zip = new ZipArchive;
            if ($zip->open($this->pathFileZip) === TRUE) {
                $zip->addFile(Storage::path($path . '/' . $fileName), $fileName);
                $zip->close();

                $pathS3 = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
                Storage::disk($this->disk)->put($pathS3 . '/' . $fileName, Storage::disk()->get($path . '/' . $fileName));
                $this->fileDataOrcAi->update([
                    'file_path' => $pathS3 . '/' . $fileName,
                    "file_size" => Storage::disk($this->disk)->size($pathS3 . '/' . $fileName),
                    "file_url" => Storage::disk($this->disk)->url($pathS3 . '/' . $fileName),
                    "file_sys_disk" => $this->disk,
                ]);
            } else {
                $this->errorMsg['errorSaveResult'] = 'Add file to zip file error';
            }
        }
        $response->close();
    }

    private function saveDataToTable()
    {
        if ($this->pathFileResult) {
            $pageIds = $this->dataContentDecode->id;
            $resultJson = Storage::get($this->pathFileResult);
            $readingParts = data_get(json_decode($resultJson), 'readingParts');
            foreach ($pageIds as $item) {
                $dataColumn1 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 1)->first(), 'result');
                $dataColumn2 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 2)->first(), 'result');
                $dataColumn3 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 3)->first(), 'result');
                $dataColumn4 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 4)->first(), 'result');
                $dataColumn5 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 5)->first(), 'result');
                $dataColumn6 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 6)->first(), 'result');
                $dataColumn7 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 7)->first(), 'result');
                $dataColumn8 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 8)->first(), 'result');
                $dataColumn9 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 9)->first(), 'result');
                $dataColumn10 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 10)->first(), 'result');
                $dataColumn11 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 11)->first(), 'result');
                $dataColumn12 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 12)->first(), 'result');
                $dataColumn13 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 13)->first(), 'result');
                $dataColumn14 = data_get(collect($readingParts)->where('readingPageId', $item)->where('columnNo', 14)->first(), 'result');
                $vehicle = Vehicle::query()
                    ->whereRaw('INSTR("' . $dataColumn3 . '" , vehicle_identification_number)')
                    ->first();
                if ($vehicle) {
                    VehicleDataORCAI::query()->create([
                        'vehicle_id' => $vehicle->id,
                        'certificate_number' => $dataColumn1,
                        'issue_date' => $dataColumn2,
                        'vehicle_identification_number' => $dataColumn3,
                        'insurance_period_1' => $dataColumn4,
                        'insurance_period_2' => $dataColumn5,
                        'address' => $dataColumn6,
                        'policyholder' => $dataColumn7,
                        'change_item' => $dataColumn8,
                        'jurisdiction_store_name_and_location' => $dataColumn9,
                        'vehicle_type' => $dataColumn10,
                        'location' => $dataColumn11,
                        'insurance_fee' => $dataColumn12,
                        'financial_institution_name' => $dataColumn13,
                        'seal' => $dataColumn14,
                        'file_path' => $this->pathFileImage,
                        'file_name' => $this->fileNameImage,
                    ]);
                }
            }
        }
    }

    public function scrapITP($dataVehicles)
    {

        $browser = null;
        $process = null;
        $port = null;
        try {
            $portCommand = CommonChromeDriver::startChromeDriver();
            $port = $portCommand['port'];
            // Mở tiến trình và chạy lệnh trong nền
            $process = new Process(explode(' ', $portCommand['command']));
            $process->start();
            sleep(5);
            // Cấu hình các tùy chọn cho ChromeDriver
            $options = new ChromeOptions();
            $arguments = Config::get('scrape_dusk.chrome');
            $options->addArguments($arguments);

//            $preferences = [
//                'download.default_directory' => Storage::disk('local')->path('test_download'),
//                'download.prompt_for_download' => false,
//                'download.directory_upgrade' => false,
//                'savefile.default_directory' => Storage::disk('local')->path('test_download'),           // Cấu hình lại thư mục tải xuống
//            ];
//            $options->setExperimentalOption('prefs', $preferences);

            // Khởi động ChromeDriver với các tùy chọn
            $driver = RemoteWebDriver::create('http://localhost:' . $port,
                DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY, $options
                )
            );

            // Bắt đầu thu thập dữ liệu
            $browser = new Browser($driver);

            $this->loginITP($browser);
            if ($this->isLoginItpSuccess) {
                foreach ($dataVehicles as $keyList => $row) {
                    try {
                        $this->keyList = $keyList;
                        $registration_date = Common::japanDateToDate(data_get($row, 'latest_vehicle_inspection_cert.RegGrantDateE') . trim(data_get($row, 'latest_vehicle_inspection_cert.RegGrantDateY')) . '年' . trim(data_get($row, 'latest_vehicle_inspection_cert.RegGrantDateM')) . '月' . trim(data_get($row, 'latest_vehicle_inspection_cert.RegGrantDateD')) . '日');
                        $vehicle_identification_number = $row->vehicle_identification_number;
                        $vehicle_identification_number2 = $row->vehicle_identification_number_2;
                        if ($vehicle_identification_number2) {
                            $vehicle_identification_number = $row->vehicle_identification_number_2;
                        }
                        $listsData = [
                            "vehicle_identification_number" => Str::padLeft(Arr::get(explode('-', $vehicle_identification_number), 1), 8, '0'),
                            "department_code" => Arr::get($this->departmentOptionEdit, data_get($row, 'department.id')),
                            "no_number_plate" => $row->plate_history()->orderBy('date', 'DESC')->first()->no_number_plate,
                            "truck_classification_2" => $row->truck_classification_2,
                            "1st_registration" => Carbon::parse($row->first_registration)->format('Y/m'),
                            "maximum_loading_capacity" => $row->maximum_loading_capacity,
                            "owner" => $row->owner,
                            "vehicle_total_weight" => $row->vehicle_total_weight,
                            "width" => $row->width,
                            "height" => $row->height,
                            'inspection_expiration_date' => Common::japanDateToDate(data_get($row, 'latest_data_orc_ai.insurance_period_2')),
                            'registration_date' => $registration_date,
                            'body_shape' => data_get($row, 'latest_vehicle_inspection_cert.CarShape'),
                            'vehicle_name' => $row->manufactor,
                            'chassis_number' => $vehicle_identification_number,
                            'model' => $row->type,
                            'user_name' => data_get($row, 'latest_vehicle_inspection_cert.UsernameLowLevelChar'),
                            'validity_expiration_date' => $row->inspection_expiration_date,
                            'riding_capacity' => data_get($row, 'latest_vehicle_inspection_cert.Cap'),
                            'itp_start_time' => Carbon::now()->format('Y-m-d H:i:s'),
                            'itp_end_time' => Carbon::now()->format('Y-m-d H:i:s'),
                            'itp_is_updated' => false,
                            'itp_error_message' => '',
                        ];
                        $this->listVehiclesScrape[$keyList] = $listsData;
                        $vehicle_identification_number = data_get($listsData, 'vehicle_identification_number');

                        $browser->visit('https://itpv3.transtron.fujitsu.com/F63')->pause(5000);

                        // chose menu department
                        $browser->select('div > div > div > div > div > section > nav > div > select', Arr::get($this->departmentMenu, data_get($row, 'department.id')));
                        Log::info("ITP department change===" . Arr::get($this->departmentOptionEdit, data_get($row, 'department.id')));
                        $browser->pause(5000);

//                            $browser->script('document.querySelector("html").style.overflow = "auto";');
//                            $browser->script('document.querySelector("div > div").style.height = "1000vh";');
//                            $browser->script('document.querySelector("div > div").style.width = "1000vh";');

                        //filter vehicle
                        $checkVhcExitInDp = false;
                        if ($browser->element('div > div > div > div > div > section > nav > div > ul > li > div > input')) {
                            $browser->type('div > div > div > div > div > section > nav > div > ul > li > div > input', $vehicle_identification_number);
                            $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[1]/div[2]/div/div[1]/div[3]/section/nav/div[2]/ul/li/div/a'))->click();
                            $browser->pause(1000);
                            if ($browser->element('#popupMessage')) {
                                if ($browser->driver->findElements(WebDriverBy::xpath('/html/body/div[4]/div[2]/div/nav/button'))) {
                                    $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[4]/div[2]/div/nav/button'))->click();
                                }
                            } else {
                                $checkVhcExitInDp = true;
                            }
                        }
                        // check insert or update
                        $indexEdit = null;
                        if ($checkVhcExitInDp) {
                            $elementViews = $browser->elements('div > div > div > div > div > div:nth-child(2) > div > div > div.wj-cell.wj-frozen.wj-frozen-col');
                            foreach ($elementViews as $key => $elem) {
                                $selectIndex = $key + 1;
                                if ($browser->element('div > div > div > div > div > div:nth-child(2) > div > div:nth-child(' . $selectIndex . ') > div.wj-cell.wj-frozen.wj-frozen-col')
                                    && $browser->element('div > div > div > div > div > div:nth-child(2) > div > div:nth-child(' . $selectIndex . ') > div.wj-cell.wj-frozen.wj-frozen-col')->getText() == $vehicle_identification_number) {
                                    $indexEdit = $selectIndex;
                                }
                            }
                        }

                        if ($indexEdit) {
                            // edit
                            $this->listVehiclesScrape[$keyList]['itp_is_updated'] = true;
                            error_log('ITP edit========' . $vehicle_identification_number);
                            Log::info('ITP edit========' . $vehicle_identification_number);
                            $browser->click('div > div > div > div > div > div:nth-child(2) > div > div:nth-child(' . $indexEdit . ') > div.wj-cell.wj-frozen.wj-frozen-col');
                            self::editVehicleITP($browser, $listsData, $keyList, $indexEdit);
                            $this->listVehiclesScrape[$keyList]['itp_end_time'] = Carbon::now()->format('Y-m-d H:i:s');
                        } else {
                            // add new
                            error_log('ITP add new');
                            Log::info('ITP add new======' . $vehicle_identification_number);
                            self::addNewVehicleITP($browser, $listsData, $vehicle_identification_number, $keyList);
                            $this->listVehiclesScrape[$keyList]['itp_end_time'] = Carbon::now()->format('Y-m-d H:i:s');
                        }
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());
                        error_log($e->getMessage());
                        if ($this->keyList) {
                            $this->listVehiclesScrape[$this->keyList]['itp_end_time'] = Carbon::now()->format('Y-m-d H:i:s');
                            $this->listVehiclesScrape[$this->keyList]['itp_error_message'] = 'Exception error:' . $e->getMessage();
                            $this->vehicle_identification_number_error[] = $this->listVehiclesScrape[$this->keyList]['vehicle_identification_number'] . '=>' . $e->getMessage();
                            if (Str::contains($e->getMessage(), '車両番号')) {
                                $this->errorBodyITPSendMails[] = [
                                    'vehicle_identification_number' => $this->listVehiclesScrape[$this->keyList]['vehicle_identification_number'],
                                    'plate' => $this->listVehiclesScrape[$this->keyList]['no_number_plate'],
                                    'department_name' => $this->listVehiclesScrape[$this->keyList]['department_code'],
                                ];
                            }
                        } else {
                            $this->vehicle_identification_number_error[] = $e->getMessage();
                        }
                    }
                }
                self::logOutITP($browser);
            }
            // Dừng ChromeDriver
            $driver->quit();
            Log::info('scrapITP============> call function quit() browser');
            $process->stop();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            error_log($e->getMessage());
            if ($this->keyList) {
                $this->listVehiclesScrape[$this->keyList]['itp_end_time'] = Carbon::now()->format('Y-m-d H:i:s');
                $this->listVehiclesScrape[$this->keyList]['itp_error_message'] = 'Exception error:' . $e->getMessage();
                $this->vehicle_identification_number_error[] = $this->listVehiclesScrape[$this->keyList]['vehicle_identification_number'] . '=>' . $e->getMessage();
            } else {
                $this->vehicle_identification_number_error[] = $e->getMessage();
            }

            if ($browser) {
                $browser->quit();
                Log::info('Exception scrapITP============> call function quit() browser');
            }
            if ($process) {
                $process->stop();
            }
            $this->errorMsg[] = 'Internal error: ' . $e->getMessage();
        }
    }

    private function loginITP(Browser $browser)
    {
        Log::info('ITP start loginITP function');
        $browser->visit('https://itpv3.transtron.fujitsu.com')
            ->keys('input[name=username]', 'izmb-free')
            ->keys('input[name=password]', 'iZumi@001')
            ->click('button.itp-btn-ok')
            ->pause(5000)
            ->visit('https://itpv3.transtron.fujitsu.com/F63')
            ->pause(5000);
        if ($browser->element('div > div > header > div > nav > ul > li:nth-child(6) > div')) {
            $texCheckLoginSuccess = $browser->element('div > div > header > div > nav > ul > li:nth-child(6) > div')->getText();
            $checkLoginSuccess = Str::contains($texCheckLoginSuccess, "イズミ物流株式会社");
            if (!$checkLoginSuccess) {
                $this->changeStatus('fail', 'Login ITP system not success');
                $this->vehicle_identification_number_error[] = 'Login ITP system not success';
                $this->downloadVehicleITPError[] = 'Login ITP system not success';
                $this->isLoginItpSuccess = false;
            }
        } else {
            $this->vehicle_identification_number_error[] = 'Login ITP system not success';
            $this->changeStatus('fail', 'Login ITP system not success');
            $this->downloadVehicleITPError[] = 'Login ITP system not success';
            $this->isLoginItpSuccess = false;
        }
        Log::info('ITP end loginITP function');
    }

    private function logOutITP(Browser $browser)
    {
        Log::info('ITP start logOutITP function');
        $browser->visit('https://itpv3.transtron.fujitsu.com/F63')->pause(5000);
        if ($browser->element('div > div > header > div > nav > ul > li:nth-child(8) > section > div')) {
            $browser->click('div > div > header > div > nav > ul > li:nth-child(8) > section > div');
            $browser->pause(1000);
            $browser->click('div > div > header > div > nav > ul > li:nth-child(8) > section > div > ul > li:nth-child(2) > button');
            $browser->pause(2000);
            $browser->click('div.modal-content.itp-dialog-default.itp-dialog-messagebox-default.itp-theme-blue.wj-control.wj-content.wj-popup > div.modal-body > div > nav > button.itp-btn-default.itp-btn-ok');
            Log::info('ITP logOutITP success');
        }
        Log::info('ITP end logOutITP function');
    }

    private function addNewVehicleITP(Browser $browser, $listsData, $vehicle_identification_number, $keyList)
    {
        Log::info('ITP start addNewVehicleITP function');
        $is_error = false;

        $browser->click('div > div > div > div > div > article > div.bottom-btn-box > ul > li:nth-child(7) > button')->pause(2000);
        $browser->type('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div.wj-cell.wj-frozen.wj-frozen-col > input', $vehicle_identification_number);
        $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div.wj-cell.wj-frozen.wj-frozen-col', WebDriverKeys::TAB);

        $browser->pause(1000);

        if ($browser->driver->findElements(WebDriverBy::xpath('/html/body/span'))) {
            if (Str::contains($browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getAttribute('class'), 'itpErrorNotice')) {
                $is_error = true;
                Log::info("Data import error or other error" . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText());
                $this->listVehiclesScrape[$keyList]['itp_error_message'] = 'Error:' . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText();
                $this->vehicle_identification_number_error[] = $this->listVehiclesScrape[$keyList]['vehicle_identification_number'] . '=>' . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText();
            }
        }
        $listStyle = $this->listsCellMap;
        for ($i = 1; $i <= 48; $i++) {
            if ((is_array($listStyle) && count($listStyle) <= 0) || $is_error) {
                break;
            }
            $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true]', WebDriverKeys::RIGHT);
            $browser->pause(500);
//            $browser->click('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div.wj-state-active');
            $key = $this->getAndCheckElement($browser, 'div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div.wj-state-active');
            $elem = null;
            if ($key) {
                $elem = Arr::get($listStyle, $key);
            } else {
                continue;
            }
            $elemCheck = $browser->element('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div.wj-state-active[style*="left: ' . $elem . '"]');
            if ($elemCheck) {
                $elemCheck->click();
                $browser->pause(500);
                if ($browser->element('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div[style*="left: ' . $elem . '"] > input')) {
                    error_log("vào if Tạo dữ liệu 111111111111");
//                        $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div[style*="left: ' . $elem . '"] > input', '{backspace}');
//                        $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div[style*="left: ' . $elem . '"] > input', '{backspace}');
//                        $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div[style*="left: ' . $elem . '"] > input', '{backspace}');
                    $dataInput = Arr::get($listsData, $key);
                    Log::info('ITP add new enter input ' . $key . $elem . ' ========' . $dataInput);
                    $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div[style*="left: ' . $elem . '"] > input', $dataInput);
                    $browser->pause(500);
                    $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div[style*="left: ' . $elem . '"]', WebDriverKeys::TAB);
                    $browser->pause(500);
                    if ($browser->driver->findElements(WebDriverBy::xpath('/html/body/span'))) {
                        if (Str::contains($browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getAttribute('class'), 'itpErrorNotice')) {
                            $is_error = true;
                            Log::info("Data import error or other error" . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText());
                            $textErr = $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText();
                            $this->listVehiclesScrape[$keyList]['itp_error_message'] = 'Error:' . $textErr ? $textErr : 'error data input in filed ' . $key;
                            $this->vehicle_identification_number_error[] = $this->listVehiclesScrape[$keyList]['vehicle_identification_number'] . '=>' . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText();
                        }
                    }
                    unset($listStyle[$key]);
                }
            }
        }
        Log::info('ITP end addNewVehicleITP function');
        //save
        if (!$is_error) {
            self::saveAddAndEditITP($browser, $keyList);
        }
    }

    private function editVehicleITP(Browser $browser, $listsData, $keyList, $indexEdit)
    {
        Log::info('ITP start editVehicleITP function');
        $listStyle = $this->listsCellMap;
        $is_error = false;
        for ($i = 1; $i <= 48; $i++) {
            if ((is_array($listStyle) && count($listStyle) <= 0) || $is_error) {
                break;
            }
            $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true]', WebDriverKeys::RIGHT);
//            $browser->click('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div.wj-state-active');
            $browser->pause(500);
            $key = $this->getAndCheckElement($browser, 'div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div.wj-state-active');
            $elem = null;
            if ($key) {
                $elem = Arr::get($listStyle, $key);
            } else {
                continue;
            }
            $elemCheck = $browser->element('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div.wj-state-active[style*="left: ' . $elem . '"]');
            if ($elemCheck) {
                $elemCheck->click();
                $browser->pause(500);
                if ($browser->element('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div[style*="left: ' . $elem . '"] > input')) {
                    error_log("vào if Tạo dữ liệu 111111111111");
                    $dataInput = Arr::get($listsData, $key);
                    Log::info('ITP edit enter input ' . $key . $elem . ' ========' . $dataInput);
                    $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div[style*="left: ' . $elem . '"] > input', $dataInput);
                    unset($listStyle[$key]);
                    $browser->pause(500);
                    $browser->keys('div > div > div > div > div > div:nth-child(2) > div > div[aria-selected=true] > div[style*="left: ' . $elem . '"]', WebDriverKeys::ENTER);
                    $browser->pause(500);
                    if ($browser->driver->findElements(WebDriverBy::xpath('/html/body/span'))) {
                        if (Str::contains($browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getAttribute('class'), 'itpErrorNotice')) {
                            $is_error = true;
                            Log::info("Data import error or other error" . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText());
                            $textErr = $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText();
                            $this->listVehiclesScrape[$keyList]['itp_error_message'] = 'Error:' . $textErr ? $textErr : 'error data input in filed ' . $key;
                            $this->vehicle_identification_number_error[] = $this->listVehiclesScrape[$keyList]['vehicle_identification_number'] . '=>' . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText();
                        }
                    }
                    $vhcIndexed = $this->getCurrentVhcIndex($browser, $listsData);
                    if (!$vhcIndexed) {
                        Log::info('ITP vhc selected false');
                        $is_error = true;
                        $this->listVehiclesScrape[$keyList]['itp_error_message'] = 'Error:';
                        $this->vehicle_identification_number_error[] = $this->listVehiclesScrape[$keyList]['vehicle_identification_number'] . '=>' . 'Unable to locate element with selector ' . '[div > div > div > div > div > div:nth-child(2) > div > div:nth-child(' . $indexEdit . ') > div[style*="left: ' . $elem . '"]]';
                    } else {
                        if ($browser->element('div > div > div > div > div > div:nth-child(2) > div > div:nth-child(' . $vhcIndexed . ') > div[style*="left: ' . $elem . '"]')) {
                            error_log("vao check row index");
                            Log::info('ITP edit set row index');
                            $browser->click('div > div > div > div > div > div:nth-child(2) > div > div:nth-child(' . $vhcIndexed . ') > div[style*="left: ' . $elem . '"]');
                        } else {
                            $is_error = true;
                            $this->listVehiclesScrape[$keyList]['itp_error_message'] = 'Error:';
                            $this->vehicle_identification_number_error[] = $this->listVehiclesScrape[$keyList]['vehicle_identification_number'] . '=>' . 'Unable to locate element with selector ' . '[div > div > div > div > div > div:nth-child(2) > div > div:nth-child(' . $indexEdit . ') > div[style*="left: ' . $elem . '"]]';
                        }
                    }
                    $browser->pause(500);
                }
            }
        }
        Log::info('ITP end editVehicleITP function');
        //save
        if (!$is_error) {
            self::saveAddAndEditITP($browser, $keyList);
        }
    }

    private function getCurrentVhcIndex(Browser $browser, $listsData)
    {
        $selected = null;
        $vehicle_identification_number = data_get($listsData, 'vehicle_identification_number');
        $elementViews = $browser->elements('div > div > div > div > div > div:nth-child(2) > div > div > div.wj-cell.wj-frozen.wj-frozen-col');
        foreach ($elementViews as $key => $elem) {
            $selectIndex = $key + 1;
            if ($browser->element('div > div > div > div > div > div:nth-child(2) > div > div:nth-child(' . $selectIndex . ') > div.wj-cell.wj-frozen.wj-frozen-col')
                && $browser->element('div > div > div > div > div > div:nth-child(2) > div > div:nth-child(' . $selectIndex . ') > div.wj-cell.wj-frozen.wj-frozen-col')->getText() == $vehicle_identification_number) {
                $selected = $key + 1;
                break;
            }
        }
        return $selected;
    }

    private function getAndCheckElement(Browser $browser, $strElement)
    {
        $browser->pause(500);
        $elemCheck = $browser->element($strElement);
        if ($elemCheck) {
            $leftCss = $elemCheck->getCSSValue('left');
            if (in_array($leftCss, $this->listsCellMap)) {
                $arrFlip = array_flip($this->listsCellMap);
                return Arr::get($arrFlip, $leftCss);
            }
        }
        return false;
    }

    private function saveAddAndEditITP(Browser $browser, $keyList)
    {
        Log::info('ITP start saveAddAndEditITP function');
        $saveSuccessCheck = [
            'データが変更されていません。', //"ko có dữ liệu thay đổi"
            'データを保存しました。', //"Đã lưu dữ liệu"
        ];
        $browser->click('div > div > div > div > div > article > div.bottom-btn-box > ul > li:nth-child(8) > button')->pause(3000);
        if ($browser->element('#popupMessage')) {
            $texCheckSuccess = $browser->element('#popupMessage')->getText();
            $checkSuccess = Str::contains($texCheckSuccess, $saveSuccessCheck);
            if (!$checkSuccess) {
                $this->listVehiclesScrape[$keyList]['itp_error_message'] = 'Exception error:' . $texCheckSuccess;
                $this->vehicle_identification_number_error[] = $this->listVehiclesScrape[$keyList]['vehicle_identification_number'] . '=>' . $texCheckSuccess;
                if (Str::contains($texCheckSuccess, '車両番号')) {
                    $this->errorBodyITPSendMails[] = [
                        'vehicle_identification_number' => $this->listVehiclesScrape[$this->keyList]['vehicle_identification_number'],
                        'plate' => $this->listVehiclesScrape[$this->keyList]['no_number_plate'],
                        'department_name' => $this->listVehiclesScrape[$this->keyList]['department_code'],
                    ];
                }
            }
            if ($browser->driver->findElements(WebDriverBy::xpath('/html/body/span'))) {
                if (Str::contains($browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getAttribute('class'), 'itpErrorNotice')) {
                    Log::info("Error:" . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText());
                    $this->listVehiclesScrape[$keyList]['itp_error_message'] = 'Error:' . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText();
                    $this->vehicle_identification_number_error[] = $this->listVehiclesScrape[$keyList]['vehicle_identification_number'] . '=>' . $browser->driver->findElement(WebDriverBy::xpath('/html/body/span'))->getText();
                }
            }
            if ($browser->driver->findElements(WebDriverBy::xpath('/html/body/div[4]/div[2]/div/nav/button'))) {
                $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[4]/div[2]/div/nav/button'))->click();
                $browser->pause(3000);
            }
        }
//        $browser->visit('https://itpv3.transtron.fujitsu.com/F63')->pause(5000);
        Log::info('ITP end saveAddAndEditITP function');
//        $browser->click('body > div.modal-content.itp-dialog-default.itp-dialog-messagebox-default.itp-theme-blue.wj-control.wj-content.wj-popup > div.modal-body > div > nav > button')->pause(10000);
    }

    private function getVehicleDataMap()
    {
        $startDateTime = Carbon::now()->subDay();
        $endDateTime = Carbon::now();
        $listVhcCheck = [];

        $vehicle_inspection_cert = VehicleInspectionCert::query()
            ->whereBetween('updated_at', [$startDateTime, $endDateTime])->pluck('vehicle_id')->toArray();
        if ($vehicle_inspection_cert && count($vehicle_inspection_cert) > 0) {
            $listVhcCheck = array_merge($vehicle_inspection_cert, $listVhcCheck);
        }
        $vehicle_data_orc_ai = VehicleDataORCAI::query()
            ->whereBetween('updated_at', [$startDateTime, $endDateTime])->pluck('vehicle_id')->toArray();
        if ($vehicle_data_orc_ai && count($vehicle_data_orc_ai) > 0) {
            $listVhcCheck = array_merge($vehicle_data_orc_ai, $listVhcCheck);
        }
        $dataVehicle = Vehicle::query()
            ->with([
                'department',
                'plate_history',
                'latest_data_orc_ai' => function ($query) {
                    return $query->select(['vehicle_data_orc_ai.id', 'vehicle_data_orc_ai.vehicle_id', 'insurance_period_2']);
                },
                'latest_vehicle_inspection_cert' => function ($query) {
                    return $query->select(['vehicle_inspection_cert.id', 'vehicle_inspection_cert.vehicle_id', 'RegGrantDateE', 'RegGrantDateY', 'RegGrantDateM', 'RegGrantDateD', 'UsernameLowLevelChar', 'Cap', 'CarShape']);
                },
            ])
            ->whereBetween('updated_at', [$startDateTime, $endDateTime])
            ->where('d1d_not_installed', '<>', 1)
            ->orWhereIn('id', $listVhcCheck)
            ->orderBy('department_id')
            ->get();
        if ($dataVehicle->count() > 0) {
            return $dataVehicle;
        }
        return false;
    }

    private function storeFileCsvItp()
    {
        Log::info('ITP start storeFileCsvItp function');
        $fileNameSrc = 'vehicle_itp.csv';
        $envBasePath = Common::getEnvBasePath();
        $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        $fileName = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_' . $this->dataItem->id . '_' . $fileNameSrc;
//        if (!Storage::exists($path)) {
//            Storage::makeDirectory($path);
//        }
        Excel::store(new VehicleExportForItp($this->listVehiclesScrape), $path . '/' . $fileName, $this->disk);

        $fileData = File::create([
            'file_path' => $path . '/' . $fileName,
            'file_name' => $fileName,
            "file_extension" => pathinfo($fileName, PATHINFO_EXTENSION),
            "file_size" => Storage::disk($this->disk)->size($path . '/' . $fileName),
            "file_url" => Storage::disk($this->disk)->url($path . '/' . $fileName),
            "file_sys_disk" => $this->disk,
        ]);
        if (!$this->vehicle_identification_number_error) {
            $this->changeStatus('success', null, null, null, $fileId = $fileData->id);
        } else {
            $this->changeStatus('fail', $this->vehicle_identification_number_error, null, null, $fileId = $fileData->id);
        }
        Log::info('ITP end storeFileCsvItp function');
        return $fileData;
    }

    private function downloadDataITP()
    {
        if (!Storage::disk('local')->exists('download_data_itp')) {
            Storage::disk('local')->makeDirectory('download_data_itp');
        }
        $browser = null;
        $process = null;
        $port = null;
        try {
            $portCommand = CommonChromeDriver::startChromeDriver();
            $port = $portCommand['port'];
            // Mở tiến trình và chạy lệnh trong nền
            $process = new Process(explode(' ', $portCommand['command']));
            $process->start();
            sleep(5);
            // Cấu hình các tùy chọn cho ChromeDriver
            $options = new ChromeOptions();
            $arguments = Config::get('scrape_dusk.chrome');
            $options->addArguments($arguments);

            $preferences = [
                'download.default_directory' => Storage::disk('local')->path('download_data_itp'),
                'savefile.default_directory' => Storage::disk('local')->path('download_data_itp'),
                'download.prompt_for_download' => false,
                'download.directory_upgrade' => true,
                'safebrowsing.enabled' => false,
                'safebrowsing.disable_download_protection' => true,
            ];
            $options->setExperimentalOption('prefs', $preferences);

            // Khởi động ChromeDriver với các tùy chọn
            $driver = RemoteWebDriver::create('http://localhost:' . $port,
                DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY, $options
                )
            );

            // Bắt đầu thu thập dữ liệu
            $browser = new Browser($driver);

            $this->loginITP($browser);
            if ($this->isLoginItpSuccess) {
                $this->downloadVehicleCostInITP($browser);
                $this->downloadVehicleTransportationInITP($browser);
                $this->unzipAndImportFileItp();
                self::logOutITP($browser);
            }

            // Dừng ChromeDriver
            $driver->quit();
            Log::info('downloadDataITP============> call function quit() browser');
            $process->stop();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            error_log($e->getMessage());
            $this->downloadVehicleITPError[] = $e->getMessage();
            if ($browser) {
                $browser->quit();
                Log::info('Exception downloadDataITP============> call function quit() browser');
            }
            if ($process) {
                $process->stop();
            }
            $this->errorMsg[] = 'Internal error: ' . $e->getMessage();
        }
        if ($this->downloadVehicleITPError && count($this->downloadVehicleITPError) > 0) {
            $this->changeStatus('fail', $this->downloadVehicleITPError);
        } else {
            $this->changeStatus('success');
        }
        //remove zip file not use
        $contents = $this->dataItem->content;
        if ($contents && count($contents) > 0) {
            foreach ($contents as $content) {
                if (Storage::exists(Arr::get($content, 'file_path'))) {
                    Storage::delete(Arr::get($content, 'file_path'));
                    File::query()->where('id', Arr::get($content, 'file_id'))->first()->delete();
                }
            }
        }
    }

    private function downloadVehicleCostInITP(Browser $browser)
    {
        Log::info('ITP start downloadVehicleCostInITP function');
        $browser->visit('https://itpv3.transtron.fujitsu.com/F45');
        $browser->pause(5000);
        if ($browser->element('div > div > div > div > div > section > article > div > div:nth-child(1) > article > div > div > div > div > div.wj-node')) {
            $browser->element('div > div > div > div > div > section > article > div > div:nth-child(1) > article > div > div > div > div > div.wj-node')->click();
            $date = Carbon::parse($this->dateGetData);
            if ($date && in_array($date->day, [1, 2])) {
                $browser->click('div > div > div > div > div > section > article > div > ul > li:nth-child(3) > button');
//                $browser->pause(180000);
                $this->pauseAndCheckFileItp($browser, '車両別経費集計表', 180000);
                //save file
                $this->storeZipFileItp('車両別経費集計表', Carbon::now()->subMonth()->firstOfMonth()->format('Y/m/d'), Carbon::now()->subMonth()->endOfMonth()->format('Y/m/d'));
            }

            $firstOfMonth = Carbon::parse($this->dateGetData)->firstOfMonth()->format('Y/m/d');
            $endOfMonth = Carbon::parse($this->dateGetData)->endOfMonth()->format('Y/m/d');
            $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[1]/div[2]/div/div[1]/div[3]/section/article/div[1]/div[3]/article[1]/div[2]/p[2]/div/div/div/div/div/input'))
                ->click()
                ->sendKeys($endOfMonth)->sendKeys(WebDriverKeys::ENTER);
            $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[1]/div[2]/div/div[1]/div[3]/section/article/div[1]/div[3]/article[1]/div[2]/p[1]/div/div/div/div/div/input'))
                ->click()
                ->sendKeys($firstOfMonth)->sendKeys(WebDriverKeys::ENTER);
            $browser->pause(1000);
            $browser->click('div > div > div > div > div > section > article > div > ul > li:nth-child(3) > button');
            //$browser->pause(180000);
            $this->pauseAndCheckFileItp($browser, '車両別経費集計表', 180000);
            $this->storeZipFileItp('車両別経費集計表', $firstOfMonth, $endOfMonth);
        }
        Log::info('ITP end downloadVehicleCostInITP function');
    }

    private function downloadVehicleTransportationInITP(Browser $browser)
    {
        Log::info('ITP start downloadVehicleTransportationInITP function');
        $browser->visit('https://itpv3.transtron.fujitsu.com/F4C');
        $browser->pause(5000);
        if ($browser->element('article > div > div > div > div > div.wj-node')) {
            $browser->element('article > div > div > div > div > div.wj-node')->click();
            $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[1]/div[2]/div/div[1]/div[3]/section/article/div[1]/div[2]/article/div/form/p[1]/div[1]'))->click();
            $date = Carbon::parse($this->dateGetData);
            if ($date && in_array($date->day, [1, 2])) {
                $browser->click('div > div > div > div > div > section > article > div > ul > li:nth-child(3) > button');
//                $browser->pause(180000);
                $this->pauseAndCheckFileItp($browser, '車両別運行実績表', 180000);
                $this->storeZipFileItp('車両別運行実績表', Carbon::now()->subMonth()->firstOfMonth()->format('Y/m/d'), Carbon::now()->subMonth()->endOfMonth()->format('Y/m/d'));
            }

            $firstOfMonth = Carbon::parse($this->dateGetData)->firstOfMonth()->format('Y/m/d');
            $endOfMonth = Carbon::parse($this->dateGetData)->endOfMonth()->format('Y/m/d');
            $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[1]/div/div/div[1]/div[3]/section/article/div[1]/div[3]/article[1]/div[2]/p[2]/div/div/div/div/div/input'))
                ->click()
                ->sendKeys($endOfMonth)->sendKeys(WebDriverKeys::ENTER);
            $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[1]/div/div/div[1]/div[3]/section/article/div[1]/div[3]/article[1]/div[2]/p[1]/div/div/div/div/div/input'))
                ->click()
                ->sendKeys($firstOfMonth)->sendKeys(WebDriverKeys::ENTER);
            $browser->pause(1000);
            $browser->click('div > div > div > div > div > section > article > div > ul > li:nth-child(3) > button');
//            $browser->pause(180000);
            $this->pauseAndCheckFileItp($browser, '車両別運行実績表', 180000);
            $this->storeZipFileItp('車両別運行実績表', $firstOfMonth, $endOfMonth);
        }
        Log::info('ITP end downloadVehicleTransportationInITP function');
    }


    private function storeZipFileItp($type, $startDate, $enDate)
    {
        Log::info('ITP start storeZipFileItp function');
        $listFiles = Storage::disk('local')->files('download_data_itp');
        $checkFileExits = false;
        foreach ($listFiles as $file) {
            $basename = pathinfo($file, PATHINFO_BASENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (Str::contains($basename, $type) && $extension == 'zip') {
                $checkFileExits = true;
                //store file and import file
                $fileName = md5(Str::uuid()->toString()) . '_' . $this->dataItem->id . '_' . $basename;
                $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
                if (!Storage::exists($path)) {
                    Storage::makeDirectory($path);
                }
                $checkStoreFile = Storage::disk('public')->writeStream($path . '/' . $fileName, Storage::disk('local')->readStream($file));
                if ($checkStoreFile) {
                    $fileData = File::create([
                        'file_path' => $path . '/' . $fileName,
                        'file_name' => $fileName,
                        "file_extension" => $extension,
                        "file_size" => Storage::size($path . '/' . $fileName),
                    ]);

                    $this->dataContent[] = [
                        'type' => $type,
                        'from' => $startDate,
                        'to' => $enDate,
                        'file_id' => $fileData->id,
                        'file_path' => $fileData->file_path,
                    ];
                    Storage::disk('local')->delete($file);
                }
            }
        }
        if (!$checkFileExits) {
            $this->downloadVehicleITPError[] = [
                'type' => $type,
                'from' => $startDate,
                'to' => $enDate,
                'file_id' => 'File zip not exit',
            ];
            $this->changeStatus('excluding', $this->downloadVehicleITPError, null, null);
        }

        $this->changeStatus('excluding', null, null, null);
        Log::info('ITP end storeZipFileItp function');
    }

    private function pauseAndCheckFileItp(Browser $browser, $type, $totalIntendTimeDownload = 100000, $interVal = 100)
    {
        for ($i = 0; $i <= $interVal; $i++) {
            if ($i > 0) {
                $checkFile = $this->checkZipFileItp($type);
                if ($checkFile) {
                    $browser->pause($totalIntendTimeDownload / $interVal);
                    break;
                } else {
                    $browser->pause($totalIntendTimeDownload / $interVal);
                }
            }
        }
    }

    private function checkZipFileItp($type)
    {
        Log::info('ITP start checkZipFileItp function');
        $listFiles = Storage::disk('local')->files('download_data_itp');
        $checkFileExits = false;
        foreach ($listFiles as $file) {
            $basename = pathinfo($file, PATHINFO_BASENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (Str::contains($basename, $type) && $extension == 'zip') {
                $checkFileExits = true;
                break;
            }
        }
        Log::info('ITP end checkZipFileItp function');
        return $checkFileExits;
    }

    private function unzipAndImportFileItp()
    {
        Log::info('ITP start unzipAndImportFileItp function');
        $contents = $this->dataItem->content;
        if ($contents && count($contents) > 0) {
            foreach ($contents as $content) {
                $type = Arr::get($content, 'type');
                $from = Carbon::parse(Arr::get($content, 'from'));
                $file_path = Storage::path(Arr::get($content, 'file_path'));
                $type_code = $type == '車両別経費集計表' ? 'etc' : 'km_l';
                $this->openFileZip($file_path, $type_code, $from);
            }
            $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path);
            }

            $fileNameZip = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_' . $this->dataItem->id . '_data-connection.zip';
            // Create ZipArchive Obj
            $zip = new ZipArchive;
            if ($zip->open(Storage::path($path . '/' . $fileNameZip), ZipArchive::CREATE) === TRUE) {
                foreach ($contents as $content) {
                    $zip->addFile(Storage::path(Arr::get($content, 'file_path')), basename(Storage::path(Arr::get($content, 'file_path'))));
                }
                $zip->close();
            }

            $envBasePath = Common::getEnvBasePath();
            $pathS3 = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
            Storage::disk($this->disk)->put($pathS3 . '/' . $fileNameZip, Storage::disk()->get($path . '/' . $fileNameZip));

            $fileDataZip = File::create([
                'file_path' => $pathS3 . '/' . $fileNameZip,
                'file_name' => $fileNameZip,
                "file_extension" => pathinfo($fileNameZip, PATHINFO_EXTENSION),
                "file_size" => Storage::disk($this->disk)->size($pathS3 . '/' . $fileNameZip),
                "file_url" => Storage::disk($this->disk)->url($pathS3 . '/' . $fileNameZip),
                "file_sys_disk" => $this->disk,
            ]);
            unlink(Storage::path($path . '/' . $fileNameZip));
            $this->changeStatus('excluding', null, null, null, $fileDataZip->id);
        }
        Log::info('ITP end unzipAndImportFileItp function');
    }

    private function openFileZip($pathZipFile, $type_code, $from)
    {
        $dpMapZipItp = $this->dpMapZipItp;
        $zip = new ZipArchive();
        if ($zip->open($pathZipFile) === TRUE) {
            foreach ($dpMapZipItp as $k_dp => $v_dp) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileNameInZip = $zip->getNameIndex($i);
                    if (Str::contains($fileNameInZip, $k_dp)) {
                        $fileContent = $zip->getStreamIndex($i);
                        $fileName = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_' . $k_dp;
                        $path = PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd') . '/unzip';
                        Storage::writeStream($path . '/' . $fileName, $fileContent);
                        if (!Storage::exists($path)) {
                            Storage::makeDirectory($path);
                        }
                        if ($type_code == 'etc' && !$this->checkEtcFileNeed($path . '/' . $fileName)) {
                            break;
                        }
                        try {
                            Common::setInputEncoding(Storage::path($path . '/' . $fileName));
                            Excel::import(new VehicleITPImport($from, $type_code, $v_dp, $path . '/' . $fileName), Storage::path($path . '/' . $fileName));
                        } catch (\Exception $e) {
                            Log::error($e->getMessage());
                            error_log($e->getMessage());
                        }
                        if (Storage::exists($path . '/' . $fileName)) {
                            Storage::delete($path . '/' . $fileName);
                        }
                    }
                }
            }
            $zip->close();
        }
    }

    private function checkEtcFileNeed($pathFileName)
    {
        try {
            $contents = Storage::disk()->get($pathFileName);
            $file_handle = fopen('php://memory', 'r+');
            fwrite($file_handle, $contents);
            rewind($file_handle);
            $headers = fgetcsv($file_handle);
            fclose($file_handle);
            if ($headers && count($headers) <= 18) {
                return true;
            } else {
                return false;
            }
        } catch (FileNotFoundException $e) {
            return false;
        }
    }

    public function alert()
    {
        if ($this->errorBodyITPSendMails) {
            Log::info('ITP start send errorITPSendMails function');
            $body = "以下の車両番号が他拠点に存在していたため、ITPに登録ができませんでした。\n";
            $body .= "ITPにて車両の拠点移動を実行後、再度連携を実施してください。\n";
            foreach ($this->errorBodyITPSendMails as $err) {
                $body .= "===========================================================\n";
                $body .= "車両番号 :" . $err['vehicle_identification_number'] . "\n";
                $body .= "車両名称 :" . $err['plate'] . "\n";
                $body .= "所属コード :" . $err['department_name'] . "\n";
            }
            $env = app()->environment();
            foreach ($this->errorITPSendMails as $key => $mail) {
                Mail::raw($body, function ($message) use ($env, $mail) {
                    $message->subject("【ITP連携_車両重複エラー】 env: " . $env)->to($mail);
                });
            }
            Log::info('ITP end send errorITPSendMails function');
        }
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
