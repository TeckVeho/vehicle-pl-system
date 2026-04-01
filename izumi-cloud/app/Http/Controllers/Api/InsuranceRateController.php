<?php
/**
 * Created by VeHo.
 * Year: 2023-03-22
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InsuranceRateRequest;
use App\Repositories\Contracts\InsuranceRateRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\InsuranceRateResource;
use Illuminate\Http\Request;

class InsuranceRateController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(InsuranceRateRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/insurance-rate",
     *   tags={"InsuranceRate"},
     *   summary="List InsuranceRate",
     *   operationId="insurance_rate_index",
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
    public function index(InsuranceRateRequest $request)
    {
        //$data = $this->repository->paginate($request->per_page);
        $data = $this->repository->get();
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Get(
     *   path="/api/insurance-rate/{id}",
     *   tags={"InsuranceRate"},
     *   summary="Detail InsuranceRate",
     *   operationId="insurance_rate_show",
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
     *   path="/api/insurance-rate/{id}",
     *   tags={"InsuranceRate"},
     *   summary="Update InsuranceRate",
     *   operationId="insurance_rate_update",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Data",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="change_rate", type="number", format="double", example="0.04920",),
     *           @OA\Property(property="applicable_date", type="string", format="date", example="2023-05-01",),
     *       ),
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
    public function update(InsuranceRateRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->updateInsuranceRate($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Get(
     *   path="/api/insurance-rate/history",
     *   tags={"InsuranceRate"},
     *   summary="List InsuranceRate History",
     *   operationId="insurance_rate_history",
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
    public function listHistory(InsuranceRateRequest $request)
    {
        $data = $this->repository->listHistory();
        return $this->responseJson(200, $data);
    }
}
