<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SendEmailLinkingShakensho extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    private $vehicleNoPlate;
    private $datetime;
    private $urlPdf;
    private $filePdfName;
    private $departmentName;
    private $action;

    public function __construct($vehicleNoPlate, $datetime, $urlPdf, $filePdfName, $departmentName, $action)
    {
        $this->vehicleNoPlate = $vehicleNoPlate;
        $this->datetime = $datetime;
        $this->urlPdf = $urlPdf;
        $this->filePdfName = $filePdfName;
        $this->departmentName = $departmentName;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->action == 'create') {
            $subject = "【新規】【車検証連携完了のお知らせ】$this->vehicleNoPlate; Env:" . App::environment();
            $actionText = '新規登録';
        } else {
            $subject = "【車検証連携完了のお知らせ】$this->vehicleNoPlate; Env:" . App::environment();
            $actionText = '更新';
        }
        return $this->view('email.email-linking-shakensho', [
            "vehicleNoPlate" => $this->vehicleNoPlate,
            "dateTime" => $this->datetime,
            "urlPdf" => $this->urlPdf,
            "filePdfName" => $this->filePdfName,
            "department_name" => $this->departmentName,
            "actionText" => $actionText
        ])->subject($subject);
        // return $this->subject("FAILED SYNC DATA FROM CLOUD TO TIMESHEET!")->view('email-fail.blade');
    }
}
