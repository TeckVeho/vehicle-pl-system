<?php

namespace App\Jobs;

use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\Events\LWSendMsTranslate;

class TranslateModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $openAIService = app(OpenAIService::class);
        $data = $openAIService->translateFromJapanese($this->text,'gpt-5-mini');
        if($data['english'] != '' && $data['chinese'] != '') {
            foreach($data as $key => $value) {
                if($value != '') {
                    LWSendMsTranslate::dispatch($value);
                }
            }
        }
    }

}

