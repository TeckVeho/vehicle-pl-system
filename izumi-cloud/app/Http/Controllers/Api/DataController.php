<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-21
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataRequest;
use App\Repositories\Contracts\DataRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\DataResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
class DataController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(DataRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/data",
     *   tags={"Data"},
     *   summary="List data",
     *   operationId="data_index",
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
     *   @OA\Parameter(
     *     name="search",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *      example="fill by your self"
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="sortby",
     *     in="query",
     *     @OA\Schema(
     *     type="string",
     *      example="name"
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="sorttype",
     *     in="query",
     *     @OA\Schema(
     *     type="string",
     *      example="asc"
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
    public function index(DataRequest $request)
    {
        $data = $this->repository->paginateAndSort(
            $request->per_page,
            Arr::get($request->all(),'sortby', 'id'),
            Arr::get($request->all(),'sorttype', 'asc'),
            Arr::get($request->all(),'search', null)
        );
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/data",
     *   tags={"Data"},
     *   summary="Add new data",
     *   operationId="data_create",
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
    public function store(DataRequest $request)
    {
        try {
            $data = $this->repository->create($request->all());
            return $this->responseJson(200, new DataResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/data/{id}",
     *   tags={"Data"},
     *   summary="Detail Data",
     *   operationId="data_show",
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
            $department = $this->repository->findOneWithDataDetail($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/data/{id}",
     *   tags={"Data"},
     *   summary="Update Data",
     *   operationId="data_update",
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
    public function update(DataRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->update($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/data/{id}",
     *   tags={"Data"},
     *   summary="Delete Data",
     *   operationId="data_delete",
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
     *   path="/api/data/get-list-data-import",
     *   tags={"Data"},
     *   summary="Get List Data Import",
     *   operationId="data_list_import",
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
    public function getListDataImport()
    {
            $data =  $this->repository->getListDataImport();
            return $this->responseJson(200, $data);
    }

    public function orcAiUnit(Request $request) {
        $data = $this->repository->orcAiUnit([
            'response' => $request->response
        ]);
        if ($data) {
            return response()->json([
                'message' => 'success',
                'data' => $data,
                'code' => 200
            ], 200);
        } else {
            return response()->json([
                'message' => 'error',
                'code' => 500
            ], 500);
        }
    }
}
