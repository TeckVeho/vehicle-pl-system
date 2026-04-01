<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-send-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi 15 mail test 【測定NG通知】 với nội dung mẫu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $to = "maivantue29@gmail.com";
        if (empty($to)) {
            $this->error('Cần chỉ định --to=email@example.com hoặc cấu hình MAIL_TEST_TO trong .env');
            return self::FAILURE;
        }

        $emails = array_map('trim', explode(',', $to));
        $subject = '【測定NG通知】古河チーム：長井純子_3347';
        $body = implode("\n", [
            'ドライバー： 長井純子_3347 （所属：古河チーム）',
            '測定時間： 2026-02-05 22:35:44',
            '点呼方法： IT点呼',
            '点呼場所： 古河チーム',
            '乗務前後： 乗務前',
            '測定結果： 0.10mg/l',
            '点呼執行者： 今泉浩二_3312',
            '点呼執行場所： 所沢チーム',
        ]);

        $totalToSend = 15;
        $sent = 0;

        $this->info("Đang gửi {$totalToSend} mail tới: " . implode(', ', $emails));

        for ($i = 0; $i < $totalToSend; $i++) {
            foreach ($emails as $email) {
                Mail::raw($body, function ($message) use ($subject, $email) {
                    $message->subject($subject)->to($email);
                });
                $sent++;
            }
        }

        $this->info("Đã gửi {$sent} mail ({$totalToSend} lần × " . count($emails) . ' địa chỉ).');
        return self::SUCCESS;
    }
}
