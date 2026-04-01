<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SendFailConnectEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    private $fromSystem;
    private $toSystem;
    private $datetime;
    private $content;
    private $dataName;
    public function __construct($fromSystem, $toSystem, $datetime, $content, $dataName)
    {
        $this->fromSystem = $fromSystem;
        $this->toSystem = $toSystem;
        $this->datetime = $datetime;
        $this->content = $content;
        $this->dataName = $dataName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "連携失敗箇所： " . $this->fromSystem . "から " . $this->toSystem . " への連携; Environment:". App::environment();
        return $this->from('phuong.codeunited@gmail.com', 'イズミクラウド連携失敗通知')
        ->view('email.email-fail',[
            "from" => $this->fromSystem,
            "to" => $this->toSystem,
            "date" => $this->datetime,
            "dataName" => $this->dataName,
            "content" => $this->content
        ])->subject($subject);
        // return $this->subject("FAILED SYNC DATA FROM CLOUD TO TIMESHEET!")->view('email-fail.blade');
    }
}
