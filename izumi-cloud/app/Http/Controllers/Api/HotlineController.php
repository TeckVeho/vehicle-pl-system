<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-06-10
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotlineRequest;
use App\Repositories\Contracts\HotlineRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\HotlineResource;
use Illuminate\Http\Request;

class HotlineController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(HotlineRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/hotline",
     *   tags={"Hotline"},
     *   summary="List hotline",
     *   operationId="hotline_index",
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
    public function index(HotlineRequest $request)
    {
        $data = $this->repository->paginate($request->per_page);
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/hotline",
     *   tags={"Hotline"},
     *   summary="Add new hotline",
     *   operationId="hotline_create",
     *   @OA\Parameter(name="username", in="query",
     *     @OA\Schema(type="string"),
     *   ),
     *   @OA\Parameter(name="channel_id", in="query",
     *       @OA\Schema(type="string"),
     *    ),
     *   @OA\Parameter(name="content", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *    @OA\Parameter(name="check_anonymous_flag", in="query", required=true,
     *        @OA\Schema(type="integer"),
     *         example=0,
     *     ),
     *     @OA\Parameter(name="contact_flag", in="query",
     *         @OA\Schema(type="integer"),
     *          example=0,
     *      ),
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
    public function store(HotlineRequest $request)
    {
        try {
            $data = $this->repository->createHotline($request->all());
            return $this->responseJson(200, new HotlineResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/hotline/{id}",
     *   tags={"Hotline"},
     *   summary="Detail Hotline",
     *   operationId="hotline_show",
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
    public function show($id)
    {
        try {
            $department = $this->repository->find($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/hotline/{id}",
     *   tags={"Hotline"},
     *   summary="Update Hotline",
     *   operationId="hotline_update",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          example={"name":"string"},
     *          @OA\Schema(
     *            required={"name"},
     *            @OA\Property(
     *              property="name",
     *              format="string",
     *            ),
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":  "............."}}
     *     ),
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Access Deny permission",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Access Deny permission"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(HotlineRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->update($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/hotline/{id}",
     *   tags={"Hotline"},
     *   summary="Delete Hotline",
     *   operationId="hotline_delete",
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
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":"Send request success"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $this->repository->delete($id);
        return $this->responseJson(200, null, trans('messages.mes.delete_success'));
    }

    /**
     * @OA\Post(
     *   path="/api/setting/channel-id",
     *   tags={"Hotline"},
     *   summary="Add new channel-id",
     *   operationId="create_channel_id",
     *     @OA\Parameter(name="name", in="query",
     *      @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(name="client_secret", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="client_id", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="scope", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="response_type", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="state", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="bot_id", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="channel_id", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="app_url", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="service_account", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="private_key_path", in="query",
     *      @OA\Schema(type="string"),
     *    ),
     *     @OA\Parameter(name="environment", in="query",
     *      @OA\Schema(type="string"),
     *    ),
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
    public function storeChannelId(HotlineRequest $request)
    {
        try {
            $data = $this->repository->createChannelId($request->all());
            return $this->responseJson(200, $data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/hotline/channel-id",
     *   tags={"Hotline"},
     *   summary="List channel-id",
     *   operationId="list_channel_id",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *
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
    public function getChanel(Request $request)
    {
        $data = $this->repository->getlistChannel();
        return $this->responseJson(200, $data);
    }
}
