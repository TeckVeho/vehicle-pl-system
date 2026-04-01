<?php

namespace App\Jobs;

use App\Mail\SendEmailLinkingShakensho;
use App\Models\ShakenshoEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendFailConnectEmail;
use App\Models\MailLog;
use Throwable;

class SendMailLingKingShakenshoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $vehicle;

    public function __construct($vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $listEmail = ShakenshoEmail::query()->get();
        foreach ($listEmail as $email) {
            Mail::to($email->email)->send(new SendEmailLinkingShakensho(
                $this->vehicle['vehicleNoPlate'],
                $this->vehicle['dateTime'],
                $this->vehicle['urlPdf'],
                $this->vehicle['filePdfName'],
                $this->vehicle['department_name'],
                $this->vehicle['action']
            ));
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
    }
}
