<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendFailConnectEmail;
use App\Models\MailLog;
use Throwable;
class MailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $mailAddress;
    private $fromSystem;
    private $toSystem;
    private $datetime;
    private $dataName;
    private $content;
    private $mailLog;
    public function __construct($mail, $fromSystem, $toSystem, $datetime, $content, $dataName, $mailLog)
    {
        $this->mailAddress = $mail;
        $this->fromSystem = $fromSystem;
        $this->toSystem = $toSystem;
        $this->datetime = $datetime;
        $this->content = $content;
        $this->dataName = $dataName;
        $this->mailLog = MailLog::find($mailLog);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->mailAddress) {
            Mail::to($this->mailAddress)->send(new SendFailConnectEmail(
                $this->fromSystem,
                $this->toSystem,
                $this->datetime,
                $this->content,
                $this->dataName
            ));
            $this->mailLog->data_name = $this->dataName;
            $this->mailLog->from_name = $this->fromSystem;
            $this->mailLog->to_name = $this->toSystem;
            $this->mailLog->supervior_email = $this->mailAddress;
            $this->mailLog->seding_status = "sent";
            $this->mailLog->save();
            sleep(15);
        }
    }

    public function failed(Throwable $exception)
    {
        $this->mailLog->data_name = $this->dataName;
        $this->mailLog->from_name = $this->fromSystem;
        $this->mailLog->to_name = $this->toSystem;
        $this->mailLog->seding_status = "fail";
        $this->mailLog->exception = $exception->getMessage();
        $this->mailLog->save();
    }
}
