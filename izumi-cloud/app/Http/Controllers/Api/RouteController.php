<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteRequest;
use App\Imports\RouteImport;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Route;
use App\Repositories\Contracts\RouteRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\RouteResource;
use Helper\Common;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class RouteController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(RouteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/route",
     *   tags={"Route"},
     *   summary="List route",
     *   operationId="route_index",
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
     *     example="1",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     example="20",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_id",
     *     in="query",
     *     example="1",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="customer_id",
     *     example="1",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="route_id",
     *     in="query",
     *     example="1",
     *     @OA\Schema(
     *     type="integer",
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="sort_by",
     *     in="query",
     *     example="department_name",
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="sort_type",
     *     in="query",
     *     example="1",
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
    public function index(RouteRequest $request)
    {
        $data = $this->repository->index($request);
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/route",
     *   tags={"Route"},
     *   summary="Add new route",
     *   operationId="route_create",
     *      @OA\RequestBody(
     *          description="Input data",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                 type="object",
     *                   @OA\Property(property="department_id", description="department_id", format="integer", example="1"),
     *                   @OA\Property(property="name", description="name", format="string", example="Name route"),
     *                   @OA\Property(property="customer_id", description="department_id", format="integer", example="1"),
     *                   @OA\Property(property="route_fare_type", description="route_fare_type", format="integer", example="1000"),
     *                   @OA\Property(property="fare", description="fare", format="integer", example="1000"),
     *                   @OA\Property(property="highway_fee", description="highway_fee", format="integer", example="1000"),
     *                   @OA\Property(property="highway_fee_holiday", description="highway_fee_holiday", format="integer", example="1000"),
     *                   @OA\Property(property="is_government_holiday", description="is_government_holiday", format="integer", example="1"),
     *                   @OA\Property(property="remark", description="remark", format="string", example="remark note"),
     *                   @OA\Property(property="list_week", description="list_week", format="object", example="[1,2]"),
     *                   @OA\Property(property="list_month", description="list_month", format="object", example="[1,28,29,30,31]"),
     *                   @OA\Property(property="list_store", description="list_store", format="object", example="[1,2,3]"),
     *              ),
     *          ),
     *      ),
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
    public function store(RouteRequest $request)
    {
        try {
            $data = $this->repository->storeRoute($request->all());
            return $this->responseJson(200, new RouteResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/route/{id}",
     *   tags={"Route"},
     *   summary="Detail Route",
     *   operationId="route_show",
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
     * @OA\Post(
     *   path="/api/route/update-many",
     *   tags={"Route"},
     *   summary="Update many Route",
     *   operationId="route_update",
     *      @OA\RequestBody(
     *          description="Input data",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                 type="object",
     *                   @OA\Property(property="id", description="id", format="integer", example="1"),
     *                   @OA\Property(property="customer_id", description="department_id", format="integer", example="1"),
     *                   @OA\Property(property="route_fare_type", description="route_fare_type", format="integer", example="1000"),
     *                   @OA\Property(property="fare", description="fare", format="integer", example="1000"),
     *                   @OA\Property(property="highway_fee", description="highway_fee", format="integer", example="1000"),
     *                   @OA\Property(property="highway_fee_holiday", description="highway_fee_holiday", format="integer", example="1000"),
     *                   @OA\Property(property="is_government_holiday", description="is_government_holiday", format="integer", example="1"),
     *                   @OA\Property(property="remark", description="remark", format="string", example="remark note"),
     *                   @OA\Property(property="list_week", description="list_week", format="object", example="[1,2]"),
     *                   @OA\Property(property="list_month", description="list_month", format="object", example="[1,28,29,30,31]"),
     *                   @OA\Property(property="list_store", description="list_store", format="object", example="[1,2,3]"),
     *                 ),
     *              ),
     *          ),
     *      ),
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
    public function updateMany(RouteRequest $request)
    {
        $attributes = $request->except([]);
        $data = $this->repository->updateRoute($attributes);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/route/{id}",
     *   tags={"Route"},
     *   summary="Delete Route",
     *   operationId="route_delete",
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
        $route = Route::where('id', $id)->withCount('courses')->first();
        $check = $route->courses()->count();
        if ($check > 0) {
            throw ValidationException::withMessages(['このルートはコースに組み込まれているため削除できません。']);
        }
        $this->repository->delete($id);
        return $this->responseJson(200, null, trans('messages.mes.delete_success'));
    }

    /**
     * @OA\Post(
     *   path="/api/route/import",
     *   tags={"Route"},
     *   summary="import route",
     *   operationId="import_route",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="file to upload",
     *                   property="file",
     *                   type="string",
     *                   format="binary",
     *               ),
     *           )
     *       )
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
    public function import(RouteRequest $request)
    {
        try {
//            ini_set('memory_limit', '-1');
            Common::setInputEncoding($request->file('file'));
            $departments = Department::pluck('id', 'name')->toArray();
            $customers = Customer::pluck('id', 'customer_name')->toArray();
            Excel::import(new RouteImport($departments, $customers), $request->file('file'));
            return $this->responseJson(Response::HTTP_OK, null, trans('messages.mes.import_success'));

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
