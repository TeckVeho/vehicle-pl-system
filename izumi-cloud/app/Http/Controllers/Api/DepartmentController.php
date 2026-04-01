<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-07-19
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Jobs\SyncDepartmentJob;
use App\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Exports\DepartmentExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class DepartmentController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(DepartmentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/department",
     *   tags={"Department"},
     *   summary="List department",
     *   operationId="department_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
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
    public function index(DepartmentRequest $request)
    {
        $data = $this->repository->index();
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/department/change-order",
     *   tags={"Department"},
     *   summary="change order",
     *   operationId="department_change_order",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "List department id",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="list_department", description="list courses", format="object", example="[1,2,3]"),
     *      ),
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
    public function changeOrder(DepartmentRequest $request)
    {
        try {
            $data = $this->repository->changeOrder($request->get('list_department'));
            if ($data) {
                SyncDepartmentJob::dispatch();
            }
            return $this->responseJson(200, new DepartmentResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/department/{id}",
     *   tags={"Department"},
     *   summary="Detail Department",
     *   operationId="department_show",
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
            $department = $this->repository->findById($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/department/{id}",
     *   tags={"Department"},
     *   summary="Update Department",
     *   operationId="department_update",
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
     *          @OA\Schema(
     *            @OA\Property(property="address",format="string",),
     *            @OA\Property(property="interview_address",format="string",),
     *            @OA\Property(property="interview_address_url",format="string",),
     *            @OA\Property(property="interview_pic",format="integer",),
     *            @OA\Property(property="post_code",format="integer",),
     *            @OA\Property(property="tel",format="integer",),
     *            @OA\Property(property="office_name",format="string",),
     *            @OA\Property(property="office_location",format="string",),
     *            @OA\Property(property="office_area",format="string",),
     *            @OA\Property(property="rest_room_area",format="string",),
     *            @OA\Property(property="garage_location_1",format="string",),
     *            @OA\Property(property="garage_area_1",format="string",),
     *            @OA\Property(property="garage_location_2",format="string",),
     *            @OA\Property(property="garage_area_2",format="string",),
     *            @OA\Property(property="operations_manager_appointment",format="array",example="[1,2,3]",),
     *            @OA\Property(property="operations_manager_assistant",format="array", example="[1,2,3]",),
     *            @OA\Property(property="chief_operations_manager",format="string",),
     *            @OA\Property(property="maintenance_manager_appointment",format="array",example="[1,2,3]",),
     *            @OA\Property(property="maintenance_manager_assistant",format="array", example="[1,2,3]"),
     *            @OA\Property(property="g_mark_action_radio",format="array", example="[1,2]",),
     *            @OA\Property(property="maintenance_manager_fax_number",format="string",),
     *            @OA\Property(property="truck_association_membership_number",format="string",),
     *            @OA\Property(property="g_mark_number",format="string",),
     *            @OA\Property(property="g_mark_expiration_date",format="string",)
     *          )
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
    public function update(DepartmentRequest $request, $id)
    {
        $attributes = $request->only(
            [
                'address',
                'interview_address',
                'interview_address_url',
                'path_for_interview_address',
                'interview_pic',
                'interview_pic_line_work',
                'post_code',
                'tel',
                'office_name',
                'office_location',
                'office_area',
                'rest_room_area',
                'garage_location_1',
                'garage_area_1',
                'garage_location_2',
                'garage_area_2',
                'operations_manager_appointment',
                'operations_manager_assistant',
                'maintenance_manager_appointment',
                'maintenance_manager_assistant',
                //'maintenance_manager_phone_number',
                'maintenance_manager_fax_number',
                'truck_association_membership_number',
                'g_mark_number',
                'g_mark_expiration_date',
                'it_roll_call',
                'g_mark_action_radio',
                'age_appropriate_interview',
                'chief_operations_manager'
            ]
        );
        $data = $this->repository->updateDepartment($attributes, $id);
        if ($data) {
            SyncDepartmentJob::dispatch();
        }
        return $this->responseJson(200, new BaseResource($data));
    }

//    /**
//     * @OA\Delete(
//     *   path="/api/department/{id}",
//     *   tags={"Department"},
//     *   summary="Delete Department",
//     *   operationId="department_delete",
//     *   @OA\Parameter(
//     *      name="id",
//     *      in="path",
//     *      required=true,
//     *     @OA\Schema(
//     *      type="string",
//     *     ),
//     *   ),
//     *   @OA\Response(
//     *     response=200,
//     *     description="Send request success",
//     *     @OA\MediaType(
//     *      mediaType="application/json",
//     *      example={"code":200,"data":"Send request success"}
//     *     )
//     *   ),
//     *   security={{"auth": {}}},
//     * )
//     * @param int $id
//     * @return \Illuminate\Http\JsonResponse
//     * @throws \Exception
//     */
//    public function destroy($id)
//    {
//        $this->repository->delete($id);
//        return $this->responseJson(200, null, trans('messages.mes.delete_success'));
//    }


    /**
     * @OA\Get(
     *   path="/api/department/list-all",
     *   tags={"Department"},
     *   summary="List Department",
     *   operationId="department_index_all",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{ "1": "本社","2": "横浜第一",}},
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
        $datas = Department::select('name as department_name', 'id')->orderBy('position', 'ASC')->get();
        return $this->responseJson(200, new BaseResource($datas));
    }

    /**
     * @OA\Get(
     *   path="/api/department/export",
     *   tags={"Department"},
     *   summary="Export Department CSV",
     *   operationId="department_export_csv",
     *   @OA\Response(
     *     response=200,
     *     description="Export CSV success",
     *     @OA\MediaType(
     *      mediaType="application/octet-stream",
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
     * Export Department data to CSV file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportCsv()
    {
        $data = $this->repository->exportCsv();
        $fileName = '拠点マスタ.csv';
        return Excel::download(new DepartmentExport($data), $fileName, null, [
            'Content-Type' => 'application/octet-stream; charset=SJIS-win',
            'Content-Transfer-Encoding' => 'Binary',
            'Charset' => 'SJIS-win'
        ]);
    }
}
