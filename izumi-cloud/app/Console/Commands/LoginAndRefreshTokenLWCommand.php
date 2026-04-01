<?php

namespace App\Console\Commands;

use App\Models\LineWorkConf;
use App\Models\System;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class LoginAndRefreshTokenLWCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:get_and_save_conf_lw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        error_log('start LoginAndRefreshTokenLWCommand');
        Log::info('start LoginAndRefreshTokenLWCommand');
        $lw_conf = Config::get('line_works_conf');
        $systemLwAccessToken = LineWorkConf::query()->where('code', 'LW_ACCESS_TOKEN')->first();
        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
            if (!$this->checkTokenLw($systemLwAccessToken)) {
                $this->refreshTokenLw($systemLwAccessToken);
            }
        } else {
            $this->getAndSaveAccessTokenLw($lw_conf, $systemLwAccessToken);
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
                'client_id' => Arr::get($lw_conf, 'client_id'),
                'client_secret' => Arr::get($lw_conf, 'client_secret'),
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
        $pathKey = base_path(Arr::get($lw_conf, 'private_key_path'));
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::file($pathKey));
        // Thêm thông tin vào token
        $token = $config->builder()
            ->issuedBy(Arr::get($lw_conf, 'client_id'))
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->relatedTo(Arr::get($lw_conf, 'service_account'))
            ->getToken($config->signer(), $config->signingKey());

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Cookie' => 'LC=en_US; WORKS_RE_LOC=jp1; WORKS_TE_LOC=jp1; language=en_US'
        ];
        $options = [
            'assertion' => $token->toString(),
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'client_id' => Arr::get($lw_conf, 'client_id'),
            'client_secret' => Arr::get($lw_conf, 'client_secret'),
            'scope' => Arr::get($lw_conf, 'scope')
        ];
        $dataJson = Http::timeout(60)->send('POST', LW_AUTH_TOKEN_URL, ['headers' => $headers, 'form_params' => $options])->json();
        if ($dataJson && data_get($dataJson, 'access_token')) {
            if ($systemLwAccessToken) {
                $systemLwAccessToken->update([
                    'code' => 'LW_ACCESS_TOKEN',
                    'value' => $dataJson
                ]);
            } else {
                $systemLwAccessToken = LineWorkConf::query()->create([
                    'code' => 'LW_ACCESS_TOKEN',
                    'value' => $dataJson
                ]);
            }
        } else {
            error_log('getAndSaveAccessTokenLw function:' . json_encode($dataJson));
            Log::info('getAndSaveAccessTokenLw function:' . json_encode($dataJson));
        }
    }
}
