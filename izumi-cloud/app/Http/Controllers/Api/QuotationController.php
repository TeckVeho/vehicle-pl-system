<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\QuotationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class QuotationController extends Controller
{
    protected $repository;

    public function __construct(QuotationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/quotations",
     *   tags={"Quotation"},
     *   summary="List quotations",
     *   operationId="quotation_index",
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
     *                   "title": "見積もりタイトル",
     *                   "author": {"id": 1, "name": "test"},
     *                   "authorId": 1,
     *                   "tonnage_id": 1,
     *                   "totalDeliveryCost": 1811619,
     *                   "monthlyTotal": 2173943,
     *                   "createdAt": 1701532800,
     *                   "updatedAt": 1701532800
     *               }
     *           },
     *           "pagination": {
     *               "display": 1,
     *               "total_records": 1,
     *               "per_page": 50,
     *               "current_page": 1,
     *               "total_pages": 1
     *           }
     *       }
     *   }
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="tonnage_id",
     *     in="query",
     *     description="Filter by quotation_master_data id",
     *     @OA\Schema(
     *      type="string"
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="search",
     *     in="query",
     *     description="Search by title or author",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="sortBy",
     *     in="query",
     *     description="Sort by field (created_at, title, vehicle_price)",
     *     @OA\Schema(
     *      type="string",
     *      default="created_at"
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="sortOrder",
     *     in="query",
     *     description="Sort order (asc, desc)",
     *     @OA\Schema(
     *      type="string",
     *      enum={"asc", "desc"},
     *      default="desc"
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *      default=1
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Items per page (max 100)",
     *     @OA\Schema(
     *      type="integer",
     *      default=50,
     *      maximum=100
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
     
        $data = $this->repository->searchWithPagination($request->all());
        return $this->responseJson(200, [
            'result' => $data['result'],
            'total_all' => $data['total_all'],
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/quotations/{id}",
     *   tags={"Quotation"},
     *   summary="Get quotation detail",
     *   operationId="quotation_show",
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
     *           "title": "見積もりタイトル",
     *           "author": {"id": 1, "name": "test"},
     *           "authorId": 1,
     *           "tonnage_id": 1,
     *           "formData": {
     *               "tonnage_id": 1,
     *               "basicHours": "8",
     *               "nightHours": "2",
     *               "hourlyWage": "1500"
     *           },
     *           "calculations": {
     *               "workingHours": 8.5,
     *               "nightTotal": 3000,
     *               "totalPersonnelCost": 15000
     *           },
     *           "totalDeliveryCost": 1811619,
     *           "grossProfit": 362324,
     *           "monthlyTotal": 2173943,
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
     *     description="Not found",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":404,"message":"Quotation not found"}
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
            $quotation = $this->repository->with(['author', 'quotationMasterData', 'deliveryLocations'])->find($id);
            return $this->responseJson(200, new QuotationResource($quotation));
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/quotations",
     *   tags={"Quotation"},
     *   summary="Create new quotation",
     *   operationId="quotation_create",
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *            required={"title", "author_id", "tonnage_id", "total_delivery_cost", "monthly_total"},
     *            @OA\Property(property="title", type="string", example="見積もりタイトル"),
     *            @OA\Property(property="author_id", type="integer", example=1, description="ID of quotation_staff"),
     *            @OA\Property(property="tonnage_id", type="integer", example=1, description="ID of quotation_master_data"),
     *            @OA\Property(property="basic_hours", type="string", example="8"),
     *            @OA\Property(property="night_hours", type="string", example="2"),
     *            @OA\Property(property="overtime_hours", type="string", example="1"),
     *            @OA\Property(property="calc_working_hours", type="number", example=8.5),
     *            @OA\Property(property="hourly_wage", type="string", example="1500"),
     *            @OA\Property(property="night_total", type="string", example="3000"),
     *            @OA\Property(property="overtime_total", type="string", example="2000"),
     *            @OA\Property(property="daily_rate", type="string", example="12000"),
     *            @OA\Property(property="working_days", type="string", example="20"),
     *            @OA\Property(property="monthly_salary", type="string", example="240000"),
     *            @OA\Property(property="calc_benefits", type="number", example=60000),
     *            @OA\Property(property="calc_total_personnel_cost", type="number", example=300000),
     *            @OA\Property(property="loading_location", type="string", example="東京"),
     *            @OA\Property(property="delivery_location", type="string", example="横浜"),
     *            @OA\Property(property="return_location", type="string", example="東京"),
     *            @OA\Property(property="start_time", type="string", example="09:00"),
     *            @OA\Property(property="end_time", type="string", example="18:00"),
     *            @OA\Property(property="loading_time", type="string", example="00:30"),
     *            @OA\Property(property="unloading_time", type="string", example="00:30"),
     *            @OA\Property(property="daily_distance", type="string", example="200"),
     *            @OA\Property(property="working_hours", type="string", example="8.5"),
     *            @OA\Property(property="break_hours", type="string", example="1"),
     *            @OA\Property(property="calc_vehicle_depreciation", type="number", example=50000),
     *            @OA\Property(property="vehicle_price", type="string", example="3000000"),
     *            @OA\Property(property="lease_years", type="string", example="5"),
     *            @OA\Property(property="residual_value_rate", type="string", example="20"),
     *            @OA\Property(property="vehicle_lease", type="string", example="50000"),
     *            @OA\Property(property="total_vehicle_costs", type="number", example=500000),
     *            @OA\Property(property="calc_acquisition_tax", type="number", example=30000),
     *            @OA\Property(property="interest_rate", type="string", example="3.5"),
     *            @OA\Property(property="installments", type="string", example="60"),
     *            @OA\Property(property="vehicle_weight_tax", type="string", example="25000"),
     *            @OA\Property(property="automobile_tax", type="string", example="39500"),
     *            @OA\Property(property="insurance", type="string", example="150000"),
     *            @OA\Property(property="compulsory_insurance", type="string", example="30000"),
     *            @OA\Property(property="monthly_cargo_insurance", type="number", example=4166.67),
     *            @OA\Property(property="cargo_insurance", type="string", example="50000"),
     *            @OA\Property(property="calc_total_taxes", type="number", example=315166.67),
     *            @OA\Property(property="calc_inspection_fee", type="number", example=22000),
     *            @OA\Property(property="calc_legal_inspection", type="number", example=7333.33),
     *            @OA\Property(property="calc_tire_cost", type="number", example=20000),
     *            @OA\Property(property="tire_replace_distance", type="string", example="50000"),
     *            @OA\Property(property="calc_oil_cost", type="number", example=40000),
     *            @OA\Property(property="oil_replace_distance", type="string", example="10000"),
     *            @OA\Property(property="fuel_efficiency", type="string", example="8.5"),
     *            @OA\Property(property="other_repair_costs", type="string", example="10000"),
     *            @OA\Property(property="calc_total_variable_cost", type="number", example=214980.39),
     *            @OA\Property(property="daily_highway_fee", type="string", example="3000"),
     *            @OA\Property(property="calc_monthly_highway_fee", type="number", example=60000),
     *            @OA\Property(property="total_delivery_cost", type="number", example=1811619),
     *            @OA\Property(property="profit_margin", type="string", example="20"),
     *            @OA\Property(property="monthly_total", type="number", example=2173943),
     *            @OA\Property(property="calc_fuel_cost", type="number", example=2173943),
     *            @OA\Property(property="calc_repair_cost", type="number", example=2173943),
     *            @OA\Property(property="gross_profit", type="number", example=2173943),
     *            @OA\Property(property="tow_way_highway", type="boolean", example=false, description="Checkbox 往復 - tính phí cao tốc 2 chiều")
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
     *           "title": "見積もりタイトル",
     *           "author": {"id": 1, "name": "test"},
     *           "authorId": 1,
     *           "tonnage_id": 1,
     *           "formData": {},
     *           "calculations": {},
     *           "totalDeliveryCost": 1811619,
     *           "grossProfit": 362324,
     *           "monthlyTotal": 2173943,
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
     * )
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        try {
            $attributes = $request->all();
            $data = $this->repository->create($attributes);
            $data->load(['author', 'quotationMasterData']);
            return $this->responseJson(200, new QuotationResource($data));
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/quotations/{id}",
     *   tags={"Quotation"},
     *   summary="Update quotation",
     *   operationId="quotation_update",
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
     *            @OA\Property(property="title", type="string", example="更新後のタイトル"),
     *            @OA\Property(property="tonnage_id", type="integer", example=1, description="ID of quotation_master_data"),
     *            @OA\Property(property="basic_hours", type="string", example="8"),
     *            @OA\Property(property="night_hours", type="string", example="2"),
     *            @OA\Property(property="overtime_hours", type="string", example="1"),
     *            @OA\Property(property="calc_working_hours", type="number", example=8.5),
     *            @OA\Property(property="hourly_wage", type="string", example="1500"),
     *            @OA\Property(property="night_total", type="string", example="3000"),
     *            @OA\Property(property="overtime_total", type="string", example="2000"),
     *            @OA\Property(property="daily_rate", type="string", example="12000"),
     *            @OA\Property(property="working_days", type="string", example="20"),
     *            @OA\Property(property="monthly_salary", type="string", example="240000"),
     *            @OA\Property(property="calc_benefits", type="number", example=60000),
     *            @OA\Property(property="calc_total_personnel_cost", type="number", example=300000),
     *            @OA\Property(property="loading_location", type="string", example="東京"),
     *            @OA\Property(property="delivery_location", type="string", example="横浜"),
     *            @OA\Property(property="return_location", type="string", example="東京"),
     *            @OA\Property(property="start_time", type="string", example="09:00"),
     *            @OA\Property(property="end_time", type="string", example="18:00"),
     *            @OA\Property(property="loading_time", type="string", example="00:30"),
     *            @OA\Property(property="unloading_time", type="string", example="00:30"),
     *            @OA\Property(property="daily_distance", type="string", example="200"),
     *            @OA\Property(property="working_hours", type="string", example="8.5"),
     *            @OA\Property(property="break_hours", type="string", example="1"),
     *            @OA\Property(property="calc_vehicle_depreciation", type="number", example=50000),
     *            @OA\Property(property="vehicle_price", type="string", example="3000000"),
     *            @OA\Property(property="lease_years", type="string", example="5"),
     *            @OA\Property(property="residual_value_rate", type="string", example="20"),
     *            @OA\Property(property="vehicle_lease", type="string", example="50000"),
     *            @OA\Property(property="total_vehicle_costs", type="number", example=500000),
     *            @OA\Property(property="calc_acquisition_tax", type="number", example=30000),
     *            @OA\Property(property="interest_rate", type="string", example="3.5"),
     *            @OA\Property(property="installments", type="string", example="60"),
     *            @OA\Property(property="vehicle_weight_tax", type="string", example="25000"),
     *            @OA\Property(property="automobile_tax", type="string", example="39500"),
     *            @OA\Property(property="insurance", type="string", example="150000"),
     *            @OA\Property(property="compulsory_insurance", type="string", example="30000"),
     *            @OA\Property(property="monthly_cargo_insurance", type="number", example=4166.67),
     *            @OA\Property(property="cargo_insurance", type="string", example="50000"),
     *            @OA\Property(property="calc_total_taxes", type="number", example=315166.67),
     *            @OA\Property(property="calc_inspection_fee", type="number", example=22000),
     *            @OA\Property(property="calc_legal_inspection", type="number", example=7333.33),
     *            @OA\Property(property="calc_tire_cost", type="number", example=20000),
     *            @OA\Property(property="tire_replace_distance", type="string", example="50000"),
     *            @OA\Property(property="calc_oil_cost", type="number", example=40000),
     *            @OA\Property(property="oil_replace_distance", type="string", example="10000"),
     *            @OA\Property(property="fuel_efficiency", type="string", example="8.5"),
     *            @OA\Property(property="other_repair_costs", type="string", example="10000"),
     *            @OA\Property(property="calc_total_variable_cost", type="number", example=214980.39),
     *            @OA\Property(property="daily_highway_fee", type="string", example="3000"),
     *            @OA\Property(property="calc_monthly_highway_fee", type="number", example=60000),
     *            @OA\Property(property="total_delivery_cost", type="number", example=2000000),
     *            @OA\Property(property="profit_margin", type="string", example="20"),
     *            @OA\Property(property="monthly_total", type="number", example=2400000),
     *            @OA\Property(property="calc_fuel_cost", type="number", example=2173943),
     *            @OA\Property(property="calc_repair_cost", type="number", example=2173943),
     *            @OA\Property(property="gross_profit", type="number", example=2173943),
     *            @OA\Property(property="tow_way_highway", type="boolean", example=true, description="Checkbox 往復 - tính phí cao tốc 2 chiều")
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"title": "更新後のタイトル"}}
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
     *      example={"code":404,"message":"Quotation not found"}
     *     )
     *   ),
     *   security={{"auth": {}}},
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
            $data->load(['author', 'quotationMasterData']);
            return $this->responseJson(200, new QuotationResource($data));
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/quotations/{id}",
     *   tags={"Quotation"},
     *   summary="Delete quotation",
     *   operationId="quotation_delete",
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
     *       "message": "Quotation deleted successfully",
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
     *      example={"code":404,"message":"Quotation not found"}
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
            return $this->responseJson(200, null, 'Quotation deleted successfully');
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }
}