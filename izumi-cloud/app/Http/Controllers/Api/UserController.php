<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UserRequest;
use App\Http\Resources\BaseResource;
use App\Jobs\SyncUserJob;
use App\Mail\RegisterMail;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends BaseController
{
    protected $repository;
    protected $userRepository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/user",
     *   tags={"User"},
     *   summary="List user",
     *   operationId="user_index",
     *   @OA\Response(
     *     response=200,
     *     description="response success",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *        example={"code":200,"data":{"result":{{"id":"mull","name":"null","role":"null","created_at":null,"updated_at":null}}}}
     *     )
     *   ),
     *  @OA\Parameter(
     *     name="id",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *  @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *  @OA\Parameter(
     *     name="page",
     *     in="query",
     *     @OA\Schema(
     *     type="integer",
     *     ),
     *     ),
     *  @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     @OA\Schema(
     *     type="integer",
     *     ),
     *     ),
     *  @OA\Parameter(
     *     name="sortby",
     *     in="query",
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *  @OA\Parameter(
     *     name="sorttype",
     *     in="query",
     *     @OA\Schema(
     *     type="integer",
     *     ),
     *     ),
     *  @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Wrong account or password"}
     *     )
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Deny access",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Từ chối quyền truy cập"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(UserRequest $request)
    {
//        if($user->role!='2')
//            return $this->show($user->id);
        $users = $this->repository->getPagination($request);
        return $this->responseJson(200, BaseResource::collection($users));
    }

    /**
     * @OA\Get(
     *   path="/api/user/{id}",
     *   tags={"User"},
     *   summary="User detail",
     *   operationId="user_show",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Submit request successfully",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *     example={"code":200,"data":{"result":{{"id":"mull","name":"null","role":"null","created_at":null,"updated_at":null}}}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Not login"}
     *     )
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Deny access",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Access deny permission"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        try {
            $user = $this->repository->getUserByEmpCode($id);
            return $this->responseJson(CODE_SUCCESS,new BaseResource($user));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/user/{id}",
     *   tags={"User"},
     *   summary="Update user",
     *   operationId="user_update",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          example={"role":"integer","name":"string","id":"string","current_password":"string","password":"string", "confirm_password": "string","email":"example@gmail.com"},
     *          @OA\Schema(
     *           @OA\Property(
     *     property="role",
     *     format="integer",
     *     ),
     *          @OA\Property(
     *     property="name",
     *     format="string",
     *     ),
     *          @OA\Property(
     *     property="id",
     *     format="string",
     *     ),
     *     @OA\Property(
     *          property="email",
     *          format="string",
     *     ),
     *           @OA\Property(
     *     property="current_password",
     *     format="string",
     *     ),
     *           @OA\Property(
     *     property="password",
     *     format="string",
     *     ),
     *           @OA\Property(
     *     property="confirm_password",
     *     format="string",
     *           ),
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *       example={"code":200,"data":{"result":{{"role":"crew","name":"tokiooi","id":112121,"created_at":1604910110,"updated_at":1604910680}}}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Chưa đăng nhập"}
     *     )
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Từ chối quyền truy cập",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Từ chối quyền truy cập"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UserRequest $request, $id)
    {
        $result = $this->repository->update($request->all(), $id);
        if (!$result['message']) {
            SyncUserJob::dispatch($result->id);
            return $this->responseJson(200, new BaseResource($result));
        }
        return $this->responseJsonError(Response::HTTP_UNAUTHORIZED, $result['message']);
    }

    /**
     * @OA\Post(
     *   path="/api/user",
     *   tags={"User"},
     *   summary="Add create",
     *   operationId="user_register",
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
     *     required=false,
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
     *   security={{"auth": {}}},
     * )
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(UserRequest $request)
    {
        $user = $this->repository->create($request->all());
        SyncUserJob::dispatch($user->id);
        return $this->responseJson(CODE_SUCCESS, new BaseResource($user));
    }


    /**
     * @OA\Delete(
     *   path="/api/user/{id}",
     *   tags={"User"},
     *   summary="Delete ..............",
     *   operationId="user_delete",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request Success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":"Send request Success"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * */
    public function destroy($id)
    {
        $this->repository->destroy($id);
        return $this->responseJson(CODE_SUCCESS, null, trans('messages.mes.delete_success'));
    }

    /**
     * @OA\Get(
     *   path="/api/user-interview-pic",
     *   tags={"User"},
     *   summary="List user interview-pic",
     *   operationId="user_index_interview-pic",
     *   @OA\Response(
     *     response=200,
     *     description="response success",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *        example={"code":200,"data":{"result":{{"id":"mull","name":"null","role":"null","created_at":null,"updated_at":null}}}}
     *     )
     *   ),
     *  @OA\Parameter(
     *     name="search",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *  @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Wrong account or password"}
     *     )
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Deny access",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Từ chối quyền truy cập"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userInterviewPic(UserRequest $request)
    {
        $users = $this->repository->getInterviewPic($request);
        return $this->responseJson(200, BaseResource::collection($users));
    }

    /**
     * @OA\Get(
     *   path="/api/line-works-list-pic",
     *   tags={"User"},
     *   summary="List user pic line work",
     *   operationId="user_index_pic_line_work",
     *  @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Wrong account or password"}
     *     )
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Deny access",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Từ chối quyền truy cập"}
     *     )
     *   ),
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userPicLw()
    {
        $users = $this->repository->getListMemberLW();
        return $this->responseJson(200, $users);
    }

    /**
     * @OA\Get(
     *   path="/api/send-mail-set-up-password",
     *   tags={"User"},
     *   summary="Send Mail Set Up Password",
     *   operationId="send_mail_set_up_password",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *     @OA\Parameter(
     *     name="user_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="email",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMailSetUpPassword(UserRequest $request)
    {
        $result = $this->repository->sendMailSetUpPassword($request);
        return $this->responseJson($result['code'], ['message' => $result['message']]);
    }

    /**
     * @OA\Post(
     *   path="/api/profile-informations",
     *   tags={"ProfileInformations"},
     *   summary="Add or update profile informations",
     *   operationId="profile_informations",
     *   @OA\Parameter(
     *     name="image_file_id",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="phone_number",
     *     in="query",
     *     required=false,
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
     *   security={{"auth": {}}},
     * )
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function storeProfileInfor(UserRequest $request)
    {
        $user = $this->repository->updateOrCreateProfileInfor($request->all());
        return $this->responseJson(CODE_SUCCESS, new BaseResource($user));
    }


    /**
     * @OA\Get  (
     *   path="/api/profile-informations",
     *   tags={"ProfileInformations"},
     *   summary="get profile informations",
     *   operationId="get_profile_informations",
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
     *   security={{"auth": {}}},
     * )
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getProfileInfor(UserRequest $request)
    {
        $user = $this->repository->getUserProfileInfor();
        return $this->responseJson(CODE_SUCCESS, $user);
    }
    /**
     * @OA\Get  (
     *   path="/api/user/department/{id}",
     *   tags={"User"},
     *   summary="get user with department",
     *   operationId="get_user_with_department",
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *      @OA\Schema(
     *       type="string",
     *      ),
     *    ),
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
     *   security={{"auth": {}}},
     * )
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public  function getUserWithDepartment($id)
    {
        $users = $this->repository->getUserWithDepartment($id);
        return $this->responseJson(200, $users);
    }

    /**
     * @OA\Post(
     *   path="/api/user/language",
     *   tags={"User"},
     *   summary="Update user language preference",
     *   operationId="update_user_language",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"language"},
     *       @OA\Property(
     *         property="language",
     *         type="string",
     *         enum={"ja", "en", "zh"},
     *         description="Language code (ja: Japanese, en: English, zh: Chinese)",
     *         example="ja"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Language updated successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="Language updated successfully"),
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         @OA\Property(property="language", type="string", example="ja")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=false),
     *       @OA\Property(property="message", type="string", example="Invalid language code"),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\Property(
     *           property="language",
     *           type="array",
     *           @OA\Items(type="string", example="The selected language is invalid.")
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated"
     *   ),
     *   security={{"auth": {}}}
     * )
     */
    public function updateLanguage(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'language' => 'required|string|in:ja,en,zh',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid language code',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $user->language = $request->language;
        $user->save();

        SyncUserJob::dispatch($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully',
            'data' => [
                'language' => $user->language,
            ],
        ]);
    }
}
