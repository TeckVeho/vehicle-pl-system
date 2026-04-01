<?php

namespace Repository;

use App\Repositories\Contracts\AlcoholConfirmRepositoryInterface;
use App\Models\DataItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZBateson\MailMimeParser\MailMimeParser;
use Illuminate\Support\Carbon;
use Helper\Pop3Retrieve;
use Illuminate\Support\Facades\Mail;

class AlcoholConfirmRepository extends BaseRepository implements AlcoholConfirmRepositoryInterface
{
    protected $mailBodyAleart;
    protected $mails = [
        'phuong.codeunited@gmail.com',
        'i.kohei2323@gmail.com'
    ];
    protected $startMail;
    protected $endMail;
    protected $success;
    protected $dataMail;
    protected $dataContent;

    public function __construct()
    {

    }

    public function model()
    {
        return DataItem::class;
    }

    public function mails()
    {
        Log::info("AlcoholConfirm ====> started at: " . Carbon::now()->toDateTimeString());
        $dataItem = DataItem::query()
            ->with('file')
            ->where('id', DataItem::where('data_connection_id', 11)->max('id'))
            ->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
            ->first();
        if ($dataItem && $dataItem->status == "success") {
            $this->dataContent = json_decode(Storage::disk($dataItem->file->file_sys_disk)->get($dataItem->file->file_path), true);
            $dataMailOld = data_get($this->dataContent, 'mail');

            $mailCount = explode('=>', $dataItem->content['mail_count']);
            $mailParser = new MailMimeParser();
            $pop3 = $this->getPop3();
            $start = $mailCount[0];
            $this->startMail = $start;
            for ($i = $start; $i <= $mailCount[1]; $i++) {
                $this->endMail = $i;
                $mail = $mailParser->parse($pop3->retrieve($i));
                if (!preg_match('/\［アルコール測定結果\］/', $mail->getHeaderValue('Subject'))) {
                    Log::info(" AlcoholConfirm ====> Mail $i skipped, subject not match: " . $mail->getHeaderValue('Subject'));
                    Log::info(" AlcoholConfirm ====> Content not match: " . $mail->getTextContent());
                    continue;
                }
                $result = $this->mail($mail->getTextContent(), $i);
                // $this->checkSpecifyEmployee($result);
            }
            $pop3->close();

            $data = collect($dataMailOld)->mapWithKeys(function ($item) {
                $keyd1 = $item['employee_code'] . Carbon::parse($item['date'])->timestamp;
                return [$keyd1 => $item];
            })->toArray();
            $data2 = collect($this->dataMail)->mapWithKeys(function ($item) {
                $keyd2 = $item['employee_code'] . Carbon::parse($item['date'])->timestamp;
                return [$keyd2 => $item];
            })->toArray();
            $dataMissed = array_diff_key($data2, $data);
            if ($dataMissed && count($dataMissed) > 0) {
                $this->mailBodyAleart .= "ALC missed: \n";
                foreach ($dataMissed as $key => $value) {
                    $this->mailBodyAleart .= "===========================================================\n";
                    $this->mailBodyAleart .= "employee_code: " . Arr::get($value, 'employee_code') . "\n";
                    $this->mailBodyAleart .= "type:  " . Arr::get($value, 'type') . "\n";
                    $this->mailBodyAleart .= "date:  " . Arr::get($value, 'date') . "\n";
                    $this->mailBodyAleart .= "department:  " . Arr::get($value, 'department') . "\n";
                    $this->mailBodyAleart .= "employee_name:  " . Arr::get($value, 'employee_name') . "\n";
                    Log::info(" =======>ALC Confirm check missed ====>: " . Arr::get($value, 'employee_code') . " | " . Arr::get($value, 'type') . " | " . Arr::get($value, 'date'));
                }
                $this->alert($this->mailBodyAleart);
                $this->dataContent['mail'] = array_merge($this->dataMail, $dataMissed);
                Storage::disk($dataItem->file->file_sys_disk)->put($dataItem->file->file_path, json_encode($this->dataContent));
            }
        } else {
            if ($dataItem) {
                $this->alert(json_encode($dataItem->msg_error), true);
            } else {
                $this->alert(" AlcoholConfirm ====> Connection fail: " . Carbon::now(), true);
            }
        }
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
                return false;
        }

        if (!isset($employee_code) || !isset($name) || !isset($day) || !isset($time) || !isset($type)) {
            return false;
        }
        Log::info("AlcoholConfirm ====> Mail $i passed: | $employee_code | $type | " . $day . " " . $time);
        $data = [
            "employee_code" => $employee_code,
            "type" => $type,
            "date" => $day . " " . $time,
            "department" => $department,
            "employee_name" => $name
        ];
        $this->dataMail[] = $data;
        return $data;

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

    public function alert($body, $isErrorConnect = false)
    {
        $env = app()->environment();
        $s = $this->startMail;
        $e = $this->endMail;

        $subject = "Report: count from $s -> $e, env: " . $env;
        if ($isErrorConnect) {
            $subject = "Connection fail, env: " . $env;
        }
        if ($body && !empty($body)) {
            foreach ($this->mails as $key => $mail) {
                Mail::raw($body, function ($message) use ($mail, $subject) {
                    $message->subject($subject)->to($mail);
                });
            }
        }
    }
}
