<?php
/**
 * Created by VeHo.
 * Year: 2026-02-13
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleMaintenanceCostRequest;
use App\Repositories\Contracts\VehicleMaintenanceCostRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\VehicleMaintenanceCostResource;
use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class VehicleMaintenanceCostController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(VehicleMaintenanceCostRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle-maintenance-cost",
     *   tags={"VehicleMaintenanceCost"},
     *   summary="List VehicleMaintenanceCost",
     *   operationId="vehicle_maintenance_cost_index",
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
    public function index(VehicleMaintenanceCostRequest $request)
    {
        $data = $this->repository->paginate($request->per_page);
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/vehicle-maintenance-cost",
     *   tags={"VehicleMaintenanceCost"},
     *   summary="Add new VehicleMaintenanceCost",
     *   operationId="vehicle_maintenance_cost_create",
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
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(VehicleMaintenanceCostRequest $request)
    {
        try {
            $data = $this->repository->create($request->all());
            return $this->responseJson(200, new VehicleMaintenanceCostResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/vehicle-maintenance-cost/{id}",
     *   tags={"VehicleMaintenanceCost"},
     *   summary="Detail VehicleMaintenanceCost",
     *   operationId="vehicle_maintenance_cost_show",
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
     *   path="/api/sync/vehicle-maintenance-cost",
     *   tags={"VehicleMaintenanceCost"},
     *   summary="Sync VehicleMaintenanceCost",
     *   operationId="sync_vehicle_maintenance_cost",
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *            type="array",
     *            description="Mảng dữ liệu VehicleMaintenanceCost cần sync",
     *            @OA\Items(
     *              type="object",
     *              @OA\Property(property="id", type="integer"),
     *              @OA\Property(property="type", type="string"),
     *              @OA\Property(property="type_text", type="string"),
     *              @OA\Property(property="scheduled_date", type="string", format="date"),
     *              @OA\Property(property="scheduled_date_display", type="string"),
     *              @OA\Property(property="schedule_month", type="integer"),
     *              @OA\Property(property="schedule_year", type="integer"),
     *              @OA\Property(property="maintained_date", type="string", format="date"),
     *              @OA\Property(property="maintained_date_display", type="string"),
     *              @OA\Property(property="vehicle_id", type="integer"),
     *              @OA\Property(property="no_number_plate", type="string"),
     *            ),
     *            example={
     *              {"type": "oil_change", "type_text": "Thay dầu", "vehicle_id": 1, "no_number_plate": "30A-12345"},
     *              {"type": "tire_change", "type_text": "Thay lốp", "vehicle_id": 2, "no_number_plate": "51B-67890"}
     *            }
     *          )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":  "............."}}
     *     ),
     *   )
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncVehicleMaintenanceCost(Request $request)
    {
        $attributes = $request->except([]);
        $data = $this->repository->syncVehicleMaintenanceCost($attributes);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/vehicle-maintenance-cost/{id}",
     *   tags={"VehicleMaintenanceCost"},
     *   summary="Delete VehicleMaintenanceCost",
     *   operationId="vehicle_maintenance_cost_delete",
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
     * @OA\Post(
     *   path="/api/vehicle-maintenance-cost/upload-file",
     *   tags={"VehicleMaintenanceCost"},
     *   summary="upload-file vehicle maintenance cost",
     *   operationId="vehicle-maintenance-cost-upload-file",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="file",
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
    public function uploadFile(FileReceiver $receiver)
    {
        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }
        // receive the file
        $save = $receiver->receive();

        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            // save the file and return any response you need
            return $this->repository->saveFile($save->getFile());
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();
        return response()->json([
            "done" => $handler->getPercentageDone()
        ]);
    }
}
