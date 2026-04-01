<?php

namespace App\Http\Middleware;

use Closure;
use DateInterval;
use DateTimeImmutable;
use Helper\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class AuthenticateOtherSys extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $authHeader = $request->header('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return ResponseService::responseJsonError(CODE_UNAUTHORIZED, trans('errors.invalid_token'), 'Token not provided');
        }

        $tokenString = $matches[1];
        $apiSecret = Config::get('common.api_shakensho_secret_key');

        // Cấu hình JWT với secret key trong .env
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($apiSecret));

        try {
            // Parse token từ chuỗi
            $token = $config->parser()->parse($tokenString);
            // Đặt ràng buộc validate chữ ký
            $config->setValidationConstraints(new SignedWith($config->signer(), $config->verificationKey()));
            // Validate chữ ký token
            if (!$config->validator()->validate($token, ...$config->validationConstraints())) {
                return ResponseService::responseJsonError(CODE_UNAUTHORIZED, trans('errors.invalid_token'), 'Invalid token signature');
            }

            // Kiểm tra token hết hạn
            if ($token->isExpired(new DateTimeImmutable())) {
                return ResponseService::responseJsonError(CODE_UNAUTHORIZED, trans('errors.invalid_token'), 'Token expired');
            }
        } catch (\Throwable $e) {
            return ResponseService::responseJsonError(CODE_UNAUTHORIZED, $e->getMessage(), 'Authorization key not found');
        }

        return $next($request);
    }
}
