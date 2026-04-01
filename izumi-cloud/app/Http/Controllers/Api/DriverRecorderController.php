<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-11-10
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverRecorderRequest;
use App\Http\Resources\DriverPlayListResource;
use App\Repositories\Contracts\DriverRecorderRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\DriverRecorderResource;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class DriverRecorderController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(DriverRecorderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/driver-recorder",
     *   tags={"DriverRecorder"},
     *   summary="List driver_recorder",
     *   operationId="driver_recorder_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *      "result": {
     *          {
     *              "id": 1,
     *              "accident_date": "2022-11-21",
     *              "title": "AVC",
     *              "type": 1,
     *              "department_id": 1,
     *              "department_name": "本社",
     *              "remark": "rm",
     *              "type_one": "1",
     *              "type_two": 1,
     *              "shipper": 1,
     *              "accident_classification": 1,
     *              "place_of_occurrence": 1,
     *              "created_at": "2022-11-20T15:00:00.000000Z",
     *              "updated_at": "2022-11-20T15:00:00.000000Z",
     *              "action_camera": {
     *                  {
     *                      {
     *                          "id": 1,
     *                          "file_name": "37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_size": "10674",
     *                          "file_url": null,
     *                          "group_position": 1
     *                      },
     *                      {
     *                          "id": 1,
     *                          "file_name": "37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_size": "10674",
     *                          "file_url": null,
     *                          "group_position": 1
     *                      },
     *                      {
     *                          "id": 1,
     *                          "file_name": "37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_size": "10674",
     *                          "file_url": null,
     *                          "group_position": 1
     *                      }
     *                  },
     *                  {
     *                      {
     *                          "id": 2,
     *                          "file_name": "9d123320cba101d3e557dd05f50a31d0_20211216_42_shokusyu.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/9d123320cba101d3e557dd05f50a31d0_20211216_42_shokusyu.csv",
     *                          "file_size": "161",
     *                          "file_url": null,
     *                          "group_position": 2
     *                      },
     *                      {
     *                          "id": 3,
     *                          "file_name": "094be5ba54d50f8be58df213d8e80fa5_20211216_43_shikaku-tokyu.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/094be5ba54d50f8be58df213d8e80fa5_20211216_43_shikaku-tokyu.csv",
     *                          "file_size": "159",
     *                          "file_url": null,
     *                          "group_position": 2
     *                      },
     *                      {
     *                          "id": 3,
     *                          "file_name": "094be5ba54d50f8be58df213d8e80fa5_20211216_43_shikaku-tokyu.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/094be5ba54d50f8be58df213d8e80fa5_20211216_43_shikaku-tokyu.csv",
     *                          "file_size": "159",
     *                          "file_url": null,
     *                          "group_position": 2
     *                      }
     *                  }
     *              }
     *          }
     *      },
     *      "pagination": {
     *          "display": 1,
     *          "total_records": 1,
     *          "per_page": 15,
     *          "current_page": 1,
     *          "total_pages": 1
     *      }
     *  }
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="type_one",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="type_two",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="shipper",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="accident_classification",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="place_of_occurrence",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
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
    public function index(DriverRecorderRequest $request)
    {
        $params = $request->all();
        $data = $this->repository->paginate($request->per_page, [
            "month" => Arr::get($params, 'month', null),
            "department_id" => Arr::get($params, 'department_id', null),
            "type" => Arr::get($params, 'type', null),
            "title" => Arr::get($params, 'title', null),
            "record_date" => Arr::get($params, 'record_date', null),
            "type_one" => Arr::get($params, 'type_one', null),
            "type_two" => Arr::get($params, 'type_two', null),
            "shipper" => Arr::get($params, 'shipper', null),
            "accident_classification" => Arr::get($params, 'accident_classification', null),
            "place_of_occurrence" => Arr::get($params, 'place_of_occurrence', null),
            "sort_by" => Arr::get($params, 'sort_by', null),
            "sort_type" => Arr::get($params, 'sort_type', null),
            "sort_by_record_date" => Arr::get($params, 'sort_by', 'asc'),
        ]);
        return DriverRecorderResource::collection($data);
    }

    public function indexMobile(DriverRecorderRequest $request)
    {
        $params = $request->all();
        $per_page = Arr::get($params, 'per_page', 100);
        $data = $this->repository
            ->paginate($per_page, [
            "month" => Arr::get($params, 'month', null),
            "department_id" => Arr::get($params, 'department_id', null),
            "type" => Arr::get($params, 'type', null),
            "title" => Arr::get($params, 'title', null),
            "record_date" => Arr::get($params, 'record_date', null),
            "type_one" => Arr::get($params, 'type_one', null),
            "type_two" => Arr::get($params, 'type_two', null),
            "shipper" => Arr::get($params, 'shipper', null),
            "accident_classification" => Arr::get($params, 'accident_classification', null),
            "place_of_occurrence" => Arr::get($params, 'place_of_occurrence', null),
            "sort_by" => Arr::get($params, 'sort_by', null),
            "sort_type" => Arr::get($params, 'sort_type', null),
        ]);
        return DriverRecorderResource::collection($data);
    }

    /**
     * @OA\Get(
     *   path="/api/driver-recorder-viewer",
     *   tags={"DriverRecorder"},
     *   summary="List driver_recorder viewer",
     *   operationId="driver_recorder_index_viewer",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *      "result": {
     *          {
     *              "id": 1,
     *              "accident_date": "2022-11-21",
     *              "title": "AVC",
     *              "type": 1,
     *              "department_id": 1,
     *              "department_name": "本社",
     *              "remark": "rm",
     *              "created_at": "2022-11-20T15:00:00.000000Z",
     *              "updated_at": "2022-11-20T15:00:00.000000Z",
     *              "action_camera": {
     *                  {
     *                      {
     *                          "id": 1,
     *                          "file_name": "37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_size": "10674",
     *                          "file_url": null,
     *                          "group_position": 1
     *                      },
     *                      {
     *                          "id": 1,
     *                          "file_name": "37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_size": "10674",
     *                          "file_url": null,
     *                          "group_position": 1
     *                      },
     *                      {
     *                          "id": 1,
     *                          "file_name": "37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/37744679fd54ba5a6d89db8db12a4358_20211216_41_roumu-keiyaku.csv",
     *                          "file_size": "10674",
     *                          "file_url": null,
     *                          "group_position": 1
     *                      }
     *                  },
     *                  {
     *                      {
     *                          "id": 2,
     *                          "file_name": "9d123320cba101d3e557dd05f50a31d0_20211216_42_shokusyu.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/9d123320cba101d3e557dd05f50a31d0_20211216_42_shokusyu.csv",
     *                          "file_size": "161",
     *                          "file_url": null,
     *                          "group_position": 2
     *                      },
     *                      {
     *                          "id": 3,
     *                          "file_name": "094be5ba54d50f8be58df213d8e80fa5_20211216_43_shikaku-tokyu.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/094be5ba54d50f8be58df213d8e80fa5_20211216_43_shikaku-tokyu.csv",
     *                          "file_size": "159",
     *                          "file_url": null,
     *                          "group_position": 2
     *                      },
     *                      {
     *                          "id": 3,
     *                          "file_name": "094be5ba54d50f8be58df213d8e80fa5_20211216_43_shikaku-tokyu.csv",
     *                          "file_extension": "csv",
     *                          "file_path": "data_item/20211216/094be5ba54d50f8be58df213d8e80fa5_20211216_43_shikaku-tokyu.csv",
     *                          "file_size": "159",
     *                          "file_url": null,
     *                          "group_position": 2
     *                      }
     *                  }
     *              }
     *          }
     *      },
     *      "pagination": {
     *          "display": 1,
     *          "total_records": 1,
     *          "per_page": 15,
     *          "current_page": 1,
     *          "total_pages": 1
     *      }
     *  }
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
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexViewer(DriverRecorderRequest $request)
    {
        $params = $request->all();
        $data = $this->repository->paginate($request->per_page, [
            "month" => Arr::get($params, 'month', null),
            "department_id" => Arr::get($params, 'department_id', null),
            "type" => Arr::get($params, 'type', null),
            "title" => Arr::get($params, 'title', null),
            "record_date" => Arr::get($params, 'record_date', null),
            "type_one" => Arr::get($params, 'type_one', null),
            "type_two" => Arr::get($params, 'type_two', null),
            "shipper" => Arr::get($params, 'shipper', null),
            "accident_classification" => Arr::get($params, 'accident_classification', null),
            "place_of_occurrence" => Arr::get($params, 'place_of_occurrence', null),
            "sort_by" => Arr::get($params, 'sort_by', null),
            "sort_type" => Arr::get($params, 'sort_type', null),
        ]);
        return DriverRecorderResource::collection($data);
    }

    /**
     * @OA\Post(
     *   path="/api/driver-recorder",
     *   tags={"DriverRecorder"},
     *   summary="Add new driver_recorder",
     *   operationId="driver_recorder_create",
     *      @OA\RequestBody(
     *          description="Input data",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                 type="object",
     *                   @OA\Property(property="record_date", description="record_date", format="string", example="2022-01-01"),
     *                   @OA\Property(property="title", description="title", format="string", example="title driver recorder"),
     *                   @OA\Property(property="department_id", description="department_id", format="integer", example="1"),
     *                   @OA\Property(property="type", description="type", format="integer", example="0"),
     *                   @OA\Property(property="remark", description="remark", format="string", example="remark note"),
     *                   @OA\Property(property="type_one", description="type_one", format="integer", example="1"),
     *                   @OA\Property(property="type_two", description="type_two", format="integer", example="1"),
     *                   @OA\Property(property="shipper", description="shipper", format="integer", example="1"),
     *                   @OA\Property(property="accident_classification", description="accident_classification", format="integer", example="1"),
     *                   @OA\Property(property="place_of_occurrence", description="place_of_occurrence", format="integer", example="1"),
     *                   @OA\Property(property="list_recorder",
     *                          description="list_recorder",
     *                          type="array",
     *                          @OA\Items(@OA\Property(property="movie_title",type="string",),
     *                                  @OA\Property(property="list_movie",
     *                                             type="object",
     *                                             @OA\Property(property="front",type="integer",),
     *                                                      @OA\Property(property="inside",type="integer",),
     *                                                      @OA\Property(property="behind",type="integer",),
     *
     *                                  ),
     *                          ),
     *                    ),
     *              ),
     *          ),
     *      ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code": 200,"data": {},},
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(DriverRecorderRequest $request)
    {
        try {
            $data = $this->repository->storeDriverRecorder(
              $request->only('department_id', 'record_date', 'title', 'type',
              'remark', 'excel_file_id', 'type_one', 'type_two', 'shipper', 'accident_classification',
                  'place_of_occurrence', 'is_draft', 'flag_send_noti', 'crew_member_id'),
              $request->get('list_recorder'),
              $request->get('recorder_images'),
            );
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * @OA\Post(
     *   path="/api/driver-recorder/add-or-update-play-list/{id}",
     *   tags={"DriverRecorder"},
     *   summary="add-or-update-play-list",
     *   operationId="driver-play-list_add-or-update",
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *      @OA\RequestBody(
     *          description="Input data",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="list_play_list",description="list_play_list", example="[1,2,3,4,5]"),
     *             ),
     *          ),
     *      ),
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
    public function addOrUpdatePlayList(DriverRecorderRequest $request, $id)
    {
        try {
            $data = $this->repository->addOrUpdatePlayList($request->get('list_play_list'), $id);
            return $this->responseJson(200, new DriverPlayListResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/driver-recorder/{id}",
     *   tags={"DriverRecorder"},
     *   summary="Update driver_recorder",
     *   operationId="driver_recorder_update",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *      @OA\RequestBody(
     *          description="Input data",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                 type="object",
     *                   @OA\Property(property="record_date", description="record_date", format="string", example="2022-01-01"),
     *                   @OA\Property(property="title", description="title", format="string", example="title driver recorder"),
     *                   @OA\Property(property="department_id", description="department_id", format="integer", example="1"),
     *                   @OA\Property(property="type", description="type", format="integer", example="0"),
     *                   @OA\Property(property="remark", description="remark", format="string", example="remark note"),
     *                   @OA\Property(property="type_one", description="type_one", format="integer", example="1"),
     *                   @OA\Property(property="type_two", description="type_two", format="integer", example="1"),
     *                   @OA\Property(property="shipper", description="shipper", format="integer", example="1"),
     *                   @OA\Property(property="accident_classification", description="accident_classification", format="integer", example="1"),
     *                   @OA\Property(property="place_of_occurrence", description="place_of_occurrence", format="integer", example="1"),
     *                   @OA\Property(property="list_recorder",
     *                          description="list_recorder",
     *                          type="array",
     *                          @OA\Items(@OA\Property(property="movie_title",type="string",),
     *                                  @OA\Property(property="list_movie",
     *                                             type="object",
     *                                             @OA\Property(property="front",type="integer",),
     *                                                      @OA\Property(property="inside",type="integer",),
     *                                                      @OA\Property(property="behind",type="integer",),
     *
     *                                  ),
     *                          ),
     *                    ),
     *              ),
     *          ),
     *      ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code": 200,"data": {},},
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(DriverRecorderRequest $request, $id)
    {
        try {
            $data = $this->repository->updateDriverRecorder(
                $request->only('department_id', 'record_date', 'title', 'type', 'remark', 'excel_file_id',
                    'type_one', 'type_two', 'shipper', 'accident_classification', 'place_of_occurrence', 'is_draft',
                    'flag_send_noti', 'crew_member_id'),
                $request->get('list_recorder'),
                $request->get('recorder_images'),
                $id
            );
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/driver-recorder/{id}",
     *   tags={"DriverRecorder"},
     *   summary="Detail DriverRecorder",
     *   operationId="driver_recorder_show",
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
            $department = $this->repository->detail($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/driver-recorder-viewer/{id}",
     *   tags={"DriverRecorder"},
     *   summary="Detail DriverRecorder viewer",
     *   operationId="driver_recorder_show_viewer",
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
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showViewer($id)
    {
        try {
            $department = $this->repository->detail($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/driver-recorder/{id}",
     *   tags={"DriverRecorder"},
     *   summary="Delete DriverRecorder",
     *   operationId="driver_recorder_delete",
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
    public function destroy(Request $request, $id)
    {
        try {
            $data = $this->repository->deleteDriverRecord($id);
            return $this->responseJson($data['status'], new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *   path="/api/driver-recorder/upload-file",
     *   tags={"DriverRecorder"},
     *   summary="upload-file recorder",
     *   operationId="driver-recorder-upload-file",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="file video[mp4]",
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

    public function download(Request $request)
    {
        return $this->repository->download($request->id);
    }

    /**
     * @OA\Post(
     *   path="/api/driver-recorder/upload-file-deface",
     *   tags={"DriverRecorder"},
     *   summary="upload-file-deface recorder",
     *   operationId="driver-recorder-upload-file-deface",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="file video[mp4]",
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
    public function handleDeface(FileReceiver $receiver)
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
            return $this->repository->saveFileDeface($save->getFile());
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
     *   path="/api/driver-recorder/save-process",
     *   tags={"DriverRecorder"},
     *   summary="Driver recorder save process",
     *   operationId="driver-recorder-save-process",
     *      @OA\RequestBody(
     *          description="Input data",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                 type="object",
     *                   @OA\Property(property="file_id", description="file_id", format="integer", example=1),
     *                   @OA\Property(property="percent", description="percent", format="integer", example=1),
     *                   @OA\Property(property="status", description="status", format="string", example=0),
     *                   @OA\Property(property="message_error", description="message_error", format="string", example="success"),
     *              ),
     *          ),
     *      ),
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
    public function handleSaveProcess(Request $request)
    {
        $params = $request->all();
        $data = $this->repository->handleSaveProcessDefaceVideo($params);
        return  $this->responseJson(200, new BaseResource($data));
    }


    /**
     * @OA\Post(
     *   path="/api/driver-recorder/save-file-deface/{id}",
     *   tags={"DriverRecorder"},
     *   summary="save-file-deface recorder",
     *   operationId="driver-recorder-save-file-deface",
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *      @OA\Schema(
     *       type="string",
     *      ),
     *    ),
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="file video[mp4]",
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
    public function handleSaveDeface($id, FileReceiver $receiver)
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
            return $this->repository->handleSaveFileDeface($save->getFile(), $id);
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();
        return response()->json([
            "done" => $handler->getPercentageDone()
        ]);

    }

    /**
     * @OA\Get(
     *   path="/api/driver-recorder/video-deface",
     *   tags={"DriverRecorder"},
     *   summary="DriverRecorder video-deface",
     *   operationId="driver_recorder_video_deface",
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
    public function getAllVideoDeface()
    {
        $data = $this->repository->getAllVideoDeface();
        return  $this->responseJson(200, new BaseResource($data));
    }


    /**
     * @OA\Delete(
     *   path="/api/driver-recorder/video-deface/{id}",
     *   tags={"DriverRecorder"},
     *   summary="Delete DriverRecorder video deface",
     *   operationId="driver_recorder_delete_video_deface",
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
    public function deleteDefaceVideo($id)
    {
        try {
            $data = $this->repository->deleteDefaceVideo($id);
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/driver-recorder/video-deface/{id}",
     *   tags={"DriverRecorder"},
     *   summary="Driver Recorder video deface",
     *   operationId="driver_recorder_video_deface_detail",
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *      @OA\Schema(
     *       type="string",
     *      ),
     *    ),
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
    public function getDefaceVideo($id)
    {
            $data = $this->repository->getDefaceVideo($id);
            return  $this->responseJson(200, new BaseResource($data));
    }

}
