<?php

namespace App\Http\Middleware;

use Closure;
use Helper\ResponseService;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Authenticate extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if ((!$user = $this->auth->parseToken()->authenticate())) {
                return ResponseService::responseJsonError(CODE_NOT_FOUND, trans('errors.user_not_found'), 'user not found');
            }
        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return ResponseService::responseJsonError(CODE_UNAUTHORIZED, trans('errors.invalid_token'), 'token not provided');
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return ResponseService::responseJsonError(CODE_UNAUTHORIZED, trans('errors.expired_token'), 'token expire');
            } else {
                return ResponseService::responseJsonError(CODE_UNAUTHORIZED, $e->getMessage(), 'Authorization Token not found');
            }
        }
        return $next($request);
    }
}
