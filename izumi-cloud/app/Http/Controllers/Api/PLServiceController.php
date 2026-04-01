<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-05-29
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PLServiceRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\PLServiceResource;
use Illuminate\Http\Request;

class PLServiceController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(PLServiceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/pl-service/pl-pca",
     *   tags={"PLService"},
     *   summary="save data pl_pca",
     *   operationId="pl_pca",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"data_import", "status"}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="year_month",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_name",
     *     in="query",
     *     @OA\Schema(
     *     type="string",
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
    public function getDataPCAForPl(Request $request)
    {
        try {
            $data = $this->repository->getDataPCAForPl($request);
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * @OA\Get(
     *   path="/api/pl-service/time_sheet_index",
     *   tags={"PLService"},
     *   summary="get data time sheet for PL",
     *   operationId="time_sheet_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"data_import", "status"}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="year_month",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_name",
     *     in="query",
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *     @OA\Parameter(
     *     name="job_type",
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
    public function getDataTimeSheetIndex(Request $request)
    {
        try {
            $data = $this->repository->getTimeSheet($request);
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * @OA\Get(
     *   path="/api/pl-service/welfare_expenses_for_cloud",
     *   tags={"PLService"},
     *   summary="welfare expenses for cloud",
     *   operationId="welfare_expenses_for_cloud",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"data_import", "status"}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="department_name",
     *     in="query",
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *     @OA\Parameter(
     *     name="job_type",
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
    public function totalWelfareExpenses(Request $request)
    {
        try {
            $data = $this->repository->totalWelfareExpenses($request);
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * @OA\Get(
     *   path="/api/pl-service/vehicle-mahoujin-data",
     *   tags={"PLService"},
     *   summary="get mahoujin for PL",
     *   operationId="mahoujin_for_PL",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"data": "..........."}}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="year_month",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_name",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *     type="string",
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
    public function getMahoujin(Request $request)
    {
        $data = $this->repository->getMahoujin($request);
        return $this->responseJson(200, new BaseResource($data));
    }
    /**
     * @OA\Get(
     *   path="/api/pl-service/maintenance-cost",
     *   tags={"PLService"},
     *   summary="get maintenance cost for PL",
     *   operationId="maintenance_cost_for_PL",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"total_amount_including_tax": "..........."}}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="year_month",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_name",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *     type="string",
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
    public function getMaintenanceCost(Request $request)
    {
        $data = $this->repository->getMaintenanceCost($request);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Get(
     *   path="/api/pl-service/vehicle-for-pl",
     *   tags={"PLService"},
     *   summary="Get Vehicle For PL",
     *   operationId="pl-service-vehicle-for-pl",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"data":"......"}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="year_month",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_name",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *     type="string",
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
    public function getVehicleForPL(Request $request) {
        $data = $this->repository->getVehicleForCloud($request);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Get(
     *   path="/api/pl-service/vehicle-itp-for-pl",
     *   tags={"PLService"},
     *   summary="Get Vehicle Itp For PL",
     *   operationId="pl-service-vehicle-itp-for-pl",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"data":"......"}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="year_month",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_name",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *     type="string",
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
    public function getVehicleItpForCloud(Request $request) {
        $data = $this->repository->getVehicleItpForCloud($request);
        return $this->responseJson(200, new BaseResource($data));
    }
}
