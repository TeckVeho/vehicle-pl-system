<?php
/**
 * Created by VeHo.
 * Year: 2026-03-16
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePdfStorageRequest;
use App\Repositories\Contracts\EmployeePdfStorageRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\EmployeePdfStorageResource;
use Illuminate\Http\Request;

class EmployeePdfStorageController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(EmployeePdfStorageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/employee-pdf-storage",
     *   tags={"EmployeePdfStorage"},
     *   summary="List EmployeePdfStorage",
     *   operationId="employee_pdf_storage_index",
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
     *     name="sort_by",
     *     in="query",
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
     *   @OA\Parameter(
     *     name="user_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department_id",
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
    public function index(Request $request)
    {
        $data = $this->repository->listAll($request);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Post(
     *   path="/api/employee-pdf-storage",
     *   tags={"EmployeePdfStorage"},
     *   summary="Add new EmployeePdfStorage",
     *   operationId="employee_pdf_storage_create",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add new EmployeePdfStorage",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="file_id", type="integer", example="1",),
     *           @OA\Property(property="department_id", type="integer", example="1",),
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
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(EmployeePdfStorageRequest $request)
    {
        try {
            $data = $this->repository->createNewEmployeePdfStorage($request->all());
            return $this->responseJson(200, new EmployeePdfStorageResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/employee-pdf-storage/{id}",
     *   tags={"EmployeePdfStorage"},
     *   summary="Detail EmployeePdfStorage",
     *   operationId="employee_pdf_storage_show",
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
            $department = $this->repository->getById($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *   path="/api/employee-pdf-storage/{id}",
     *   tags={"EmployeePdfStorage"},
     *   summary="Update EmployeePdfStorage",
     *   operationId="employee_pdf_storage_update",
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
     *          example={"name":"string"},
     *          @OA\Schema(
     *            required={"name"},
     *            @OA\Property(
     *              property="name",
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
    public function update(EmployeePdfStorageRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->update($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/employee-pdf-storage/{id}",
     *   tags={"EmployeePdfStorage"},
     *   summary="Delete EmployeePdfStorage",
     *   operationId="employee_pdf_storage_delete",
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
     *   path="/api/employee-pdf-storage/driver-license",
     *   tags={"EmployeePdfStorage"},
     *   summary="Add driver license",
     *   operationId="employee_pdf_storage_add_driver_license",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add driver license",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="employee_pdf_storage_id", type="integer", example="1",),
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
    public function addDriverLicense(EmployeePdfStorageRequest $request)
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
     *   path="/api/employee-pdf-storage/driving-record-certificate",
     *   tags={"EmployeePdfStorage"},
     *   summary="Add driving record certificate",
     *   operationId="employee_pdf_storage_add_driving_record_certificate",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add driving record certificate",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="employee_pdf_storage_id", type="integer", example="1",),
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
    public function addDrivingRecordCertificate(EmployeePdfStorageRequest $request)
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
     *   path="/api/employee-pdf-storage/aptitude-assessment-form",
     *   tags={"EmployeePdfStorage"},
     *   summary="Add aptitude assessment form",
     *   operationId="employee_pdf_storage_add_aptitude_assessment_form",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add aptitude assessment form",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="employee_pdf_storage_id", type="integer", example="1",),
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
    public function addAptitudeAssessmentForm(EmployeePdfStorageRequest $request)
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
     *   path="/api/employee-pdf-storage/health-examination-results",
     *   tags={"EmployeePdfStorage"},
     *   summary="Add health examination results",
     *   operationId="employee_pdf_storage_add_health_examination_results",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "Add health examination results",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="employee_pdf_storage_id", type="integer", example="1",),
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
    public function addHealthExaminationResults(EmployeePdfStorageRequest $request)
    {
        $data = $this->repository->addHealthExaminationResults($request->all());
        if ($data) {
            return $this->responseJson(200, new BaseResource($data));
        } else {
            return $this->responseJson(400, null, 'Failed to add health examination results');
        }
    }
}
