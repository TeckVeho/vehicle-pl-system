<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-12-02
 */

namespace App\Http\Controllers\Api;

use App\Exports\VehicleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Http\Resources\BaseResource;
use App\Http\Resources\VehicleResource;
use App\Http\Resources\VehicleStyleShowResource;
use App\Jobs\SyncVehicleJob;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class VehicleController extends Controller
{
    /**
     * var Repository
     */
    protected $repository;

    public function __construct(VehicleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle",
     *   tags={"Vehicle"},
     *   summary="List vehicle",
     *   operationId="vehicle_index",
     *
     *   @OA\Parameter(
     *     name="department",
     *     in="query",
     *     example="[1,2,3]",
     *
     *     @OA\Schema(
     *      format="object",
     *     ),
     *   ),
     *
     *   @OA\Parameter(
     *     name="vehicle_identification_number",
     *     in="query",
     *     description="vehicle identification number",
     *
     *     @OA\Schema(type="string")
     *   ),
     *
     *   @OA\Parameter(
     *     name="inspection_expiration_date",
     *     in="query",
     *     description="Inspection expiration date",
     *
     *     @OA\Schema(type="string", format="date")
     *   ),
     *
     *   @OA\Parameter(
     *     name="scrap_date",
     *     in="query",
     *     description="Scrap date",
     *
     *     @OA\Schema(type="string", format="date")
     *   ),
     *
     *  @OA\Parameter(
     *     name="sort_by",
     *     in="query",
     *     description="sort by",
     *
     *     @OA\Schema(type="string")
     *   ),
     *
     *  @OA\Parameter(
     *     name="sort_type",
     *     in="query",
     *     description="Sort type",
     *
     *     @OA\Schema(type="string")
     *   ),
     *
     *  @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page",
     *
     *     @OA\Schema(type="string")
     *   ),
     *
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     description="Per page",
     *
     *     @OA\Schema(type="string")
     *   ),
     *
     *  @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *
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
    public function index(VehicleRequest $request)
    {
        $department = Arr::get($request->all(), 'department', null);
        $listRoleName = $request->user()->getRoleNames()->toArray();
        $user = auth()->user()->load(['roles']);
        $role = $user->roles->first();
        if (in_array($role->name, [ROLE_CREW, ROLE_CLERKS, ROLE_TL, ROLE_DEPARTMENT_OFFICE_STAFF])) {
            $department = $user->department->id;
        }

        $data = $this->repository->paginate($request->per_page, ['*'], 'paginate', [
            'number_plate' => Arr::get($request->all(), 'number_plate', null),
            'vehicle_identification_number' => Arr::get($request->all(), 'vehicle_identification_number', null),
            'scrap_date' => Arr::get($request->all(), 'scrap_date', null),
            'department' => $department,
            'month' => Arr::get($request->all(), 'month', null),
            'hide_scrap_date' => $request->boolean('hide_scrap_date'),
            'inspection_expiration_date' => Arr::get($request->all(), 'inspection_expiration_date', null),
        ],
            [
                'sort_by' => Arr::get($request->all(), 'sort_by', null),
                'sort_type' => Arr::get($request->all(), 'sort_type', null),
            ]);

        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/vehicle",
     *   tags={"Vehicle"},
     *   summary="Add new vehicle",
     *   operationId="vehicle_create",
     *
     *   @OA\Parameter(name="name", in="query", required=true,
     *
     *     @OA\Schema(type="string"),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={},
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function store(VehicleRequest $request)
    {
        $truck_classification_number = (int) $request->get('truck_classification', 0);
        $datas = $request->all();
        $datas['truck_classification_number'] = $truck_classification_number;
        try {
            $data = $this->repository->create($datas);

            return $this->responseJson(200, new VehicleResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle/{id}",
     *   tags={"Vehicle"},
     *   summary="Detail Vehicle",
     *   operationId="vehicle_show",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *
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
            $vehicle = $this->repository->find($id);
            foreach ($vehicle->plate_history as $key => $value) {
                $value->date = Carbon::parse($value->date)->format('Y-m-d');
            }
            foreach ($vehicle->vehicle_department_history as $key => $value) {
                $value->date = Carbon::parse($value->date)->format('Y-m-d');
            }

            if ($vehicle->inspection_expiration_date) {
                $vehicle->inspection_expiration_date = Carbon::parse($vehicle->inspection_expiration_date)->format('Y-m-d');
            }
            if ($vehicle->vehicle_delivery_date) {
                $vehicle->vehicle_delivery_date = Carbon::parse($vehicle->vehicle_delivery_date)->format('Y-m-d');
            }
            if ($vehicle->scrap_date) {
                $vehicle->scrap_date = Carbon::parse($vehicle->scrap_date)->format('Y-m-d');
            }
            if ($vehicle->first_registration) {
                if (strlen($vehicle->first_registration) === 7 && strpos($vehicle->first_registration, '-') === 4) {
                    [$year, $month] = explode('-', $vehicle->first_registration);
                    $vehicle->first_registration = sprintf('%04d-%02d', $year, $month);
                }
            }

            return $this->responseJson(200, new BaseResource($vehicle));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/vehicle/{id}",
     *   tags={"Vehicle"},
     *   summary="Update Vehicle",
     *   operationId="vehicle_update",
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *   @OA\RequestBody(
     *
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          example={"name":"string"},
     *
     *          @OA\Schema(
     *            required={"name"},
     *
     *            @OA\Property(
     *              property="name",
     *              format="string",
     *            ),
     *         )
     *      )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":  "............."}}
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=403,
     *     description="Access Deny permission",
     *
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
    public function update(VehicleRequest $request, $id)
    {
        $truck_classification_number = (int) $request->get('truck_classification', 0);
        $attributes = $request->except([]);
        $attributes['truck_classification_number'] = $truck_classification_number;
        $attributes['id'] = $id;
        $data = $this->repository->update($attributes, $id);
        SyncVehicleJob::dispatch($id);

        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/vehicle/{id}",
     *   tags={"Vehicle"},
     *   summary="Delete Vehicle",
     *   operationId="vehicle_delete",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":"Send request success"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $this->repository->delete($id);
        SyncVehicleJob::dispatch($id);

        return $this->responseJson(200, null, trans('messages.mes.delete_success'));
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle/vehicle-for-pl",
     *   tags={"Vehicle"},
     *   summary="Get Vehicle For PL",
     *   operationId="vehicle-for-pl",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"data":"......"}}
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="year_month",
     *     in="query",
     *     required=true,
     *
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *   @OA\Parameter(
     *     name="department_name",
     *     in="query",
     *     required=true,
     *
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *
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
    public function getVehicleForPL(Request $request)
    {
        $data = $this->repository->getVehicleForCloud($request);

        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle/vehicle-itp-for-pl",
     *   tags={"Vehicle"},
     *   summary="Get Vehicle Itp For PL",
     *   operationId="vehicle-itp-for-pl",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"data":"......"}}
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="year_month",
     *     in="query",
     *     required=true,
     *
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *   @OA\Parameter(
     *     name="department_name",
     *     in="query",
     *     required=true,
     *
     *     @OA\Schema(
     *     type="string",
     *     ),
     *     ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *
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
    public function getVehicleItpForCloud(Request $request)
    {
        $data = $this->repository->getVehicleItpForCloud($request);

        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle/vehicle-style-show",
     *   tags={"Vehicle"},
     *   summary="Get Vehicle Style Show",
     *   operationId="vehicle-style-show",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"data":"......"}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVehicleStyleShow()
    {
        $data = $this->repository->getVehicleStyleShow();

        return $this->responseJson(200, VehicleStyleShowResource::collection($data));

    }

    /**
     * @OA\Post(
     *   path="/api/vehicle/add-vehicle-style-show",
     *   tags={"Vehicle"},
     *   summary="Add new vehicle style show",
     *   operationId="add-vehicle-style-show",
     *
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add new vehicle style show",
     *
     *      @OA\JsonContent(
     *           type="array",
     *
     *           @OA\Items(
     *               type="object",
     *
     *               @OA\Property(property="key", description="key", format="string", example="key"),
     *               @OA\Property(property="label", description="label", format="string", example="label"),
     *               @OA\Property(property="position", description="position", format="integer", example=1),
     *               @OA\Property(property="is_deletable", description="is_deletable", format="boolean", example=true),
     *               @OA\Property(property="is_locked", description="is_locked", format="boolean", example=true),
     *               @OA\Property(property="is_display", description="is_display", format="boolean", example=true),
     *               @OA\Property(property="is_selected", description="is_selected", format="boolean", example=true),
     *           ),
     *      ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function addVehicleStyleShow(Request $request)
    {
        $data = $this->repository->addVehicleStyleShow($request->all());

        return $this->responseJson(200, VehicleStyleShowResource::collection($data));
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle/download",
     *   tags={"Vehicle"},
     *   summary="download vehicle",
     *   operationId="download_vehicle",
     *
     *  @OA\Parameter(
     *     name="department",
     *     in="query",
     *     example="[1,2,3]",
     *
     *     @OA\Schema(
     *      format="object",
     *     ),
     *   ),
     *
     *   @OA\Parameter(
     *     name="number_plate",
     *     in="query",
     *     description="number plate",
     *
     *     @OA\Schema(type="string")
     *   ),
     *
     *   @OA\Parameter(
     *     name="vehicle_identification_number",
     *     in="query",
     *     description="vehicle identification number",
     *
     *     @OA\Schema(type="string")
     *   ),
     *
     *   @OA\Parameter(
     *     name="inspection_expiration_date",
     *     in="query",
     *     description="Inspection expiration date",
     *
     *     @OA\Schema(type="string", format="date")
     *   ),
     *
     *   @OA\Parameter(
     *     name="scrap_date",
     *     in="query",
     *     description="Scrap date",
     *
     *     @OA\Schema(type="string", format="date")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *
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
    public function downloadVehicle(Request $request)
    {
        ini_set('memory_limit', '-1');
        $data = $this->repository->getAllVehicle($request->all());
        $fileName = 'vehicle_'.date('Y_m_d').'.csv';

        return Excel::download(new VehicleExport($data), $fileName, null, ['Content-Type' => 'application/octet-stream; charset=SJIS-win', 'Content-Transfer-Encoding' => 'Binary', 'Charset' => 'SJIS-win']);
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle/dashboard",
     *   tags={"Vehicle"},
     *   summary="dashboard vehicle",
     *   operationId="dashboard_vehicle",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *
     * @OA\Parameter(
     *     name="vehicle_identification_number",
     *     in="query",
     *
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *   @OA\Parameter(
     *     name="number_plate",
     *     in="query",
     *
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *  @OA\Parameter(
     *     name="department",
     *     in="query",
     *
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *  @OA\Parameter(
     *     name="inspection_expiration_date",
     *     in="query",
     *
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *
     *  @OA\Parameter(
     *     name="hide_scrap_date",
     *     in="query",
     *
     *     @OA\Schema(
     *      type="boolean",
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *
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
    public function dashboardVehicle(Request $request)
    {
        $department = Arr::get($request->all(), 'department', null);
        $listRoleName = $request->user()->getRoleNames()->toArray();
        if (is_array($listRoleName) && count($listRoleName) == 1 && in_array(ROLE_CREW, $listRoleName)) {
            $user = auth()->user()->load(['department']);
            if ($user->department) {
                $department = $user->department->id;
            }
        }
        $data = $this->repository->getDashboardVehicle([
            'number_plate' => Arr::get($request->all(), 'number_plate', null),
            'vehicle_identification_number' => Arr::get($request->all(), 'vehicle_identification_number', null),
            'scrap_date' => Arr::get($request->all(), 'scrap_date', null),
            'department' => $department,
            'hide_scrap_date' => $request->boolean('hide_scrap_date'),
            'inspection_expiration_date' => Arr::get($request->all(), 'inspection_expiration_date', null),
        ]);

        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle/division",
     *   tags={"Vehicle"},
     *   summary="get department division",
     *   operationId="get_department_division",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *
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
    public function getDepartmentDivision(Request $request)
    {
        $data = $this->repository->getDepartmentDivision($request->all());

        return $this->responseJson(200, new BaseResource($data));
    }
}
