<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-02-06
 */

namespace App\Http\Controllers\Api;

use App\Exports\CourseExport;
use App\Exports\ExportUserContact;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserContactsRequest;
use App\Repositories\Contracts\UserContactsRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\UserContactsResource;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UserContactsController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(UserContactsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/user-contacts",
     *   tags={"UserContacts"},
     *   summary="List user_contacts",
     *   operationId="user_contacts_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *      name="user_id",
     *      in="query",
     *      @OA\Schema(
     *       type="string",
     *      ),
     *    ),
     *   @OA\Parameter(
     *      name="user_name",
     *      in="query",
     *      @OA\Schema(
     *       type="string",
     *      ),
     *    ),
     *   @OA\Parameter(
     *      name="department_name",
     *      in="query",
     *      @OA\Schema(
     *       type="string",
     *      ),
     *    ),
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
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
    public function index(UserContactsRequest $request)
    {
        $data = $this->repository->getList($request->all());
        return $this->responseJson(200, UserContactsResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/user-contacts",
     *   tags={"UserContacts"},
     *   summary="Add new user_contacts",
     *   operationId="user_contacts_create",
     *   @OA\RequestBody(
     *           description="Input data",
     *           @OA\MediaType(
     *               mediaType="application/json",
     *               @OA\Schema(
     *                  type="object",
     *                    @OA\Property(property="post_code", description="post_code", format="string", example="8386"),
     *                    @OA\Property(property="address", description="address", format="string", example="Nam Định"),
     *                    @OA\Property(property="tel", description="tel", format="string", example="666666666"),
     *                    @OA\Property(property="personal_tel", description="personal_tel", format="string", example="999999999"),
     *                    @OA\Property(property="list_user_contact_info",
     *                           description="list_user_contact_info",
     *                           type="array",
     *                              @OA\Items(
     *                                  @OA\Property(property="group",type="integer",example="1"),
     *                                  @OA\Property(property="urgent_contact_name",type="string",example="Thư"),
     *                                  @OA\Property(property="urgent_contact_relation",type="string",example="GT"),
     *                                  @OA\Property(property="urgent_contact_tel",type="string",example="888888888")
     *                           ),
     *                     ),
     *               ),
     *           ),
     *       ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(UserContactsRequest $request)
    {
        try {
            $data = $this->repository->createUserContacts($request->all());
            return $this->responseJson(200, new UserContactsResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/user-contacts/download",
     *   tags={"UserContacts"},
     *   summary="download UserContacts",
     *   operationId="user_contacts_download",
     *     @OA\Parameter(
     *       name="user_id",
     *       in="query",
     *       @OA\Schema(
     *        type="string",
     *       ),
     *     ),
     *    @OA\Parameter(
     *       name="user_name",
     *       in="query",
     *       @OA\Schema(
     *        type="string",
     *       ),
     *     ),
     *    @OA\Parameter(
     *       name="department_name",
     *       in="query",
     *       @OA\Schema(
     *        type="string",
     *       ),
     *     ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
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
    public function download(UserContactsRequest $request)
    {
        try {
            $data = $this->repository->download($request->all());

            $fileName = '緊急連絡先マスタ.xlsx';
            return Excel::download(new ExportUserContact($data), $fileName, null, ['Content-Type' => 'application/octet-stream; charset=SJIS-win', 'Content-Transfer-Encoding' => 'Binary', 'Charset' => 'SJIS-win']);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/user-contacts/user-contacts-profile",
     *   tags={"UserContactsrofile"},
     *   summary="user contacts profile",
     *   operationId="user_contacts_profile",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
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
    public function getUserContactsProfile(UserContactsRequest $request)
    {
        $data = $this->repository->getUserContactsProfile();
        return $this->responseJson(200, $data);
    }

    /**
     * @OA\Get(
     *   path="/api/user-contacts/check-update-user-contact",
     *   tags={"UserContacts"},
     *   summary="Check update user contact",
     *   operationId="check_update_user_contact",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
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
    public function checkUpdateUserContact(UserContactsRequest $request)
    {
        $data = $this->repository->checkUpdateUserContact();
        return $this->responseJson(200, $data);
    }

}
