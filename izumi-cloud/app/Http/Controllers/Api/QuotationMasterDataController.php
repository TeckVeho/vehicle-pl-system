<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\QuotationMasterDataRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\QuotationMasterDataResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuotationMasterDataController extends Controller
{
    protected $repository;

    public function __construct(QuotationMasterDataRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/quotation-master-data",
     *   tags={"QuotationMasterData"},
     *   summary="Get all quotation master data grouped by tonnage",
     *   operationId="quotation_master_data_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *       "code": 200,
     *       "data": {
     *           "3t": {
     *               "carInspectionPrice": 264000,
     *               "regularInspectionPrice": 22000,
     *               "tirePrice": 50000,
     *               "oilChangePrice": 20000,
     *               "fuelUnitPrice": 5
     *           },
     *           "4t": {
     *               "carInspectionPrice": 264000,
     *               "regularInspectionPrice": 22000,
     *               "tirePrice": 50000,
     *               "oilChangePrice": 20000,
     *               "fuelUnitPrice": 5
     *           },
     *           "10t": {
     *               "carInspectionPrice": 264000,
     *               "regularInspectionPrice": 22000,
     *               "tirePrice": 50000,
     *               "oilChangePrice": 20000,
     *               "fuelUnitPrice": 5
     *           }
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
     * Get all quotation master data grouped by tonnage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = $this->repository->getAllGroupedByTonnage();
        return $this->responseJson(200, $data);
    }

    /**
     * @OA\Get(
     *   path="/api/quotation-master-data/{tonnage}",
     *   tags={"MasterData"},
     *   summary="Get quotation master data by tonnage",
     *   operationId="quotation_master_data_show",
     *   @OA\Parameter(
     *     name="tonnage",
     *     in="path",
     *     required=true,
     *     description="Tonnage (3t, 4t, 10t)",
     *     @OA\Schema(
     *      type="string",
     *      enum={"3t", "4t", "10t"}
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
     *           "tonnage": "3t",
     *           "carInspectionPrice": 264000,
     *           "regularInspectionPrice": 22000,
     *           "tirePrice": 50000,
     *           "oilChangePrice": 20000,
     *           "fuelUnitPrice": 5,
     *           "createdAt": 1701532800,
     *           "updatedAt": 1701532800
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
     *     description="Quotation master data not found",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":404,"message":"Master data not found"}
     *     )
     *   ),
     * )
     * Get master data by tonnage.
     *
     * @param string $tonnage
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($tonnage)
    {
        try {
            $masterData = $this->repository->findByTonnage($tonnage);
            if (!$masterData) {
                return $this->responseJsonError(404, 'Master data not found');
            }
            return $this->responseJson(200, new QuotationMasterDataResource($masterData));
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/quotation-master-data/update",
     *   tags={"QuotationMasterData"},
     *   summary="Update quotation master data",
     *   operationId="quotation_master_data_update",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *            type="object",
     *            additionalProperties={
     *              @OA\Schema(
     *                type="object",
     *                @OA\Property(property="car_inspection_price", type="number", example=280000),
     *                @OA\Property(property="regular_inspection_price", type="number", example=22000),
     *                @OA\Property(property="tire_price", type="number", example=55000),
     *                @OA\Property(property="oil_change_price", type="number", example=20000),
     *                @OA\Property(property="fuel_unit_price", type="number", example=5.5)
     *              )
     *            },
     *            example={
     *              "3t": {
     *                "car_inspection_price": 280000,
     *                "regular_inspection_price": 22000,
     *                "tire_price": 55000,
     *                "oil_change_price": 20000,
     *                "fuel_unit_price": 5.5
     *              },
     *              "4t": {
     *                "car_inspection_price": 280000,
     *                "regular_inspection_price": 22000,
     *                "tire_price": 55000,
     *                "oil_change_price": 20000,
     *                "fuel_unit_price": 5.5
     *              },
     *              "10t": {
     *                "car_inspection_price": 280000,
     *                "regular_inspection_price": 22000,
     *                "tire_price": 55000,
     *                "oil_change_price": 20000,
     *                "fuel_unit_price": 5.5
     *              }
     *            }
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"tonnage": "3t","carInspectionPrice": 280000}}
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
     *     description="Master data not found",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":404,"message":"Master data not found"}
     *     )
     *   ),
     * )
     * Update quotation master data by tonnage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $data = $this->repository->updateAll($request);
            return $this->responseJson(200, $data);
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }
}
