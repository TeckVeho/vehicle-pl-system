<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace Repository;

use App\Helpers\CommonChromeDriver;
use App\Models\Department;
use App\Models\TimesheetData;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Helper\Common;
use App\Events\MessageSentEvent;
use App\Models\Employee;
use App\Models\File;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Process\Process;
use ZipArchive;
use App\Imports\ClomoImport;

class EmployeeServiceRepository extends BaseRepository
{

    protected $dataConnection;
    protected $dataItem;
    protected $dataContent;
    protected $isLoginClomoSuccess = true;
    protected $errorMsg;
    protected $fileModel;
    protected $clomoFilePath;
    protected $memory_limit;
    protected $type = 'passive';
    protected $browser;
    protected $disk = 'public';

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        $this->disk = Common::checkS3Conn() ? 's3' : 'public';
        return Employee::class;
    }

    public function scrapeClomo($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');

        //remove file befor run
        if (!Storage::disk('local')->exists('clomo_download')) {
            Storage::disk('local')->makeDirectory('clomo_download');
        }
        $listFiles = Storage::disk('local')->files('clomo_download');
        foreach ($listFiles as $file) {
            Storage::disk('local')->delete($file);
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
                'download.default_directory' => Storage::disk('local')->path('clomo_download'),
                'savefile.default_directory' => Storage::disk('local')->path('clomo_download'),
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
            $this->loginClomo($browser);
            if ($this->isLoginClomoSuccess) {
                $this->exportCsvClomo($browser);
                if (!$this->errorMsg || count($this->errorMsg) <= 0) {
                    $this->importClomoCSVToDB();
                }
            }
            // Dừng ChromeDriver
            $driver->quit();
            Log::info('scrapeClomo============> call function quit() browser');
            $process->stop();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            error_log($e->getMessage());
            if ($browser) {
                $browser->quit();
                Log::info('Exception scrapeClomo============> call function quit() browser');
            }
            if ($process) {
                $process->stop();
            }
            $this->errorMsg[] = 'Internal error: ' . $e->getMessage();
        }
        if ($this->errorMsg && count($this->errorMsg) > 0) {
            $this->changeStatus('fail', $this->errorMsg, null, null, $this->fileModel ? $this->fileModel->id : null);
        } else {
            $this->changeStatus('success', null, null, null, $this->fileModel ? $this->fileModel->id : null);
        }
        return;
    }

    private function loginClomo(Browser $browser)
    {
        Log::info('Clomo start loginITP function');
        $browser->visit('https://clomo.com/panel/daiseigroup/login')
            ->keys('#login', 'kohei.ikeda@veho-works.com')
            ->keys('#password', 'Veho4649')
            ->click('input.blackButton')
            ->pause(5000);
        $chkLoginSuccess = $browser->driver->getCurrentURL();
        if ($chkLoginSuccess !== 'https://clomo.com/panel/daiseigroup') {
            error_log("login fail");
            $this->changeStatus('fail', 'Login Clomo system not success');
            $this->errorMsg[] = 'Login Clomo system not success';
            $this->isLoginClomoSuccess = false;
        }
        Log::info('Clomo end loginITP function');
    }

    private function exportCsvClomo(Browser $browser)
    {
        Log::info('Clomo start exportCsvClomo function');
        $browser->visit('https://clomo.com/panel/daiseigroup/mobile_devices')->pause(5000);
        $browser->click('#ext-gen116')->pause(1000);
        $browser->click('#ext-comp-1032')->pause(5000);
        $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[16]/div[2]/div[1]/div/div/div/div/div/div/div[1]/div/div/div/div[2]/div[1]/div/label[3]/input'))->click();
        $browser->pause(2000);
        $browser->driver->findElement(WebDriverBy::xpath('/html/body/div[16]/div[2]/div[2]/div/div/div/div[1]/table/tbody/tr/td[2]/table/tbody/tr/td[1]/table/tbody/tr/td[2]/table/tbody/tr[2]/td[2]/em/button'))->click();
        $browser->pause(20000);
        //check file
        $listFiles = Storage::disk('local')->files('clomo_download');
        $checkFileExits = false;
        foreach ($listFiles as $file) {
            $basename = pathinfo($file, PATHINFO_BASENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (Str::contains($basename, 'devices_') && $extension == 'csv') {
                //store file and import file
                $checkFileExits = true;
                $this->storeFileCsvClomo($basename, $file);
                break;
            }
        }
        if (!$checkFileExits) {
            $this->errorMsg[] = "No files csv are exported";
        }
        Log::info('Clomo end exportCsvClomo function');
    }

    private function storeFileCsvClomo($fileNameSrc, $currentPathFile)
    {
        Log::info('Clomo start storeFileCsvItp function');
        $fileName = md5(Str::uuid()->toString()) . '_' . $this->dataItem->id . '_' . $fileNameSrc;

        $envBasePath = Common::getEnvBasePath();
        $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');

        if (!Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->makeDirectory($path);
        }

        Storage::disk($this->disk)->writeStream($path . '/' . $fileName, Storage::disk('local')->readStream($currentPathFile));

        $fileData = File::create([
            'file_path' => $path . '/' . $fileName,
            'file_name' => $fileName,
            "file_extension" => pathinfo($fileName, PATHINFO_EXTENSION),
            "file_size" => Storage::disk($this->disk)->size($path . '/' . $fileName),
            "file_url" => Storage::disk($this->disk)->url($path . '/' . $fileName),
            "file_sys_disk" => $this->disk,
        ]);
        $this->fileModel = $fileData;
        $this->clomoFilePath = $path . '/' . $fileName;
        Log::info('Clomo end storeFileCsvItp function');
        return $fileData;
    }

    private function importClomoCSVToDB()
    {
//        ini_set('memory_limit', '-1');
        Common::setInputEncoding($this->clomoFilePath, true);

        $import = new ClomoImport();
        $import->import($this->clomoFilePath, $this->disk);

        foreach ($import->failures() as $failure) {
            $failure->row(); // row that went wrong
            $failure->attribute(); // either heading key (if using heading row concern) or column index
            $failure->errors(); // Actual error messages from Laravel validator
            $failure->values(); // The values of the row that has failed.
            $this->errorMsg[] = Arr::get($failure->errors(), 0);
        }

    }

    public function saveTimeSheetToTable($dataConnection, $dataItem, $date, $department_name)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');

        $urlCallApi = CLOUD_DATA_TIMESHEET_DEV;
        if (App::environment('staging')) {
            $urlCallApi = CLOUD_DATA_TIMESHEET_STAGING;
        }
        if (App::environment('production')) {
            $urlCallApi = CLOUD_DATA_TIMESHEET_PRODUCTION;
        }
        $dateTime = Carbon::now();
        if ($date) {
            $dateTime = Carbon::parse($date);
        }
        $month = $dateTime->month;
        $year = $dateTime->year;
        $errorConnect = null;
        $contentBody = [];

        $department = Department::query()->where('name', $department_name)->first();
        if ($department) {
            $response = Http::timeout(60)->withoutVerifying()->get($urlCallApi, ['month' => $dateTime->format('Y-m'), 'department' => $department->name]);
            if ($response->getStatusCode() !== 200) {
                $body = json_decode($response->getBody());
                $errorConnect[] = [
                    'body' => $body,
                    'status_code' => $response->getStatusCode(),
                ];
            } else {
                $body = json_decode($response->getBody());
                if ($body) {
                    $contentBody[$department->id] = $body;
                    foreach ($body->data->time_sheet_index as $timeSheetIndex) {
                        foreach ($body->data->time_sheet_salary as $timeSheetSalary) {
                            if (
                                $timeSheetIndex->employee_id == $timeSheetSalary->employee_id
                                && $timeSheetIndex->job_type == $timeSheetSalary->job_type
                            ) {
                                $systemTimesheet = TimesheetData::where('employee_id', $timeSheetIndex->employee_id)
                                    ->where('department_id', $department->id)
                                    ->where('job_type', $timeSheetIndex->job_type)
                                    ->where('year', $year)
                                    ->where('month', $month)
                                    ->first();
                                if ($systemTimesheet) {
                                    $systemTimesheet->scheduled_wh = $timeSheetIndex->scheduled_working_hours;
                                    $systemTimesheet->overtime_salary_wh = $timeSheetIndex->overtime_salary_working_hours;
                                    $systemTimesheet->midnight_wh = $timeSheetIndex->midnight_working_hours;
                                    $systemTimesheet->holiday_wh = $timeSheetIndex->hard_work_2_working_hours;
                                    $systemTimesheet->actual_working_day = $timeSheetIndex->actual_working_days;
                                    $systemTimesheet->working_day = $timeSheetSalary->working;
                                    $systemTimesheet->transportation_cp = $timeSheetSalary->transportation_cp;
                                    $systemTimesheet->save();
                                } else {
                                    TimesheetData::create([
                                        'employee_id' => $timeSheetIndex->employee_id,
                                        'department_id' => $department->id,
                                        'job_type' => $timeSheetIndex->job_type,
                                        'scheduled_wh' => $timeSheetIndex->scheduled_working_hours,
                                        'overtime_salary_wh' => $timeSheetIndex->overtime_salary_working_hours,
                                        'midnight_wh' => $timeSheetIndex->midnight_working_hours,
                                        'holiday_wh' => $timeSheetIndex->hard_work_2_working_hours,
                                        'actual_working_day' => $timeSheetIndex->actual_working_days,
                                        'working_day' => $timeSheetSalary->working,
                                        'transportation_cp' => $timeSheetSalary->transportation_cp,
                                        'year' => $year,
                                        'month' => $month,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->storeFileContentData($contentBody);
        if ($errorConnect && count($errorConnect) > 0) {
            $this->changeStatus('fail', $response->getStatusCode(), $body, 'Connection API error');
        } else {
            if (!$department) {
                $this->changeStatus('fail', "Department not exit" . $department_name);
            } else {
                $this->changeStatus('success');
            }
        }
    }

    private function storeFileContentData($dataContent)
    {
        $fileName = md5(Str::uuid()->toString()) . '_' . Carbon::now()->format('Ymd') . '_' . $this->dataItem->id . '_' . 'data_connection_timesheet.txt';
        $envBasePath = Common::getEnvBasePath();
        $path = $envBasePath . PATH_UPLOAD_DATA_ITEM . '/' . Carbon::now()->format('Ymd');
        if (!Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->makeDirectory($path);
        }

        Storage::disk($this->disk)->put($path . '/' . $fileName, json_encode($dataContent));

        $fileData = File::create([
            'file_path' => $path . '/' . $fileName,
            'file_name' => $fileName,
            "file_extension" => pathinfo($fileName, PATHINFO_EXTENSION),
            "file_size" => Storage::disk($this->disk)->size($path . '/' . $fileName),
            "file_url" => Storage::disk($this->disk)->url($path . '/' . $fileName),
            "file_sys_disk" => $this->disk,
        ]);
        if ($fileData) {
            $this->dataItem->file_id = $fileData->id;
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
