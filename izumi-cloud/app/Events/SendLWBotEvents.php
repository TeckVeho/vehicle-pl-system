<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;

class SendLWBotEvents implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $lineworkBotMessage;

    public function __construct($lineworkBotMessage)
    {
        $this->lineworkBotMessage = $lineworkBotMessage;
    }

    public function broadcastOn()
    {
        call_user_func_array([$this, 'sendMsgLineworkBot'], []);
        return [App::environment() . '_linework_bot_channel'];
    }

    public function broadcastAs()
    {
        return 'linework_bot_event';
    }

    private function sendMsgLineworkBot()
    {
        //send when no data
        Log::info('Sending LWMS Linework Bot ');
        $this->sendMsgToLWBoard($this->lineworkBotMessage);
    }

    private function sendMsgToLWBoard($lineworkBotMessage)
    {
        try {
            $lw_conf = Config::get('line_works_conf');

            $access_token = $this->getAndSaveAccessTokenLw();


            if (!$access_token) {
                Log::error("Không lấy được Access Token LINE WORKS");
                return false;
            }
            //$boardId = Arr::get($lw_conf, 'board_id'); // Bạn dùng board có sẵn

            $headers = [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ];
            if ($lineworkBotMessage && $lineworkBotMessage->message) {
                    $parts = array_filter([
                        $lineworkBotMessage->message,
                        $lineworkBotMessage->message_en ?? null,
                        $lineworkBotMessage->message_zh ?? null,
                    ], fn ($v) => $v !== null && $v !== '');
                    $lineBreak = match (Arr::get($lw_conf, 'board_post_line_break', 'br')) {
                        'crlf' => "\r\n",
                        'lf' => "\n",
                        'double_lf' => "\n\n",
                        'br' => '<br>',
                        'br_double' => '<br><br>',
                        default => '<br>',
                    };
                    $messBody = implode($lineBreak, $parts);

                    $body = [
                        "title"  => "【平川社長 今日の一言】" . $lineworkBotMessage->message,
                        "body"   => $messBody,
                        "temporary" => false,
                        "sendNotifications" => true,
                        "enableComment"=> false
                    ];

                    $boardIds = [
                        Arr::get($lw_conf, 'board_id_1'),
                        Arr::get($lw_conf, 'board_id_2'),
                        Arr::get($lw_conf, 'board_id_3'),
                    ];

                    $response = null;
                    foreach ($boardIds as $id) {
                        if (empty($id)) {
                            continue;
                        }
                        $urlPostArticle = "https://www.worksapis.com/v1.0/boards/{$id}/posts";
                        $response = Http::withHeaders($headers)->post($urlPostArticle, $body);
                        Log::info("Post Article to Board ID {$id} Response ==> " . $response->body());
                    }

                    return $response ? $response->json() : 'success';
            } else {
                Log::error("Không có dữ liệu để gửi");
                return false;
            }
            
        } catch (\Throwable $th) {
            Log::info("Error sending message to LW Board: " . $th->getMessage());
        }
        
    }

    private function getAndSaveAccessTokenLw()
    {
        // Tạo một Builder để xây dựng token
        $lw_conf = Config::get('line_works_conf');
        $now = new DateTimeImmutable();
        $pathKey = base_path(Arr::get($lw_conf, 'private_key_path_translate'));
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::file($pathKey));
        // Thêm thông tin vào token
        $token = $config->builder()
            ->issuedBy(Arr::get($lw_conf, 'client_id_translate'))
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->relatedTo(Arr::get($lw_conf, 'service_account_translate'))
            ->getToken($config->signer(), $config->signingKey());

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Cookie' => 'LC=en_US; WORKS_RE_LOC=jp1; WORKS_TE_LOC=jp1; language=en_US'
        ];
        $options = [
            'assertion' => $token->toString(),
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'client_id' => Arr::get($lw_conf, 'client_id_translate'),
            'client_secret' => Arr::get($lw_conf, 'client_secret_translate'),
            'scope' => Arr::get($lw_conf, 'scope_translate')
        ];
        $dataJson = Http::send('POST', LW_AUTH_TOKEN_URL, ['headers' => $headers, 'form_params' => $options])->json();
        if ($dataJson && data_get($dataJson, 'access_token')) {
            return data_get($dataJson, 'access_token');
        } else {
            Log::info('getAndSaveAccessTokenLw function:' . json_encode($dataJson));
            return null;
        }
    }
}
