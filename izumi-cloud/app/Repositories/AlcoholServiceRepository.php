<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace Repository;

use App\Events\MessageSentEvent;
use App\Jobs\DataConnectionJob;
use App\Models\DataConnection;
use App\Models\DataItem;
use App\Models\File;
use App\Repositories\Contracts\AlcoholServiceRepositoryInterface;
use Helper\Common;
use Illuminate\Support\Carbon;
use Helper\Pop3Retrieve;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Revolution\Salvager\Facades\Salvager;
use Symfony\Component\DomCrawler\Crawler;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\ClientManager;
use ZBateson\MailMimeParser\MailMimeParser;
use App\Models\ACL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Jobs\AclConfirmJob;

class AlcoholServiceRepository extends BaseRepository implements AlcoholServiceRepositoryInterface
{

    protected $doneMailBody = "";
    protected $access_count = 0;
    protected $dataConnection;
    protected $dataItem;
    protected $dataContent;
    protected $dataContentALC;
    protected $type = 'active';
    protected $mailCount;
    protected $mailCountStart;
    protected $dateKeeper;
    protected $dateKeeperStart;
    protected $updateItDate = true;
    protected $date;
    protected $serverMail = 'xserver';
    protected $file_id_save;

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        return DataConnection::class;
    }

    public function AlcGetByScrapingAndMailer($dataConnection, $dataItem)
    {
        Log::info("Crawling started at: " . Carbon::now()->toDateTimeString());
        $this->dataContentALC['mail'] = [];
        $this->dataContentALC['keeper'] = [];
        $this->dataContentALC['mail_count'] = '';
        $this->dataContentALC['date_keeper'] = '';
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');
        $this->mails();
//        $this->its();
        $this->changeStatus('success');

        if ($this->mailCount) {
            $this->dataContentALC['mail_count'] = $this->mailCountStart . '=>' . $this->mailCount;
            file_put_contents(storage_path("mail-count.txt"), $this->mailCount);
        }
        if ($this->dateKeeper) {
            $this->dataContentALC['date_keeper'] = $this->dateKeeperStart . '=>' . $this->dateKeeper;
            if ($this->updateItDate)
                file_put_contents(storage_path("it-date.txt"), $this->dateKeeper);
        }
        $this->storeFileContentData('alc_xserver_mail.txt');
        Log::info("Crawling End at: " . Carbon::now()->toDateTimeString());

        $dataConnectionICL1011 = DataConnection::query()->where('data_code', 'ICL_1011')->first();
        if ($dataConnectionICL1011) {
            DataConnectionJob::dispatch($dataConnectionICL1011->id, Carbon::now());
        }
    }

    public function AlcCloudToTimeSheet($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');

        $urlCallApi = URL_API_SEND_TO_IZUMI;
        if (App::environment('staging')) {
            $urlCallApi = URL_API_SEND_TO_IZUMI_STAGING;
        }
        if (App::environment('production')) {
            $urlCallApi = URL_API_SEND_TO_IZUMI_PRODUCTION;
        }

        $dataAlc = DataConnection::where('data_code', 'ICL_1010')->first();

        if (!$dataAlc) {
            $this->changeStatus('fail', "Data connection 'data_code' not exists");
            return;
        }

        if (Carbon::now()->format('Ymd') !== $this->dataItem->created_at->format('Ymd')) {
            $this->changeStatus('fail', "Job exceeded the scheduled time");
            return;
        }

        $dataItemContent = DataItem::query()
            ->where('status', 'success')
            ->where('data_connection_id', $dataAlc->id)
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
                    'mail' => json_encode(Arr::get($dataContent, 'mail')),
                    'mail_count' => json_encode(Arr::get($dataContent, 'mail_count')),
                    'keeper' => json_encode(Arr::get($dataContent, 'keeper')),
                    'date_keeper' => json_encode(Arr::get($dataContent, 'date_keeper')),
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

    public function AlcGetByGmail($dataConnection, $dataItem)
    {
        Log::info("AlcGetByGmail started at: " . Carbon::now()->toDateTimeString());
        $this->dataContentALC['mail'] = [];
        $this->dataContentALC['keeper'] = [];
        $this->dataContentALC['mail_count'] = '';
        $this->dataContentALC['date_keeper'] = '';

        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');
        $this->gMails();
        $this->changeStatus('success');
        if ($this->mailCount) {
            $this->dataContentALC['mail_count'] = $this->mailCountStart . '=>' . $this->mailCount;
            file_put_contents(storage_path("gmail-count.txt"), $this->mailCount);
        }
        if ($this->dateKeeper) {
            $this->dataContentALC['date_keeper'] = $this->dateKeeperStart . '=>' . $this->dateKeeper;
        }
        $this->storeFileContentData('alc_gmail_mail.txt');
        Log::info("AlcGetByGmail End at: " . Carbon::now()->toDateTimeString());

        $dataConnectionICL1011 = DataConnection::query()->where('data_code', 'ICL_1011')->first();
        if ($dataConnectionICL1011) {
            DataConnectionJob::dispatch($dataConnectionICL1011->id, Carbon::now());
        }
    }

    public function AlcCloudToTimeSheetV2($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');
        $this->sendContentBodyV2();
    }

    private function its($date = null)
    {
        Log::info("IT started at: " . Carbon::now()->toDateTimeString());
        if (!$date) {
            $date = @trim(file_get_contents(storage_path("it-date.txt")));
            $this->dateKeeperStart = $date;
            if (!$date || time() - strtotime($date) > 3600 * 24 * 5)
                $date = date("Y-m-d", strtotime('-3 days'));

            while ($date <= date("Y-m-d")) {
                print $date . "\n";
                $this->it($date);
                $this->dateKeeper = $date;
                $date = date("Y-m-d", strtotime($date . ' +1 days'));
            }
        } else {
            $this->it($date);
            $this->updateItDate = false;
            $this->dateKeeper = $date;
        }
        Log::info("IT End at: " . Carbon::now()->toDateTimeString());
    }

    private function it($date)
    {
        $date = date("Y年m月d日", strtotime(preg_replace("/[^0-9]$/", "", preg_replace("/[^0-9]+/", "/", $date))));
        $date2 = date("Y/m/d", strtotime(preg_replace("/[^0-9]$/", "", preg_replace("/[^0-9]+/", "/", $date))));
        $crews = [];
        $this->doneMailBody .= "it date:" . $date . "\n";
        Salvager::browse(
            function (Browser $b) use ($date, $date2, &$crews) {
                try {
                    $b = $this->itLogin($b);
                    $crews = $this->itGetCrews($b, $date);
                    $b->quit();
                    Log::info('itKeeper============> call function quit() browser');
                } catch (\Exception $exception) {
                    $b->quit();
                    Log::info('itKeeper============> call function quit() browser');
                    throw $exception;
                }
            }
        );
        $this->saveCrews($crews);
    }

    private function itLogin(Browser $b)
    {
        print "it login\n";
        $b->visit("https://ittkv2.ittenko-keeper.com/se/manage/index");
        $this->access_count++;
        $b->waitForLocation("/se/manage/index", 3);
        sleep(2);
        $b->keys('input[name=loginId]', 'm-izumitrans-admin');
        $b->keys('input[name=loginPsw]', 'h4RUYkDH');
        $b->click('input[type=submit]');
        $this->access_count++;
        $b->waitForText("IT点呼キーパー", 3);
        return $b;
    }

    private function itGetCrews(Browser $b, $date)
    {
        //メニューの点呼結果一覧をクリック
        $b->driver->executeScript("menuClick('../tenkolist/index')");
        $this->access_count++;
        $b->waitForText("点呼区分");
        $b->driver->executeScript("void((function(){document.getElementById('tenkoDateFm').value='{$date}';})());");
        $b->driver->executeScript("void((function(){document.getElementsByName('days')[0].value='2';})());");
        $b->driver->executeScript("clickSearch()");
        $this->access_count++;
        $b->waitForText("表示");
        $crews = [];
        $crews = $this->itGetCrewsPage($b, $crews);
        $done = [];
        for ($i = 0; $i < 20; $i++) {
            $b->crawler()->filter('.pageLinkOther a')->each(function (Crawler $node) use ($b, &$crews, &$done) {
                if (in_array($node->attr('onclick'), $done)) {
                    return;
                }
                $done[] = $node->attr('onclick');
                if ($this->access_count > 200)
                    throw new \Exception("Too much browser access:" . $this->access_count, 1);
                $b->driver->executeScript($node->attr('onclick'));
                $this->access_count++;
                sleep(2);
                $b->waitForText("表示");
                $crews = $this->itGetCrewsPage($b, $crews);
            });
        }
        $this->doneMailBody .= "it access count:" . $this->access_count . "\n";
        $this->doneMailBody .= "it crew count:" . count($crews) . "\n";
        return $crews;
    }

    private function itGetCrewsPage(Browser $b, $crews)
    {
        $b->crawler()->filter('.table-condensed tr')->each(function (Crawler $tr) use (&$crews) {
            $crew = [];
            $tr->filter('td')->each(function (Crawler $td) use (&$crew) {
                $crew[] = $td->text();
            });
            if (count($crew) < 7) {
                return;
            }
            $crews[] = $crew;
        });
        return $crews;
    }

    private function saveCrews($crews)
    {
        foreach ($crews as $crew) {
            $name = trim($crew[2]);
            $date = $crew[0] . " " . $crew[4] . ":00";
            $type = (trim($crew[5]) == "乗務前") ? 0 : 1;
            if ($crew[7] != "○") {
                continue;
            }
            $this->dataContentALC['keeper'][] = [
                "employee_name" => $name,
                "type" => $type,
                "date" => $date
            ];
        }
    }


    private function mails()
    {
        Log::info("Xserver mail started at: " . Carbon::now()->toDateTimeString());
        $cm = new ClientManager($options = []);
        $cm->account('default');
        $client = $cm->make([
            'host' => 'sv6148.xserver.jp',
            'port' => 110,
            'encryption' => 'tcp',
            'validate_cert' => false,
            'username' => 'izumi@veho-works.com',
            'password' => '#2020aaaa',
            'protocol' => 'pop3',
        ]);
        $client->connect();
        $folders = $client->getFolder('INBOX');
        $count = Arr::get($folders->getStatus(), 'exists');

        $start = @(int)file_get_contents(storage_path("mail-count.txt"));
        Log::info("Xserver mail Count Number start at: " . $start);
        Log::info("Xserver mail Count Number end at: " . $count);
        $this->mailCountStart = $start;
        //$count = $start+10;
        $j = 0;
        for ($i = $start; $i <= $count; $i++) {
            print "Xserver mail:{$i}/{$count}\n";
            $mail = $folders->messages()->getMessageByMsgn($i);
            $sent_at = $mail->getDate()->toString();
            $subject = $mail->getSubject()->toString();
            if (Str::contains($subject, '?B?')) {
                $subject = mb_decode_mimeheader($subject);
            }
            if (!preg_match('/\［アルコール測定結果\］/', $subject)) {
                Log::info("Xserver mail $i skipped, subject not match: " . $subject);
                Log::info("Content not match: " . $mail->getTextBody());
                if (!$subject || empty($subject)) {
                    $client->disconnect();
                    throw new \Exception("Xserver mail $i skipped, subject not match: " . $subject);
                }
                continue;
            }
            $dataMail = $this->mail($mail->getTextBody(), $i);
            if (is_array($dataMail)) {
                $this->dataContentALC['mail'][] = $dataMail;
            } else {
                Log::info("Error mail $i  sent at: $sent_at | content | " . $mail->getTextBody());
            }
            $this->mailCount = $i;
            $j++;
        }
        $client->disconnect();
        $this->doneMailBody .= "mail-count:{$j}\n";
        Log::info("Xserver mail End at: " . Carbon::now()->toDateTimeString());
    }

    private function gMails()
    {
        Log::info("Gmail mail started at: " . Carbon::now()->toDateTimeString());
        $client = Client::account('default');
        $client->connect();
        $folders = $client->getFolder('INBOX');
        $count = Arr::get($folders->getStatus(), 'exists');
        $start = @(int)file_get_contents(storage_path("gmail-count.txt"));
        Log::info("Gmail mail Count Number start at: " . $start);
        Log::info("Gmail mail Count Number end at: " . $count);
        $this->mailCountStart = $start;
        //$count = $start+10;
        $j = 0;
        for ($i = $start; $i <= $count; $i++) {
            print "Gmail mail:{$i}/{$count}\n";
            $mail = $folders->messages()->getMessageByMsgn($i);
            $sent_at = $mail->getDate()->toString();
            $subject = $mail->getSubject()->toString();
            if (Str::contains($subject, '?B?')) {
                $subject = mb_decode_mimeheader($subject);
            }
            if (!preg_match('/\［アルコール測定結果\］/', $subject)) {
                Log::info("Gmails mail $i skipped, subject not match: " . $subject);
                Log::info("Content not match: " . $mail->getTextBody());
                if (!$subject || empty($subject)) {
                    $client->disconnect();
                    throw new \Exception("Gmails mail $i skipped, subject not match: " . $subject);
                }
                continue;
            }
            $dataMail = $this->mail($mail->getTextBody(), $i);
            if (is_array($dataMail)) {
                $this->dataContentALC['mail'][] = $dataMail;
            } else {
                Log::info("Error gmail mail $i  sent at: $sent_at | content | " . $mail->getTextBody());
            }
            $this->mailCount = $i;
            $j++;
        }
        $client->disconnect();
        $this->doneMailBody .= "gmail-count:{$j}\n";
        Log::info("Gmails mail End at: " . Carbon::now()->toDateTimeString());
    }

    private function mail($body, $i)
    {
        if (preg_match("/ID ：([0-9]*)/", $body, $match)) {
            $employee_code = trim($match[1]);
        }
        if (preg_match("/乗務員名 ：(.*)/", $body, $match)) {
            $name = trim($match[1]);
        }
        if (preg_match("/日付 ：(.*)/", $body, $match)) {
            $day = trim($match[1]);
        }
        if (preg_match("/時間 ：(.*)/", $body, $match)) {
            $time = trim($match[1]);
        }
        if (preg_match("/測定場所 ：(.*)/", $body, $match)) {
            $department = trim($match[1]);
        }
        if (preg_match("/勤務形態 ：(.*)/", $body, $match)) {
            $type_raw = trim($match[1]);
        } else {
            $type_raw = "★";
        }
        //print $employee_code."<>".$name."\n";
        switch ($type_raw) {
            case "始業":
            case "乗務前":
            case "出勤":
                $type = 0;
                break;
            case "終業":
            case "乗務後":
            case "退勤":
                $type = 1;
                break;
            default:
                return $this->alert("勤務形態エラー\n\n" . $body, $i);
        }
        if (!isset($employee_code) || !isset($name) || !isset($day) || !isset($time) || !isset($type)) {
            $this->doneMailBody .= "メールフォーマットエラー:\n" . $body . "\n\n";
            return $this->alert("メールフォーマットエラー\n\n" . $body, $i);
        }
        Log::info("Mail $i passed: | $employee_code | $type | " . $day . " " . $time);
        return [
            "employee_code" => $employee_code,
            "type" => $type,
            "date" => $day . " " . $time,
            "department" => $department,
            "employee_name" => $name
        ];

    }

    private function getPop3()
    {
        $host = 'tcp://sv6148.xserver.jp';
        $user = 'izumi@veho-works.com';
        $pass = '#2020aaaa';
        $port = 110;
        $pop3 = new Pop3Retrieve();
        $pop3->open($host, $user, $pass, $port);
        return $pop3;
    }

    public function alert($body, $i)
    {
        print $body;
        Log::info("Mail $i FAILD: | $body");
        return;
        Mail::raw($body, function ($message) {
            $message->subject('[アラート]イズミ物流アルコールチェッカー')->to('kido@veho-works.com');
        });
    }

    private function sendContentBody()
    {
        $this->dataConnection = DataConnection::query()->where('data_code', 'ICL_1011')->first();
        $this->dataItem = $this->dataConnection->dataItem()->create(["data_connection_id" => $this->dataConnection->id]);
        $this->type = $this->dataConnection->type;
        $this->changeStatus('excluding');

        $urlCallApi = URL_API_SEND_TO_IZUMI;
        if (App::environment('staging')) {
            $urlCallApi = URL_API_SEND_TO_IZUMI_STAGING;
        }
        if (App::environment('production')) {
            $urlCallApi = URL_API_SEND_TO_IZUMI_PRODUCTION;
        }
        $dataAlc = DataConnection::where('data_code', 'ICL_1010')->first();
        $dataAlcV2 = DataConnection::where('data_code', 'ICL_1034')->first();

        if (!$dataAlc && !$dataAlcV2) {
            $this->changeStatus('fail', "Data connection 'data_code' not exists");
            return;
        }

        $dataItemContent = DataItem::query()
            ->where('status', 'success')
            ->where('data_connection_id', $dataAlc->id)
            ->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()])
            ->orderBy('id', 'desc')->first();

        $dataItemContentV2 = DataItem::query()
            ->where('status', 'success')
            ->where('data_connection_id', $dataAlcV2->id)
            ->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()])
            ->orderBy('id', 'desc')->first();

        if (!$dataItemContent && !$dataItemContentV2) {
            $this->changeStatus('fail', "Data item not exists");
            return;
        }

        if (($dataItemContent && $dataItemContent->status !== 'success')
            && ($dataItemContentV2 && $dataItemContentV2->status !== 'success')) {
            $this->changeStatus('fail', "Connection time final status data mismatch");
            return;
        }

        $dataContent = [];
        $dataContentV2 = [];
        if ($dataItemContent && $dataItemContent->file) {
            $dataContent = json_decode(Storage::disk($dataItemContent->file->file_sys_disk)->get($dataItemContent->file->file_path), true);
        } elseif ($dataItemContent) {
            $dataContent = $dataItemContent->content;
        }
        if ($dataItemContentV2 && $dataItemContentV2->file) {
            $dataContentV2 = json_decode(Storage::disk($dataItemContentV2->file->file_sys_disk)->get($dataItemContentV2->file->file_path), true);
        } elseif ($dataItemContentV2) {
            $dataContentV2 = $dataItemContent->content;
        }

        $response = Http::timeout(3600)->withoutVerifying()
            ->post($urlCallApi,
                [
                    'mail' => json_encode(Arr::get($dataContent, 'mail', [])),
                    'mail_count' => json_encode(Arr::get($dataContent, 'mail_count')),
                    'keeper' => json_encode(Arr::get($dataContentV2, 'keeper', [])),
                    'date_keeper' => json_encode(Arr::get($dataContentV2, 'date_keeper')),
                ]
            );
        $this->dataItem->file_id = $this->file_id_save;

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

    private function sendContentBodyV2()
    {
        $urlCallApi = URL_API_SEND_TO_IZUMI_V2;
        if (App::environment('staging')) {
            $urlCallApi = URL_API_SEND_TO_IZUMI_STAGING_V2;
        }
        if (App::environment('production')) {
            $urlCallApi = URL_API_SEND_TO_IZUMI_V2_PRODUCTION;
        }

        $dataAlc = DataConnection::where('data_code', 'ICL_1010')->first();
        $dataAlcV2 = DataConnection::where('data_code', 'ICL_1034')->first();

        if (!$dataAlc && !$dataAlcV2) {
            $this->changeStatus('fail', "Data connection 'data_code' not exists");
            return;
        }

        $dataItemContent = DataItem::query()
            ->where('status', 'success')
            ->where('data_connection_id', $dataAlc->id)
            ->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()])
            ->orderBy('id', 'desc')->first();

        $dataItemContentV2 = DataItem::query()
            ->where('status', 'success')
            ->where('data_connection_id', $dataAlcV2->id)
            ->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()])
            ->orderBy('id', 'desc')->first();

        if (!$dataItemContent && !$dataItemContentV2) {
            $this->changeStatus('fail', "Data item not exists");
            return;
        }

        if (($dataItemContent && $dataItemContent->status !== 'success')
            && ($dataItemContentV2 && $dataItemContentV2->status !== 'success')) {
            $this->changeStatus('fail', "Connection time final status data mismatch");
            return;
        }

        $dataContent = [];
        $dataContentV2 = [];
        if ($dataItemContent && $dataItemContent->file) {
            $dataContent = json_decode(Storage::disk($dataItemContent->file->file_sys_disk)->get($dataItemContent->file->file_path), true);
        } elseif ($dataItemContent) {
            $dataContent = $dataItemContent->content;
        }
        if ($dataItemContentV2 && $dataItemContentV2->file) {
            $dataContentV2 = json_decode(Storage::disk($dataItemContentV2->file->file_sys_disk)->get($dataItemContentV2->file->file_path), true);
        } elseif ($dataItemContentV2) {
            $dataContentV2 = $dataItemContent->content;
        }

        $response = Http::timeout(3600)->withoutVerifying()
            ->post($urlCallApi,
                [
                    'mail' => json_encode(Arr::get($dataContent, 'mail', [])),
                    'mail_count' => json_encode(Arr::get($dataContent, 'mail_count')),
                    'keeper' => json_encode(Arr::get($dataContentV2, 'keeper', [])),
                    'date_keeper' => json_encode(Arr::get($dataContentV2, 'date_keeper')),
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
            $this->dataItem->content = $this->dataContent;
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

        Storage::disk($disk)->put($path . '/' . $fileName, json_encode($this->dataContentALC));

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
            $this->dataItem->save();
            $this->file_id_save = $fileData->id;
        }
    }
}
