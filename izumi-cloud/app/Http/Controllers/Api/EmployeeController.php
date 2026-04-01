<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-08-24
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\EmployeeResource;
use App\Exports\EmployeesExport;
use App\Imports\EmployeeDetailImport;
use Helper\Common;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(EmployeeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/employee",
     *   tags={"Employee"},
     *   summary="List employee",
     *   operationId="employee_index",
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
     *   @OA\Parameter(
     *     name="month",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="employee_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="employee_name",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_base_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="working_base_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="sort_by",
     *     in="query",
     *     description="sort_by [department_base, working_base, employee_id, employee_name, retirement_date]",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="sort_type",
     *     in="query",
     *     @OA\Schema(
     *      type="boolean",
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
    public function index(EmployeeRequest $request)
    {
        $data = $this->repository->index($request);
        return $this->responseJson(200, EmployeeResource::collection($data));
    }

//    /**
//     * @OA\Post(
//     *   path="/api/employee",
//     *   tags={"Employee"},
//     *   summary="Add new employee",
//     *   operationId="employee_create",
//     *   @OA\Parameter(name="name", in="query", required=true,
//     *     @OA\Schema(type="string"),
//     *   ),
//     *
//     *   @OA\Response(
//     *     response=200,
//     *     description="Send request success",
//     *     @OA\MediaType(
//     *      mediaType="application/json",
//     *      example={"code":200,"data":{"id": 1,"name": "......"}}
//     *     )
//     *   ),
//     *   security={{"auth": {}}},
//     * )
//     * @return \Illuminate\Http\JsonResponse
//     * @throws \Exception
//     */
//    public function store(EmployeeRequest $request)
//    {
//        try {
//            $data = $this->repository->create($request->all());
//            return $this->responseJson(200, new EmployeeResource($data));
//        } catch (\Exception $e) {
//            throw $e;
//        }
//    }

    /**
     * @OA\Get(
     *   path="/api/employee/{id}",
     *   tags={"Employee"},
     *   summary="Detail Employee",
     *   operationId="employee_show",
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
            $data = $this->repository->detail($id, null);
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * @OA\Get(
     *   path="/api/employee/dp-working",
     *   tags={"Employee"},
     *   summary="Detail employee department working",
     *   operationId="employee_show_dp_working",
     *   @OA\Parameter(
     *     name="employee_id",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_working_id",
     *     in="query",
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
    public function departmentWorking(EmployeeRequest $request)
    {
        $isSupport = $request->boolean('is_support');
        try {
            $department = $this->repository->departmentWorking($request->get('employee_id'), $request->get('department_working_id'));
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/employee/{id}",
     *   tags={"Employee"},
     *   summary="Update Employee working department",
     *   operationId="employee_update",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Data employee",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(
     *              property="working_date",
     *              type="array",
     *              example={{"start_date": "2022-05-01","end_date": "2022-05-30","is_support":"1",},
     *                      {"start_date": "2022-06-01","end_date": "2022-06-30","is_support":"1",},
     *                      {"start_date": "2022-07-01","end_date": "","is_support":"0",}},
     *              @OA\Items(
     *                    @OA\Property(property="start",type="string",),
     *                    @OA\Property(property="end",type="string",),
     *                    @OA\Property(property="is_support",type="integer",),
     *              ),
     *           ),
     *           @OA\Property(property="department_working_id", type="integer", example="1",),
     *           @OA\Property(property="grade", type="integer", example="1",),
     *           @OA\Property(property="employee_grade_2", type="integer", example="2",),
     *           @OA\Property(property="boarding_employee_grade", type="integer" , example="3",),
     *           @OA\Property(property="boarding_employee_grade_2", type="integer", example="4",),
     *           @OA\Property(property="transportation_compensation", type="integer", example="5",),
     *           @OA\Property(property="daily_transportation_cp", type="integer", example="6",),
     *           @OA\Property(property="midnight_worktime_hour", type="integer", example="7",),
     *           @OA\Property(property="midnight_worktime_minutes", type="integer", example="8",),
     *           @OA\Property(property="scheduled_labor_hour", type="integer", example="9",),
     *           @OA\Property(property="scheduled_labor_minutes", type="integer", example="10",),
     *           @OA\Property(property="employee_courses", description="list courses", format="object", example="[1,2,3]"),
     *           @OA\Property(property="beginner_driver_training_classroom", type="integer", example="0",),
     *           @OA\Property(property="beginner_driver_training_practical", type="integer", example="0",),
     *      ),
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
    public function update(EmployeeRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->updateEmployeeDpWorking($id, $attributes);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Put(
     *   path="/api/employee/contents/{id}",
     *   tags={"Employee"},
     *   summary="Add the content on Employee",
     *   operationId="employee_add_content",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add content",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="company_car", type="string", example="company01card",),
     *           @OA\Property(property="etc_card", type="string", example="etc02card",),
     *           @OA\Property(property="fuel_card", type="string", example="fuel03card",),
     *           @OA\Property(property="other", type="string" , example="other",),
     *      ),
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
    public function contentEmployee(EmployeeRequest $request, $id)
    {
        $attributes = array_merge($request->except([]));
        $data = $this->repository->addContentEmployee($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

//    /**
//     * @OA\Delete(
//     *   path="/api/employee/{id}",
//     *   tags={"Employee"},
//     *   summary="Delete Employee",
//     *   operationId="employee_delete",
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
     *   path="/api/employee/dp-working/list-course/{department_working_id}",
     *   tags={"Employee"},
     *   summary="dp working list route",
     *   operationId="employee_dp_wk_routes",
     *   @OA\Parameter(
     *     name="department_working_id",
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
    public function listCourse($department_working_id)
    {
        try {
            $data = $this->repository->listCourseByDepartment($department_working_id);
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *   path="/api/employee/driver-license",
     *   tags={"Employee"},
     *   summary="Add driver license",
     *   operationId="employee_add_driver_license",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add driver license",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="surface_file_id", type="integer", example="1",),
     *           @OA\Property(property="back_file_id", type="integer", example="1",),
     *           @OA\Property(property="employee_id", type="integer", example="1",),
     *      ),
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
    public function addDriverLicense(EmployeeRequest $request)
    {
        $data = $this->repository->addDriverLicense($request->all());
        if ($data) {
            return $this->responseJson(200, new BaseResource($data));
        } else {
            return $this->responseJson(400, null, 'Failed to add driver license');
        }
    }

    /**
     * @OA\Post(
     *   path="/api/employee/upload-file",
     *   tags={"Employee"},
     *   summary="upload-file employee",
     *   operationId="employee-upload-file",
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

    /**
     * @OA\Post(
     *   path="/api/employee/driving-record-certificate",
     *   tags={"Employee"},
     *   summary="Add driving record certificate",
     *   operationId="employee_add_driving_record_certificate",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add driving record certificate",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="file_id", type="integer", example="1",),
     *           @OA\Property(property="employee_id", type="integer", example="1",),
     *      ),
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
     *   @OA\Response(
     *     response=400,
     *     description="Failed to add driving record certificate",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":400,"message":"Failed to add driving record certificate"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDrivingRecordCertificate(EmployeeRequest $request)
    {
        $data = $this->repository->addDrivingRecordCertificate($request->all());
        if ($data) {
            return $this->responseJson(200, new BaseResource($data));
        } else {
            return $this->responseJson(400, null, 'Failed to add driving record certificate');
        }
    }

    /**
     * @OA\Post(
     *   path="/api/employee/aptitude-assessment-form",
     *   tags={"Employee"},
     *   summary="Add aptitude assessment form",
     *   operationId="employee_add_aptitude_assessment_form",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add aptitude assessment form",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="file_id", type="integer", example="1",),
     *           @OA\Property(property="employee_id", type="integer", example="1",),
     *           @OA\Property(property="type", type="integer", example="1",),
     *           @OA\Property(property="date_of_visit", type="date", example="2025-01-01",),
     *      ),
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
     *   @OA\Response(
     *     response=400,
     *     description="Failed to add aptitude assessment form",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":400,"message":"Failed to add aptitude assessment form"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAptitudeAssessmentForm(EmployeeRequest $request)
    {
        $data = $this->repository->addAptitudeAssessmentForm($request->all());
        if ($data) {
            return $this->responseJson(200, new BaseResource($data));
        } else {
            return $this->responseJson(400, null, 'Failed to add aptitude assessment form');
        }
    }

    /**
     * @OA\Post(
     *   path="/api/employee/health-examination-results",
     *   tags={"Employee"},
     *   summary="Add health examination results",
     *   operationId="employee_add_health_examination_results",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add health examination results",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="file_id", type="integer", example="1",),
     *           @OA\Property(property="employee_id", type="integer", example="1",),
     *           @OA\Property(property="date_of_visit", type="date", example="2025-01-01",),
     *      ),
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
     *   @OA\Response(
     *     response=400,
     *     description="Failed to add health examination results",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":400,"message":"Failed to add health examination results"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addHealthExaminationResults(EmployeeRequest $request)
    {
        $data = $this->repository->addHealthExaminationResults($request->all());
        if ($data) {
            return $this->responseJson(200, new BaseResource($data));
        } else {
            return $this->responseJson(400, null, 'Failed to add health examination results');
        }
    }
    /**
     * @OA\Get(
     *   path="/api/employee/export-all",
     *   tags={"Employee"},
     *   summary="Export all employees",
     *   operationId="employee_export_all",
     *   @OA\Parameter(
     *     name="month",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="employee_id",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="employee_name",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_base_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="working_base_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
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
     * Export all employees detail.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportAll(EmployeeRequest $request)
    {
        try {
            $month = $request ? $request->get('month') : null;
            $data = $this->repository->getAllForExport($request);
            $fileName = '従業員マスタ情報_' . $month . '.csv';
            return Excel::download(new EmployeesExport($data), $fileName, null, ['Content-Type' => 'application/octet-stream; charset=SJIS-win', 'Content-Transfer-Encoding' => 'Binary', 'Charset' => 'SJIS-win']);
        } catch (\Exception $e) {
            return $this->responseJson(500, null, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/employee/import-detail",
     *   tags={"Employee"},
     *   summary="Import employee detail from CSV",
     *   operationId="employee_import_detail",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="CSV file to import",
     *                   property="file",
     *                   type="string",
     *                   format="binary",
     *               ),
     *           )
     *       )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Import success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"message":"Import success"}
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
     * Import employee detail from CSV file.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function importDetail(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:csv,txt|max:10240'
            ]);

            $file = $request->file('file');
            Excel::import(new EmployeeDetailImport(), $file);
            
            return $this->responseJson(Response::HTTP_OK, null, trans('messages.mes.import_success'));
        } catch (\Exception $e) {
            return $this->responseJson(500, null, $e->getMessage());
        }
    }
  
    /**
     * @OA\Get(
     *   path="/api/employee/all",
     *   tags={"Employee"},
     *   summary="All employee",
     *   operationId="employee_all",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $data = $this->repository->getAll();
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Post(
     *   path="/api/employee/upload-employee-pdf",
     *   tags={"Employee"},
     *   summary="Upload employee pdf",
     *   operationId="employee_upload_employee_pdf",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Upload employee pdf",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="file_id", type="integer", example="1",),
     *           @OA\Property(property="employee_id", type="integer", example="1",),
     *      ),
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
     *   @OA\Response(
     *     response=400,
     *     description="Failed to upload employee pdf",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":400,"message":"Failed to upload employee pdf"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadEmployeePdf(EmployeeRequest $request)
    {
        $data = $this->repository->uploadEmployeePdf($request->all());
        if ($data) {
            return $this->responseJson(200, new BaseResource($data));
        } else {
            return $this->responseJson(400, null, 'Failed to upload employee pdf');
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/employee/delete-employee-pdf/{id}",
     *   tags={"Employee"},
     *   summary="Delete employee pdf",
     *   operationId="employee_delete_employee_pdf",
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
     *   @OA\Response(
     *     response=400,
     *     description="Failed to delete employee pdf",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":400,"message":"Failed to delete employee pdf"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEmployeePdf($id)
    {
        $data = $this->repository->deleteEmployeePdf($id);
        if ($data) {
            return $this->responseJson(200, new BaseResource($data));
        } else {
            return $this->responseJson(400, null, 'Failed to delete employee pdf');
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/employee/delete-health-examination-file-history/{id}",
     *   tags={"Employee"},
     *   summary="Delete health examination file history",
     *   operationId="employee_delete_health_examination_file_history",
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
     *   @OA\Response(
     *     response=400,
     *     description="Failed to delete health examination file history",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":400,"message":"Failed to delete health examination file history"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteHealthExaminationFileHistory($id)
    {
        $data = $this->repository->deleteHealthExaminationFileHistory($id);
        if ($data) {
            return $this->responseJson(200, null, 'Delete health examination file history success');
        } else {
            return $this->responseJson(400, null, 'Failed to delete health examination file history');
        }
    }
  
     /**
     * @OA\Delete(
     *   path="/api/employee/delete-aptitude-assessment-form/{id}",
     *   tags={"Employee"},
     *   summary="Delete aptitude assessment form",
     *   operationId="employee_delete_aptitude_assessment_form",
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
     *   @OA\Response(
     *     response=400,
     *     description="Failed to delete aptitude assessment form",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":400,"message":"Failed to delete aptitude assessment form"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAptitudeAssessmentForm($id)
    {
        $data = $this->repository->deleteAptitudeAssessmentForm($id);
        if ($data) {
            return $this->responseJson(200, null, 'Delete aptitude assessment form success');
        } else {
            return $this->responseJson(400, null, 'Failed to delete aptitude assessment form');
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/employee/delete-driving-record-certificate/{id}",
     *   tags={"Employee"},
     *   summary="Delete driving record certificate",
     *   operationId="employee_delete_driving_record_certificate",
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
     *   @OA\Response(
     *     response=400,
     *     description="Failed to delete driving record certificate",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":400,"message":"Failed to delete driving record certificate"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEmployeeDrivingRecordCertificates($id)
    {
        $data = $this->repository->deleteEmployeeDrivingRecordCertificates($id);
        if ($data) {
            return $this->responseJson(200, null, 'Delete driving record certificate success');
        } else {
            return $this->responseJson(400, null, 'Failed to delete driving record certificate');
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/employee/delete-driver-license/{id}",
     *   tags={"Employee"},
     *   summary="Delete driver license",
     *   operationId="employee_delete_driver_license",
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
     *   @OA\Response(
     *     response=400,
     *     description="Failed to delete driver license",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":400,"message":"Failed to delete driver license"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDriverLicense($id)
    {
        $data = $this->repository->deleteDriverLicense($id);
        if ($data) {
            return $this->responseJson(200, null, 'Delete driver license success');
        } else {
            return $this->responseJson(400, null, 'Failed to delete driver license');
        }
    }

    /**
     * @OA\Get(
     *   path="/api/employee/get-employee-by-department-id/{department_id}",
     *   tags={"Employee"},
     *   summary="Get employee by department id",
     *   operationId="employee_get_employee_by_department_id",
     *   security={{"auth": {}}},
     *   @OA\Parameter(
     *     name="department_id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(
     *         type="object"
     *       )
     *     )
     *   )
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeeByDepartmentId($department_id)
    {
        $data = $this->repository->getEmployeeByDepartmentId($department_id);
        return $this->responseJson(200, new BaseResource($data));
    }
}
