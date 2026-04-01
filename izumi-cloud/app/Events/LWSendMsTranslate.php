<?php

namespace App\Events;

use App\Models\LineWorkConf;
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


class LWSendMsTranslate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $text;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        call_user_func_array([$this, 'sendMsgTranslate'], []);
        return [App::environment() . '_translate_lw_channel'];
    }
    private function sendMsgTranslate()
    {
        //send when no data
        Log::info('Sending LWMS translate ');
        $this->sendMsgToLW($this->text);
    }
    public function broadcastAs()
    {
        return 'lw_send_ms_translate_event';
    }

    private function sendMsgToLW($msg, $url = null)
    {
        $lw_conf = Config::get('line_works_conf');
        $systemLwAccessToken = LineWorkConf::query()->where('code', 'LW_ACCESS_TOKEN_TRANSLATE')->first();
        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
            if (!$this->checkTokenLw($systemLwAccessToken)) {
                $this->refreshTokenLw($systemLwAccessToken);
            }
        } else {
            $this->getAndSaveAccessTokenLw($lw_conf, $systemLwAccessToken);
        }
        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . data_get($systemLwAccessToken, 'value.access_token')
            ];
            $urlApi = Str::replaceArray('?',
                [
                    Arr::get($lw_conf, 'bot_translate_id'),
                    Arr::get($lw_conf, 'channel_translate_id')
                ], LW_API_SEND_MSG);

            $bodyLink = [
                "content" => [
                    "type" => "link",
                    "contentText" => $msg,
                    "linkText" => "Translate Screen",
                    "link" => Arr::get($lw_conf, 'app_url') . $url
                ]
            ];
            $bodyText = [
                "content" => [
                    "type" => "text",
                    "text" => $msg
                ]
            ];
            $body = $url ? $bodyLink : $bodyText;
            $dataJson = Http::timeout(60)->withHeaders($headers)->post($urlApi, $body)->json();
            Log::info('call api lw ==> ' . json_encode($dataJson));
        }
    }

    private function checkTokenLw($systemLwAccessToken)
    {
        $checkTokenExpire = false;
        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
            $headers = [
                'Authorization' => 'Bearer ' . data_get($systemLwAccessToken, 'value.access_token')
            ];
            $check = Http::timeout(60)->withHeaders($headers)->get(LW_API_GET_LIST_BOT)->json();
            if ($check && data_get($check, 'bots')) {
                $checkTokenExpire = true;
            } else {
                error_log('checkTokenLw function:' . json_encode($check));
                Log::info('checkTokenLw function:' . json_encode($check));
            }
        }
        return $checkTokenExpire;
    }

    private function refreshTokenLw(&$systemLwAccessToken)
    {
        $lw_conf = Config::get('line_works_conf');
        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
            $body = [
                'refresh_token' => data_get($systemLwAccessToken, 'value.refresh_token'),
                'grant_type' => 'refresh_token',
                'client_id' => Arr::get($lw_conf, 'client_id_translate'),
                'client_secret' => Arr::get($lw_conf, 'client_secret_translate'),
            ];

            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cookie' => 'LC=en_US; language=en_US'
            ];

            $dataJson = Http::timeout(60)->send('POST', LW_AUTH_TOKEN_URL, ['headers' => $headers, 'form_params' => $body])->json();
            if ($dataJson && data_get($dataJson, 'access_token')) {
                $data = $dataJson;
                $data['refresh_token'] = data_get($systemLwAccessToken, 'value.refresh_token');
                $systemLwAccessToken->update([
                    'code' => 'LW_ACCESS_TOKEN_TRANSLATE',
                    'value' => $data
                ]);
            } else {
                error_log('refreshTokenLw function:' . json_encode($dataJson));
                Log::info('refreshTokenLw function:' . json_encode($dataJson));
                $this->getAndSaveAccessTokenLw($lw_conf, $systemLwAccessToken);
            }
        }
    }

    private function getAndSaveAccessTokenLw($lw_conf, &$systemLwAccessToken)
    {
        // Tạo một Builder để xây dựng token
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
            'scope' => Arr::get($lw_conf, 'scope')
        ];
        $dataJson = Http::timeout(60)->send('POST', LW_AUTH_TOKEN_URL, ['headers' => $headers, 'form_params' => $options])->json();
        if ($dataJson && data_get($dataJson, 'access_token')) {
            if ($systemLwAccessToken) {
                $systemLwAccessToken->update([
                    'code' => 'LW_ACCESS_TOKEN_TRANSLATE',
                    'value' => $dataJson
                ]);
            } else {
                $systemLwAccessToken = LineWorkConf::query()->create([
                    'code' => 'LW_ACCESS_TOKEN_TRANSLATE',
                    'value' => $dataJson
                ]);
            }
        } else {
            error_log('getAndSaveAccessTokenLw function:' . json_encode($dataJson));
            Log::info('getAndSaveAccessTokenLw function:' . json_encode($dataJson));
        }
    }
}
