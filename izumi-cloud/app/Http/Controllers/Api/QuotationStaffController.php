<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\QuotationStaffRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\QuotationStaffResource;
use Illuminate\Http\Request;

class QuotationStaffController extends Controller
{
    protected $repository;

    public function __construct(QuotationStaffRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/quotation-staff",
     *   tags={"QuotationStaff"},
     *   summary="List all staff",
     *   operationId="quotation_staff_index",
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
     *                   "id": 1,
     *                   "name": "test",
     *                   "created_at": 1701532800,
     *                   "updated_at": 1701532800
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
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $data = $this->repository->paginate($request->get('per_page', 15));
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Get(
     *   path="/api/quotation-staff/{id}",
     *   tags={"QuotationStaff"},
     *   summary="Get staff detail",
     *   operationId="quotation_staff_show",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="integer",
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
     *           "id": 1,
     *           "name": "test",
     *           "created_at": 1701532800,
     *           "updated_at": 1701532800
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
     *   @OA\Response(
     *     response=404,
     *     description="Not found",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":404,"message":"Staff not found"}
     *     )
     *   ),
     * )
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $staff = $this->repository->find($id);
            return $this->responseJson(200, new BaseResource($staff));
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/quotation-staff",
     *   tags={"QuotationStaff"},
     *   summary="Add new staff",
     *   operationId="quotation_staff_create",
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *            required={"name"},
     *            @OA\Property(property="name", type="string", example="山田太郎")
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *       "code": 200,
     *       "data": {
     *           "id": 1,
     *           "name": "山田太郎",
     *           "created_at": 1701532800,
     *           "updated_at": 1701532800
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
     * )
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        try {
            $data = $this->repository->create($request->all());
            return $this->responseJson(200, new QuotationStaffResource($data));
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/quotation-staff/{id}",
     *   tags={"QuotationStaff"},
     *   summary="Update staff",
     *   operationId="quotation_staff_update",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *            required={"name"},
     *            @OA\Property(property="name", type="string", example="更新後の名前")
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "更新後の名前"}}
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
     *   @OA\Response(
     *     response=404,
     *     description="Not found",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":404,"message":"Staff not found"}
     *     )
     *   ),
     * )
     * Update the specified resource in storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $attributes = $request->except(['id', 'created_at', 'updated_at']);
            $data = $this->repository->update($attributes, $id);
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/quotation-staff/{id}",
     *   tags={"QuotationStaff"},
     *   summary="Delete staff",
     *   operationId="quotation_staff_delete",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *       "code": 200,
     *       "message": "Staff deleted successfully",
     *       "data": null
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
     *   @OA\Response(
     *     response=404,
     *     description="Not found",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":404,"message":"Staff not found"}
     *     )
     *   ),
     * )
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        try {
            $this->repository->delete($id);
            return $this->responseJson(200, null, 'Staff deleted successfully');
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }
}
