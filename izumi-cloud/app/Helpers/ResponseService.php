<?php
/**
 * Created by PhpStorm.
 * User: Fuji_account
 * Date: 21/06/2019
 * Time: 09:59
 */

namespace Helper;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class ResponseService
{
    public static function responseJson($code = 200, $data = null, $message = null, $messageContent = null)
    {
        $return = [];
        $return['code'] = $code;
        if ($message) $return['message'] = $message;
        if ($messageContent) $return['message_content'] = $messageContent;
        $return['data'] = $data;
        return response()->json($return);
    }

    public static function responseJsonError($code = null, $message = null, $messageContent = null, $internalMessage = null, $dataError = null)
    {
        $status = ($code && $code > 0) ? $code : Response::HTTP_INTERNAL_SERVER_ERROR;
        return response()->json([
            'code' => $status,
            'message' => $message ?? trans('errors.something_error'),
            'message_content' => $messageContent,
            'message_internal' => !in_array(app()->environment(), ['production', 'product']) ? $internalMessage : null,
            'data_error' => $dataError
        ], $status);
    }

    public static function responsePaginate($result, LengthAwarePaginator $resource)
    {
        return [
            'result' => $result,
            'pagination' => [
                'display' => (int)$resource->count(),
                'total_records' => (int)$resource->total(),
                'per_page' => (int)$resource->perPage(),
                'current_page' => (int)$resource->currentPage(),
                'total_pages' => (int)$resource->lastPage(),
            ],
        ];
    }

    public static function handlerInstanceofEx($exception)
    {
        if ($exception instanceof ValidationException) {
            $errors = $exception->errors();
            $errorsMs = [];
            foreach ($errors as $key => $messages) {
                foreach ($messages as $message) {
                    $errorsMs[] = $message;
                }
            }
            return ResponseService::responseJsonError(Response::HTTP_UNPROCESSABLE_ENTITY, data_get(array_values($errors), '0.0'), $errorsMs, $errors);
        } else if ($exception instanceof ModelNotFoundException) {
            return ResponseService::responseJsonError(Response::HTTP_NOT_FOUND, $exception->getMessage(), trans('errors.page_not_found'));
        } else if ($exception instanceof UnauthorizedHttpException | $exception instanceof AuthenticationException) {
            return ResponseService::responseJsonError(Response::HTTP_UNAUTHORIZED, trans('errors.unauthenticated'));
        } else if ($exception instanceof TokenInvalidException) {
            return ResponseService::responseJsonError(Response::HTTP_UNAUTHORIZED, trans('errors.unauthenticated'), trans('errors.invalid_token'));
        } else if ($exception instanceof TokenExpiredException) {
            return ResponseService::responseJsonError(Response::HTTP_UNAUTHORIZED, trans('errors.unauthenticated'), trans('errors.expired_token'));
        } else if ($exception instanceof NotFoundHttpException) {
            return ResponseService::responseJsonError(Response::HTTP_NOT_FOUND, trans('errors.route_not_found'));
        } else if ($exception instanceof HttpException) {
            return ResponseService::responseJsonError($exception->getStatusCode(), trans('errors.access_denied'), $exception->getMessage());
        } else {
            return ResponseService::responseJsonError(Response::HTTP_INTERNAL_SERVER_ERROR, trans('errors.something_error'), $exception->getMessage(), $exception->getTraceAsString());
        }
    }
}
