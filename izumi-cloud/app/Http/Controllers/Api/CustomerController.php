<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(CustomerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/customer",
     *   tags={"Customer"},
     *   summary="List customer",
     *   operationId="customer_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *       "code": 200,
     *       "data": {
     *           "result": {
     *               {
     *                   "id": 2,
     *                   "customer_name": "Vinmart+ Update",
     *                   "deleted_at": null,
     *                   "created_at": 1657081589,
     *                   "updated_at": 1657081589
     *               }
     *           },
     *           "pagination": {
     *               "display": 1,
     *               "total_records": 1,
     *               "per_page": 15,
     *               "current_page": 1,
     *               "total_pages": 1
     *           }
     *       }
     *   }
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
    public function index(CustomerRequest $request)
    {
        $data = $this->repository->paginate($request->per_page);
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/customer",
     *   tags={"Customer"},
     *   summary="Add new customer",
     *   operationId="customer_create",
     *   @OA\Parameter(name="customer_name", in="query", required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *       "code": 200,
     *       "data": {
     *           "customer_name": "Customer Create",
     *           "updated_at": 1657081589,
     *           "created_at": 1657081589,
     *           "id": 2
     *       }
     *   }
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(CustomerRequest $request)
    {
        try {
            $data = $this->repository->create($request->all());
            return $this->responseJson(200, new CustomerResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/customer/{id}",
     *   tags={"Customer"},
     *   summary="Detail Customer",
     *   operationId="customer_show",
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
     *      example={
     *       "code": 200,
     *       "data": {
     *           "id": 2,
     *           "customer_name": "Vinmart+ Update",
     *           "deleted_at": null,
     *           "created_at": 1657081589,
     *           "updated_at": 1657081589
     *       }
     *   }
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
     *   path="/api/customer/{id}",
     *   tags={"Customer"},
     *   summary="Update Customer",
     *   operationId="customer_update",
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
     *          example={"customer_name":"string"},
     *          @OA\Schema(
     *            required={"customer_name"},
     *            @OA\Property(
     *              property="customer_name",
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
    public function update(CustomerRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->update($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/customer/{id}",
     *   tags={"Customer"},
     *   summary="Delete Customer",
     *   operationId="customer_delete",
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
     *      example={
     *       "code": 200,
     *       "message": "delete success",
     *       "data": null
     *   }
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
     *   path="/api/customer/list-all",
     *   tags={"Customer"},
     *   summary="List all Customer",
     *   operationId="customer_index_all",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"1": "本社","2": "横浜第一",}},
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
    public function listAll()
    {
        $datas = Customer::select('customer_name', 'id')->get();
        return $this->responseJson(200, $datas);
    }
}
