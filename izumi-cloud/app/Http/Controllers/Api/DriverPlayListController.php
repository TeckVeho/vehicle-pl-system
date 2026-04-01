<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-11-15
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverPlayListRequest;
use App\Repositories\Contracts\DriverPlayListRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\DriverPlayListResource;
use Illuminate\Http\Request;

class DriverPlayListController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(DriverPlayListRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/driver-recorder-play-list-viewer",
     *   tags={"DriverPlayList"},
     *   summary="List driver-play-list viewer",
     *   operationId="driver-play-list_index_viewer",
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
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexViewer(DriverPlayListRequest $request)
    {
        $data = $this->repository->indexViewer();
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Get(
     *   path="/api/driver-play-list",
     *   tags={"DriverPlayList"},
     *   summary="List driver-play-list",
     *   operationId="driver-play-list_index",
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
    public function index(DriverPlayListRequest $request)
    {
        $data = $this->repository->index();
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/driver-play-list",
     *   tags={"DriverPlayList"},
     *   summary="Add new driver-play-list",
     *   operationId="driver-play-list_create",
     *   @OA\Parameter(name="name", in="query", required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *   @OA\Parameter(name="file_id", in="query",
     *     @OA\Schema(type="string"),
     *   ),
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
    public function store(DriverPlayListRequest $request)
    {
        try {
            $data = $this->repository->storePlayList(
                $request->only('name', 'file_id')
            );
            return $this->responseJson(200, new DriverPlayListResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/driver-recorder-play-list-viewer/{id}",
     *   tags={"DriverPlayList"},
     *   summary="Detail DriverPlayList detail viewer",
     *   operationId="driver-play-list_show-viewer",
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
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showViewer($id)
    {
        try {
            $data = $this->repository->showViewer($id);
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/driver-play-list/{id}",
     *   tags={"DriverPlayList"},
     *   summary="Detail DriverPlayList",
     *   operationId="driver-play-list_show",
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
            $department = $this->repository->detail($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/driver-play-list/{id}",
     *   tags={"DriverPlayList"},
     *   summary="Update DriverPlayList",
     *   operationId="driver-play-list_update",
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
     *          example={"name":"string", "file_id":"1"},
     *          @OA\Schema(required={"name"},
     *            @OA\Property(property="name",format="string",),
     *            @OA\Property(property="file_id",format="integer",),
     *          )
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
    public function update(DriverPlayListRequest $request, $id)
    {
        $data = $this->repository->updatePlayList(
            $id,
            $request->only('name', 'file_id')
        );
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/driver-play-list/{id}",
     *   tags={"DriverPlayList"},
     *   summary="Delete DriverPlayList",
     *   operationId="driver-play-list_delete",
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
     * @OA\Put(
     *   path="/api/driver-play-list/update-position",
     *   tags={"DriverPlayList"},
     *   summary="Update Position DriverPlayList",
     *   operationId="driver-play-list_update_position",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *            @OA\Property(property="list_position", type="array", 
     *            @OA\Items(type="object", format="array"),
     *           ),
     *          )
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
    public function updatePosition(Request $request)
    {
        $data = $this->repository->updatePosition($request->all());
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Put(
     *   path="/api/driver-play-list/update-position-for-user",
     *   tags={"DriverPlayListForUser"},
     *   summary="Update Position DriverPlayList",
     *   operationId="driver-play-list_update_position_for_user",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *            @OA\Property(property="list_position", type="array", 
     *            @OA\Items(type="object", format="array"),
     *           ),
     *          )
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
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePositionForUser(Request $request)
    {
        $data = $this->repository->updatePosition($request->all());
        return $this->responseJson(200, new BaseResource($data));
    }

}
