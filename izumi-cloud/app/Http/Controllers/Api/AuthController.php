<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterAccountRequest;
use App\Http\Requests\RegisterPasswordRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RemindRequest;
use App\Http\Requests\UserRequest;

use App\Http\Resources\UserResource;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Repository\AuthRepository;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Jobs\MailJob;
use Tests\Feature\RegistertAccountTest;
use App\Models\User;


class AuthController extends BaseController
{
    protected $authRepository;
    protected $userRepository;

    public function __construct(AuthRepositoryInterface $authRepository, UserRepositoryInterface $userRepository)
    {
        $this->authRepository = $authRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *   path="/api/auth/login",
     *   tags={"Auth"},
     *   summary="Login",
     *   operationId="user_login",
     *   @OA\Parameter(
     *     name="id",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *     example="111111",
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *     example="123456789",
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Submit request successfully",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"access_token":"Bearer ...",
     *     "profile":{"id":121232,
     *     "name":null,
     *     "role":null,
     *     "created_at":null
     *     }}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login failed",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Wrong account or password"}
     *     )
     *   ),
     *   security={},
     * )
     * Display a listing of the resource.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authRepository->doLogin($request);
        if ($result['attempt']) {
            $user = $result['user'];
            $token = $result['attempt'] != 'temporary_password' ? "Bearer " . $result['attempt'] : $result['attempt'];
            return $this->responseJson(Response::HTTP_OK, [
                'access_token' => $token,
                'profile' => new UserResource($user),
                'roles' => $user->getRoleNames(),
            ]);
        }
        return $this->responseJsonError(Response::HTTP_UNAUTHORIZED, $result['mes']);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/mobile/login",
     *   tags={"Auth"},
     *   summary="Mobile Login",
     *   operationId="user_login_mobile",
     *   @OA\Parameter(
     *     name="id",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Submit request successfully",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"access_token":"Bearer ...",
     *     "profile":{"id":121232,
     *     "name":null,
     *     "role":null,
     *     "created_at":null
     *     }}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login failed",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Wrong account or password"}
     *     )
     *   ),
     *   security={},
     * )
     * Display a listing of the resource.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mobileLogin(LoginRequest $request)
    {
        $result = $this->authRepository->doLoginMobile($request);
        if ($result['attempt']) {
            $user = $result['user'];
            $token = $result['attempt'] != 'temporary_password' ? "Bearer " . $result['attempt'] : $result['attempt'];
            return $this->responseJson(Response::HTTP_OK, [
                'access_token' => $token,
                'profile' => new UserResource($user),
                'roles' => $user->getRoleNames(),
            ]);
        }
        return $this->responseJsonError(Response::HTTP_UNAUTHORIZED, $result['mes']);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/register",
     *   tags={"Auth"},
     *   summary="Register",
     *   operationId="user_auth_register",
     *   @OA\Parameter(
     *     name="role",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="id",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="password_confirmation",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"access_token":"","profile":{"id":1,
     *     "role":null,
     *     "name":null,
     *     "id":"example@gmail.com",
     *     "created_at":1570031021}}}
     *     )
     *   ),
     *   security={},
     * )
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            /* @see AuthRepository::register() */
            $user = $this->authRepository->register($request->all());

            $token = JWTAuth::fromUser($user);
            DB::commit();
            return $this->responseJson(200, [
                'access_token' => "Bearer " . $token,
                'profile' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *   path="/api/remind-passwords",
     *   tags={"Auth"},
     *   summary="Reset password",
     *   operationId="user_remind",
     *   @OA\Parameter(
     *     name="emp_code",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Submit request successfully",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":"Submit request successfully"}
     *     )
     *   ),
     *   security={},
     * )
     * @param RemindRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function remindPassword(RemindRequest $request)
    {
        $data = $this->authRepository->remindPassword($request);
        if ($data) {
            return $this->responseJson(Response::HTTP_OK, 'Submit request successfully');
        } else
            return $this->responseJsonError(Response::HTTP_UNAUTHORIZED, $this->authRepository->mes);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/refresh",
     *   tags={"Auth"},
     *   summary="User register",
     *   operationId="user_reset_token",
     *   @OA\Response(
     *
     *     response=200,
     *     description="Submit request successfully",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"access_token":"...."}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function refresh()
    {
        return $this->responseJson(200, [
            'access_token' => auth()->login(auth()->user()),
            'exp_token' => auth()->payload('exp'),
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/profile",
     *   tags={"Auth"},
     *   summary="Get Profile",
     *   operationId="user_profile",
     *   @OA\Response(
     *     response=200,
     *     description="Submit request successfully",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1, "emp_name":"null","email":"exam@gmail.com","emp_code":"null","":""}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login failed",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Wrong account or password"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        $user = auth()->user()->load(['department']);
        return $this->responseJson(Response::HTTP_OK, [
            'profile' => new UserResource($user),
            'roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * @OA\Put(
     *   path="/api/change_pass/{emp_code}",
     *   tags={"Auth"},
     *   summary="Change temporaty password",
     *   operationId="auth_change_pass",
     *   @OA\Parameter(name="emp_code",in="path",required=true,@OA\Schema(type="string",),),
     *   @OA\Parameter(name="password",in="query",required=true,@OA\Schema(type="string",),),
     *   @OA\Parameter(name="password_confirmation",in="query",required=true,@OA\Schema(type="string",),),
     *   @OA\Response(
     *     response=200,
     *     description="request sent successfully",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1}}
     *     ),
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Deny access",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Deny access"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(UserRequest $request, $emp_code)
    {
        $result = $this->authRepository->changeTempPass($request->all(), $emp_code);
        if (!$result['message']) {
            $loginRequest = new LoginRequest();
            $loginRequest->merge(['emp_code' => $emp_code, 'password' => $request->password]);
            return $this->login($loginRequest);
        }
        return $this->responseJsonError(Response::HTTP_UNAUTHORIZED, $result['message']);
    }

    public function RegisterAccount(RegisterAccountRequest $request)
    {
        DB::beginTransaction();
        try {
            $regis = $this->authRepository->RegisterAccount($request);
            return $this->responseJsonError($regis['code'], $regis['message']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function RegisterPassword(RegisterPasswordRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->authRepository->RegisterPassword($request);
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
