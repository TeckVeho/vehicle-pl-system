<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-10-07
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataRequest;
use App\Http\Requests\UploadDataRequest;
use App\Imports\StoreExcelImport;
use App\Models\DataConnection;
use App\Repositories\Contracts\UploadDataRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\UploadDataResource;
use App\Repositories\Contracts\DataRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class UploadDataController extends Controller
{

    /**
     * var Repository
     */
    protected $dataItemRepository;
    protected $dataRepository;

    public function __construct(UploadDataRepositoryInterface $dataItemRepository, DataRepositoryInterface $dataRepository)
    {
        $this->dataItemRepository = $dataItemRepository;
        $this->dataRepository = $dataRepository;
    }

    /**
     * @OA\Post(
     *   path="/api/upload",
     *   tags={"UploadData"},
     *   summary="Add new upload_data",
     *   operationId="upload_data_create",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file", "data_connection_id"},
     *              @OA\Property(
     *                   description="data_connection_id",
     *                   property="data_connection_id",
     *                   type="integer",
     *                   format="integer",
     *               ),
     *              @OA\Property(
     *                   description="date",
     *                   property="date",
     *                   type="string",
     *                   format="string",
     *               ),
     *              @OA\Property(
     *                   description="file to upload",
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
    public function store(UploadDataRequest $request)
    {
        try {
            //validate upload file name
            $file = $request->file('file') ? $request->file('file') : $request->get('file');
            $dataConnection = DataConnection::find($request->get('data_connection_id'));
            if ($dataConnection->file_name_map && ($dataConnection->file_name_map !== $file->getClientOriginalName())) {
                throw ValidationException::withMessages(['ファイル名は「' . $dataConnection->file_name_map . '」である必要があります']);
            }
            $data = $this->dataItemRepository->upload($request);
            if ($data == false) {
                throw ValidationException::withMessages(['file' => 'File must be csv']);
            } else {
                return $this->responseJson(200, new UploadDataResource($data));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/download-file",
     *   tags={"DownloadFile"},
     *   summary="Download file",
     *   operationId="download_file",
     *    @OA\Parameter(
     *     name="item_id",
     *     in="query",
     *     description="Data item id",
     *     required=true,
     *     @OA\Schema(
     *       type="integer",
     *       ),
     *     ),
     *   @OA\Parameter(
     *     name="is_history",
     *     in="query",
     *     description="Is history",
     *     @OA\Schema(
     *       type="boolean",
     *       ),
     *     ),
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
     * @return
     */
    public function download(UploadDataRequest $request)
    {
        try {
            $isHistory = $request->get('is_history') === "true" ? true : false;
            $dataItem = $this->dataItemRepository->getDataItemDownload($request->get('item_id'), $isHistory);
            if ($isHistory) {
                return response()->streamDownload(function () use ($dataItem) {
                    print json_encode($dataItem, JSON_UNESCAPED_UNICODE);
                }, "izumi_log.txt");
            } else {
                if ($dataItem->file) {
                    return Storage::disk($dataItem->file->file_sys_disk)
                        ->download($dataItem->file->file_path, $dataItem->file->file_name, ['Content-Type' => 'application/octet-stream; charset=SHIFT-JIS', 'Content-Transfer-Encoding' => 'Binary', 'Charset' => 'SHIFT-JIS']);
                } else {
                    return response()->streamDownload(
                        function () use ($dataItem) {
                            print json_encode($dataItem->content, JSON_UNESCAPED_UNICODE);
                        }, "izumi_content.txt");
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * @OA\Post(
     *   path="/api/receive-data-jinzi-bugyo",
     *   tags={"Receive data"},
     *   summary="Receive data Jinzi Bugyo",
     *   operationId="receive_data_jinzi",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="Zip file upload",
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
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function receiveDataJinziBugyo(UploadDataRequest $request)
    {
        try {
            $data = $this->dataItemRepository->receiveDataJinziBugyo($request);
            return $this->responseJson(200, new UploadDataResource($data));
        } catch (\Exception $e) {
            return $this->responseJsonError(500, $e->getMessage());
        }
    }


    /**
     * @OA\Post(
     *   path="/api/receive-data-mahojin",
     *   tags={"Receive data"},
     *   summary="Receive data Mahojin",
     *   operationId="receive_data_mahojin",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="Csv file upload",
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
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function receiveDataMahojin(UploadDataRequest $request)
    {
        try {
            $data = $this->dataItemRepository->receiveDataMahojin($request);
            return $this->responseJson(200, new UploadDataResource($data));
        } catch (\Exception $e) {
            return $this->responseJsonError(500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/receive-data-kyuyo-bugyo",
     *   tags={"Receive data"},
     *   summary="Receive data Kyuyo Bugyo",
     *   operationId="receive-data-kyuyo-bugyo",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="Zip file upload",
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
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function receiveDataKyuyoBugyo(UploadDataRequest $request)
    {
        try {
            $data = $this->dataItemRepository->receiveDataKyuyoBugyo($request);
            return $this->responseJson(200, new UploadDataResource($data));
        } catch (\Exception $e) {
            return $this->responseJsonError(500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/receive-vehicle-inspection-cert",
     *   tags={"Receive data"},
     *   summary="Receive data Vehicle Inspection Cert",
     *   operationId="receive_vehicle-inspection-cert",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file", "department_id", "car_no", "date_pdf", "date_json", "file_pdf"},
     *              @OA\Property(
     *                   description="file upload",
     *                   property="file",
     *                   type="string",
     *                   format="binary",
     *               ),
     *              @OA\Property(
     *                     description="file pdf upload",
     *                     property="file_pdf",
     *                     type="string",
     *                     format="binary",
     *                 ),
     *               @OA\Property(
     *                      description="car no",
     *                      property="car_no",
     *                      type="string",
     *                      format="string",
     *                ),
     *                @OA\Property(
     *                      description="date pdf",
     *                      property="date_pdf",
     *                      type="string",
     *                      format="string",
     *                ),
     *                @OA\Property(
     *                      description="date json",
     *                      property="date_json",
     *                      type="string",
     *                      format="string",
     *                 ),
     *                  @OA\Property(
     *                       description="department id",
     *                       property="department_id",
     *                       type="integer",
     *                       format="number",
     *                  ),
     *           ),
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
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function receiveVehicleInspectionCert(DataRequest $request)
    {
        $data = $this->dataItemRepository->receiveVIC($request);
        if ($data) {
            if ( isset($data['status']) && $data['status'] == 'fail') {
                return $this->responseJsonError(500, $data['message']);
            }
            return $this->responseJson(200, null, 'success');
        } else {
            return $this->responseJsonError(500, 'error', 'error');
        }
    }

    /**
     * @OA\Post(
     *   path="/api/receive-data-pca",
     *   tags={"Receive data"},
     *   summary="Receive data PCA",
     *   operationId="receive_data_pca",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="file upload",
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
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function receiveDataPCA(UploadDataRequest $request)
    {
        try {
            $data = $this->dataItemRepository->receiveDataPCA($request);
            return $this->responseJson(200, new UploadDataResource($data));
        } catch (\Exception $e) {
            return $this->responseJsonError(500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/receive-import-store",
     *   tags={"Receive data"},
     *   summary="Import store",
     *   operationId="receive_import_store",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="file excel import",
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
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function importStore(UploadDataRequest $request)
    {
        try {
            $data = $this->dataItemRepository->uploadAndImportStore($request);
            return $this->responseJson(200, 'import success');
        } catch (\Exception $e) {
            return $this->responseJsonError(500, $e->getMessage());
        }
    }
}
