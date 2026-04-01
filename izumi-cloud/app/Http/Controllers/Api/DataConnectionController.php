<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataConnectionPLRequest;
use App\Http\Requests\DataConnectionRequest;
use App\Models\DataConnection;
use App\Repositories\Contracts\DataConnectionRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\DataConnectionResource;
use App\Jobs\SaveDataTimeSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class DataConnectionController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(DataConnectionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/data_connection",
     *   tags={"DataConnection"},
     *   summary="List data_connection",
     *   operationId="data_connection_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *    @OA\Parameter(
     *     name="page",
     *     in="query",
     *     @OA\Schema(
     *     type="integer",
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     @OA\Schema(
     *     type="integer",
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="start_date",
     *     in="query",
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="end_date",
     *     in="query",
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="sortby",
     *     in="query",
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="sorttype",
     *     in="query",
     *     @OA\Schema(
     *     type="integer",
     *     ),
     *     ),
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

    public function index(DataConnectionRequest $request)
    {
        $data = $this->repository->getIndex($request);
        return $this->responseJson(200, BaseResource::collection($data));
    }


    /**
     * @OA\Post(
     *   path="/api/data_connection",
     *   tags={"DataConnection"},
     *   summary="Add new data_connection",
     *   operationId="data_connection_create",
     *   @OA\Parameter(name="name", in="query", required=true,
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
     *   security={},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(DataConnectionRequest $request)
    {
        try {
            $data = $this->repository->create($request->all());
            return $this->responseJson(200, new DataConnectionResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/data_connection/{id}",
     *   tags={"DataConnection"},
     *   summary="Detail DataConnection",
     *   operationId="data_connection_show",
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
            $department = $this->repository->showDetail($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/data_connection/{id}",
     *   tags={"DataConnection"},
     *   summary="Update DataConnection",
     *   operationId="data_connection_update",
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
    public function update(DataConnectionRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->update($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/data_connection/{id}",
     *   tags={"DataConnection"},
     *   summary="Delete DataConnection",
     *   operationId="data_connection_delete",
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
     * @OA\Get(
     *   path="/api/data_connection/exec-queue/{id}",
     *   tags={"DataConnection"},
     *   summary="DataConnection execQueue",
     *   operationId="data_connection_execQueue",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="date",
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
    public function execQueue($id, DataConnectionRequest $request)
    {
        $data = $this->repository->execQueue($id, $request->get('date'));
        return $this->responseJson(200, $data, "Exec success");
    }

    /**
     * @OA\Post(
     *   path="/api/data_connection/update-status-connection",
     *   tags={"DataConnection"},
     *   summary="Add new data_connection",
     *   operationId="data_connection_update_status",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"item_id","status", "content"},
     *              @OA\Property(description="item id connection",property="item_id",type="integer"),
     *              @OA\Property(description="status update connection",property="status",type="boolean"),
     *              @OA\Property(description="description content success or error",property="content",type="string"),
     *           )
     *       )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function updateStatusConnection(Request $request)
    {
        try {
            $data = $this->repository->changeStatus($request);
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/data_connection/exec-queue-by-pl",
     *   tags={"DataConnection"},
     *   summary="DataConnection exec by pl sys",
     *   operationId="data_connection_execQueue_by_pl",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="date",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="url_api_callback",
     *     in="query",
     *     required=true,
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
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function execQueueByPl(DataConnectionPLRequest $request)
    {
        $dataConnections = DataConnection::query()->whereIn('data_code', ['ICL_1026'])->get();
        foreach ($dataConnections as $dataConnection) {
            $data = $this->repository->execQueue($dataConnection->id, $request->get('date'), $request->get('url_api_callback'), $request->get('department_name'));
        }
        return $this->responseJson(200, 200, "Exec success");
    }
}
