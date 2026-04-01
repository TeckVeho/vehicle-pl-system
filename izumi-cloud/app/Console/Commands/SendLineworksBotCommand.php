<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\LineworkBotMessage;
use App\Events\SendLWBotEvents;

class SendLineworksBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-lineworks-bot-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Start Sending Linework Bot Job');
        $month = (int) date('m');
        $day = (int) date('d');
        $lineworkBotMessagesLast = LineworkBotMessage::where('status', 1)->first();
        $lineworkBotMessages = LineworkBotMessage::where('month', $month)->where('day', $day)->first();
        if ($lineworkBotMessages) {
            if($lineworkBotMessagesLast) {
                $lineworkBotMessagesLast->status = 0;
                $lineworkBotMessagesLast->save();
            }
            $lineworkBotMessages->status = 1;
            $lineworkBotMessages->save();
            SendLWBotEvents::dispatch($lineworkBotMessages);
        }
        Log::info('End Sending Linework Bot Job');
    }
}
