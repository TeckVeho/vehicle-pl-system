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

class HotlinesSendLWEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $hotlines;
    protected $channel;
    protected $categoryName;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($hotlines, $channel, $categoryName)
    {
        $this->hotlines = $hotlines;
        $this->channel= $channel;
        $this->categoryName = $categoryName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */

    public function broadcastOn()
    {
        call_user_func_array([$this, 'sendMsgHotlines'], []);
        return [App::environment() . '_hotlines_lw_channel'];
    }
    public function broadcastAs()
    {
        return 'hotlines_lw_event';
    }

    private function sendMsgHotlines()
    {
        //send when no data
        Log::info('Sending LWMS Hotlines ');
        $msg = $this->handleMessage();
        $this->sendMsgToLW($msg);
    }

    private function handleMessage()
    {
        $mes ="【相談受付のお知らせ】 \n";
        $mes .= $this->categoryName ."に関するCrewメンバーから新しい相談が届きました。 \n";
        $mes .= "氏名：". $this->hotlines->username . "\n";
        $mes .= "電話番号：". $this->hotlines->phone ."\n";
        $mes .= "メールアドレス：". $this->hotlines->email ."\n";
        $mes .= "相談内容：" . $this->hotlines->content;

        return $mes;
    }
    private function sendMsgToLW($msg, $url = null)
    {

       // $systemLwAccessToken = LineWorkConf::query()->where('code', 'LW_ACCESS_TOKEN')->first();
        $access_token = $this->getAndSaveAccessTokenLw();

        if ($access_token) {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ];
            $urlApi = Str::replaceArray('?',
                [
                    $this->channel->bot_id,
                    $this->channel->channel_id
                ], LW_API_SEND_MSG);

            $bodyLink = [
                "content" => [
                    "type" => "link",
                    "contentText" => $msg,
                    "linkText" => "日程確定画面へ",
                    "link" =>  $this->channel->app_url . $url
                ]
            ];
            $bodyText = [
                "content" => [
                    "type" => "text",
                    "text" => $msg
                ]
            ];
            $body = $url ? $bodyLink : $bodyText;
            $dataJson = Http::withHeaders($headers)->post($urlApi, $body)->json();
            Log::info('call api lw ==> ' . json_encode($dataJson));
        }
    }

//    private function checkTokenLw($systemLwAccessToken)
//    {
//        $checkTokenExpire = false;
//        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
//            $headers = [
//                'Authorization' => 'Bearer ' . data_get($systemLwAccessToken, 'value.access_token')
//            ];
//            $check = Http::withHeaders($headers)->get(LW_API_GET_LIST_BOT)->json();
//            if ($check && data_get($check, 'bots')) {
//                $checkTokenExpire = true;
//            } else {
//                error_log('checkTokenLw function:' . json_encode($check));
//                Log::info('checkTokenLw function:' . json_encode($check));
//            }
//        }
//        return $checkTokenExpire;
//    }

//    private function refreshTokenLw(&$systemLwAccessToken)
//    {
//
//        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
//            $body = [
//                'refresh_token' => data_get($systemLwAccessToken, 'value.refresh_token'),
//                'grant_type' => 'refresh_token',
//                'client_id' =>  $this->channel->client_id,
//                'client_secret' => $this->channel->client_secret,
//            ];
//
//            $headers = [
//                'Content-Type' => 'application/x-www-form-urlencoded',
//                'Cookie' => 'LC=en_US; language=en_US'
//            ];
//
//            $dataJson = Http::send('POST', LW_AUTH_TOKEN_URL, ['headers' => $headers, 'form_params' => $body])->json();
//            if ($dataJson && data_get($dataJson, 'access_token')) {
//                $data = $dataJson;
//                $data['refresh_token'] = data_get($systemLwAccessToken, 'value.refresh_token');
//                $systemLwAccessToken->update([
//                    'value' => $data
//                ]);
//            } else {
//                error_log('refreshTokenLw function:' . json_encode($dataJson));
//                Log::info('refreshTokenLw function:' . json_encode($dataJson));
//                $this->getAndSaveAccessTokenLw($systemLwAccessToken);
//            }
//        }
//    }

    private function getAndSaveAccessTokenLw()
    {
        // Tạo một Builder để xây dựng token
        $now = new DateTimeImmutable();
        $pathKey = base_path($this->channel->private_key_path);
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::file($pathKey));
        // Thêm thông tin vào token
        $token = $config->builder()
            ->issuedBy($this->channel->client_id)
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->relatedTo($this->channel->service_account)
            ->getToken($config->signer(), $config->signingKey());

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Cookie' => 'LC=en_US; WORKS_RE_LOC=jp1; WORKS_TE_LOC=jp1; language=en_US'
        ];
        $options = [
            'assertion' => $token->toString(),
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'client_id' => $this->channel->client_id,
            'client_secret' => $this->channel->client_secret,
            'scope' => $this->channel->scope
        ];
        $dataJson = Http::send('POST', LW_AUTH_TOKEN_URL, ['headers' => $headers, 'form_params' => $options])->json();
        if ($dataJson && data_get($dataJson, 'access_token')) {
            return data_get($dataJson, 'access_token');
//            if ($systemLwAccessToken) {
//                $systemLwAccessToken->update([
//                    'code' => 'LW_ACCESS_TOKEN',
//                    'value' => $dataJson
//                ]);
//            } else {
//                $systemLwAccessToken = LineWorkConf::query()->create([
//                    'code' => 'LW_ACCESS_TOKEN',
//                    'value' => $dataJson
//                ]);
//            }
        } else {
            //error_log('getAndSaveAccessTokenLw function:' . json_encode($dataJson));
            Log::info('getAndSaveAccessTokenLw function:' . json_encode($dataJson));
            return null;
        }
    }
}
