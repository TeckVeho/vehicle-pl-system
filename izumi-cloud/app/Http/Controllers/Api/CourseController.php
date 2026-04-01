<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Http\Controllers\Api;

use App\Exports\CourseExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Http\Requests\RouteRequest;
use App\Imports\CourseRoutImport;
use App\Models\Course;
use App\Models\Department;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\CourseResource;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Helper\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Route;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Response;

class CourseController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(CourseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="course/selected/list-all?department=1",
     *   tags={"Course"},
     *   summary="Route In Course",
     *   operationId="route_in_course",
     *   @OA\Parameter(
     *     name="department",
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
     *      example={{
     *              "id": 1,
     *              "name": "My First Route",
     *              "department_id": 1,
     *              "customer_id": 1,
     *              "route_fare_type": 1,
     *              "fare": 1200,
     *              "highway_fee": 120,
     *              "highway_fee_holiday": 125,
     *              "is_government_holiday": 1,
     *              "deleted_at": null,
     *              "created_at": "2022-07-09T15:00:00.000000Z",
     *              "updated_at": "2022-07-09T15:00:00.000000Z"
     *          },
     *          {
     *              "id": 2,
     *              "name": "My Secind Route",
     *              "department_id": 1,
     *              "customer_id": 1,
     *              "route_fare_type": 1,
     *              "fare": 1200,
     *              "highway_fee": 120,
     *              "highway_fee_holiday": 125,
     *              "is_government_holiday": 1,
     *              "deleted_at": null,
     *              "created_at": "2022-07-09T15:00:00.000000Z",
     *              "updated_at": "2022-07-09T15:00:00.000000Z"
     *          }
     *  }
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
    public function listAll(Request $request)
    {
        $department = Arr::get($request->all(), 'department', null);
        return Route::where('department_id', $department)->get(['*']);
    }

    /**
     * @OA\Get(
     *   path="/api/course/schedule",
     *   tags={"Course"},
     *   summary="List course",
     *   operationId="course_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *   "code": 200,
     *   "data": {
     *       "current_page": 1,
     *       "data": {
     *           {
     *               "id": 1,
     *               "course_code": "1900",
     *               "start_date": "2022-05-01",
     *               "end_date": "2022-07-01",
     *               "course_type": 1,
     *               "delivery_type": 1,
     *               "start_time": "20:00:00",
     *               "gate": 1,
     *               "wing": 1,
     *               "tonnage": 1,
     *               "quantity": 1,
     *               "allowance": 10000,
     *               "department_id": 1,
     *               "fare": 74400,
     *               "highway_fare": 7560,
     *               "operating_day": 24,
     *               "schedule": {
     *                   "1": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 1
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 1
     *                       }
     *                   },
     *                   "2": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 2
     *                       }
     *                   },
     *                   "3": {},
     *                   "4": {},
     *                   "5": {},
     *                   "6": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 6
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 6
     *                       }
     *                   },
     *                   "7": {
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 7
     *                       }
     *                   },
     *                   "8": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 8
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 8
     *                       }
     *                   },
     *                   "9": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 9
     *                       }
     *                   },
     *                   "10": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 10
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 10
     *                       }
     *                   },
     *                   "11": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 11
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 11
     *                       }
     *                   },
     *                   "12": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 12
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 12
     *                       }
     *                   },
     *                   "13": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 13
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 13
     *                       }
     *                   },
     *                   "14": {
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 14
     *                       }
     *                   },
     *                   "15": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 15
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 15
     *                       }
     *                   },
     *                   "16": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 16
     *                       }
     *                   },
     *                   "17": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 17
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 17
     *                       }
     *                   },
     *                   "18": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 18
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 18
     *                       }
     *                   },
     *                   "19": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 19
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 19
     *                       }
     *                   },
     *                   "20": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 20
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 20
     *                       }
     *                   },
     *                   "21": {
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 21
     *                       }
     *                   },
     *                   "22": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 22
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 22
     *                       }
     *                   },
     *                   "23": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 23
     *                       }
     *                   },
     *                   "24": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 24
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 24
     *                       }
     *                   },
     *                   "25": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 25
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 25
     *                       }
     *                   },
     *                   "26": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 26
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 26
     *                       }
     *                   },
     *                   "27": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 27
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 27
     *                       }
     *                   },
     *                   "28": {
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 28
     *                       }
     *                   },
     *                   "29": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 29
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 29
     *                       }
     *                   },
     *                   "30": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 30
     *                       }
     *                   },
     *                   "31": {
     *                       {
     *                           "route_id": 1,
     *                           "route_name": "My First Route",
     *                           "date": 31
     *                       },
     *                       {
     *                           "route_id": 2,
     *                           "route_name": "My Secind Route",
     *                           "date": 31
     *                       }
     *                   }
     *               },
     *               "routes": {
     *                   {
     *                       "id": 1,
     *                       "name": "My First Route",
     *                       "department_id": 1,
     *                       "customer_id": 1,
     *                       "route_fare_type": 1,
     *                       "fare": 1200,
     *                       "highway_fee": 120,
     *                       "highway_fee_holiday": 125,
     *                       "is_government_holiday": 1,
     *                       "total_fare": 37200,
     *                       "total_highway_fare": 2280,
     *                       "total_highway_fare_holiday": 1500,
     *                       "route_operating_day": 24,
     *                       "route_non_delivery": {
     *                           "1": {
     *                               "route_id": 1,
     *                               "number_at": 7,
     *                               "is_week": 1,
     *                               "is_gorvernment_holiday": 0
     *                           },
     *                           "2": {
     *                               "route_id": 1,
     *                               "number_at": 14,
     *                               "is_week": 1,
     *                               "is_gorvernment_holiday": 0
     *                           },
     *                           "3": {
     *                               "route_id": 1,
     *                               "number_at": 21,
     *                               "is_week": 1,
     *                               "is_gorvernment_holiday": 0
     *                           },
     *                           "4": {
     *                               "route_id": 1,
     *                               "number_at": 28,
     *                               "is_week": 1,
     *                               "is_gorvernment_holiday": 0
     *                           },
     *                           "5": {
     *                               "route_id": 1,
     *                               "number_at": 3,
     *                               "is_week": 0,
     *                               "is_gorvernment_holiday": 1
     *                           },
     *                           "6": {
     *                               "route_id": 1,
     *                               "number_at": 4,
     *                               "is_week": 0,
     *                               "is_gorvernment_holiday": 1
     *                           },
     *                           "7": {
     *                               "route_id": 1,
     *                               "number_at": 5,
     *                               "is_week": 0,
     *                               "is_gorvernment_holiday": 1
     *                           }
     *                       }
     *                   },
     *                   {
     *                       "id": 2,
     *                       "name": "My Secind Route",
     *                       "department_id": 1,
     *                       "customer_id": 1,
     *                       "route_fare_type": 1,
     *                       "fare": 1200,
     *                       "highway_fee": 120,
     *                       "highway_fee_holiday": 125,
     *                       "is_government_holiday": 1,
     *                       "total_fare": 37200,
     *                       "total_highway_fare": 2280,
     *                       "total_highway_fare_holiday": 1500,
     *                       "route_operating_day": 23,
     *                       "route_non_delivery": {
     *                           "1": {
     *                               "route_id": 2,
     *                               "number_at": 30,
     *                               "is_week": 0,
     *                               "created_at": "2022-07-10T01:01:01.000000Z",
     *                               "updated_at": "2022-07-10T01:01:01.000000Z"
     *                           },
     *                           "2": {
     *                               "route_id": 2,
     *                               "number_at": 2,
     *                               "is_week": 1,
     *                               "is_gorvernment_holiday": 0
     *                           },
     *                           "3": {
     *                               "route_id": 2,
     *                               "number_at": 9,
     *                               "is_week": 1,
     *                               "is_gorvernment_holiday": 0
     *                           },
     *                           "4": {
     *                               "route_id": 2,
     *                               "number_at": 16,
     *                               "is_week": 1,
     *                               "is_gorvernment_holiday": 0
     *                           },
     *                           "5": {
     *                               "route_id": 2,
     *                               "number_at": 23,
     *                               "is_week": 1,
     *                               "is_gorvernment_holiday": 0
     *                           },
     *                           "6": {
     *                               "route_id": 2,
     *                               "number_at": 30,
     *                               "is_week": 1,
     *                               "is_gorvernment_holiday": 0
     *                           },
     *                           "7": {
     *                               "route_id": 2,
     *                               "number_at": 3,
     *                               "is_week": 0,
     *                               "is_gorvernment_holiday": 1
     *                           },
     *                           "8": {
     *                               "route_id": 2,
     *                               "number_at": 4,
     *                               "is_week": 0,
     *                               "is_gorvernment_holiday": 1
     *                           },
     *                           "9": {
     *                               "route_id": 2,
     *                               "number_at": 5,
     *                               "is_week": 0,
     *                               "is_gorvernment_holiday": 1
     *                           }
     *                       }
     *                   }
     *               }
     *           },
     *           {
     *               "id": 2,
     *               "course_code": "1800",
     *               "start_date": "2022-05-01",
     *               "end_date": "2022-07-01",
     *               "course_type": 1,
     *               "delivery_type": 1,
     *               "start_time": "20:00:00",
     *               "gate": 1,
     *               "wing": 1,
     *               "tonnage": 1,
     *               "quantity": 1,
     *               "allowance": 10000,
     *               "department_id": 2,
     *               "fare": 0,
     *               "highway_fare": 0,
     *               "operating_day": 0,
     *               "schedule": {
     *                   "1": {},
     *                   "2": {},
     *                   "3": {},
     *                   "4": {},
     *                   "5": {},
     *                   "6": {},
     *                   "7": {},
     *                   "8": {},
     *                   "9": {},
     *                   "10": {},
     *                   "11": {},
     *                   "12": {},
     *                   "13": {},
     *                   "14": {},
     *                   "15": {},
     *                   "16": {},
     *                   "17": {},
     *                   "18": {},
     *                   "19": {},
     *                   "20": {},
     *                   "21": {},
     *                   "22": {},
     *                   "23": {},
     *                   "24": {},
     *                   "25": {},
     *                   "26": {},
     *                   "27": {},
     *                   "28": {},
     *                   "29": {},
     *                   "30": {},
     *                   "31": {}
     *               },
     *               "routes": {}
     *           }
     *       },
     *       "first_page_url": "/?page=1",
     *       "from": 1,
     *       "last_page": 1,
     *       "last_page_url": "/?page=1",
     *       "links": {
     *           {
     *               "url": null,
     *               "label": "&laquo; Previous",
     *               "active": false
     *           },
     *           {
     *               "url": "/?page=1",
     *               "label": "1",
     *               "active": true
     *           },
     *           {
     *               "url": null,
     *               "label": "Next &raquo;",
     *               "active": false
     *           }
     *       },
     *       "next_page_url": null,
     *       "path": "/",
     *       "per_page": 15,
     *       "prev_page_url": null,
     *       "to": 2,
     *       "total": 2
     *   }
     *}
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
     *     name="course_code",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
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
     *     name="is_export",
     *     in="query",
     *     @OA\Schema(
     *      type="boolean",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="month",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="department",
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
    public function index(CourseRequest $request)
    {
        $month = Arr::get($request->all(), 'month', null);
        $department_id = Arr::get($request->all(), 'department', null);
        $course_code = Arr::get($request->all(), 'course_code', null);
        $orderBy = Arr::get($request->all(), 'order_by', null);
        $orderType = Arr::get($request->all(), 'order_type', null);
        $page = Arr::get($request->all(), 'page', 1);
        $per_page = Arr::get($request->all(), 'per_page', 15);
        $isExport = $request->boolean('is_export');
        $data = $this->repository->courseSchedule($month, [
            "department" => $department_id,
            "course_code" => $course_code,
            "order_by" => $orderBy,
            "order_type" => $orderType,
            "month" => $month
        ]);

        if ($isExport) {
            $department = Department::find($department_id);
            $department = $department ? $department->name : "";
            $fileName = Carbon::parse($month . '-01')->format('Y_m') . $department . '.csv';
            return Excel::download(new CourseExport($data), $fileName, null, ['Content-Type' => 'application/octet-stream; charset=SJIS-win', 'Content-Transfer-Encoding' => 'Binary', 'Charset' => 'SJIS-win']);
        }

        return $this->responseJson(200, [
            'schedule' => $this->MyPaginate($data['schedule'], $per_page, $page),
            'gorvernment_holiday' => $data['gorvernment_holiday']
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/course",
     *   tags={"Course"},
     *   summary="Add new course",
     *   operationId="course_create",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          example={
     *       "course_code" : "Hatikano edenize",
     *       "start_date": "2022-01-01",
     *       "end_date": "2022-02-01",
     *       "course_type": 1,
     *       "delivery_type": 1,
     *       "start_time": "20:00:00",
     *       "gate": 0,
     *       "wing": 0,
     *       "tonnage": 5,
     *       "quantity": 10,
     *       "allowance": 100,
     *       "department_id": 1,
     *       "address": "1234567890",
     *       "routes" : {
     *           7, 1, 2
     *       }
     *   },
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
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(CourseRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->repository->create($request->all());
            DB::commit();
            return $this->responseJson(200, new CourseResource($data));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/course/{id}",
     *   tags={"Course"},
     *   summary="Detail Course",
     *   operationId="course_show",
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
     *      example={
     *      "code": 200,
     *      "data": {
     *          {
     *              "id": 1,
     *              "course_code": "1900",
     *              "start_date": "2022-05-01",
     *              "end_date": "2022-07-01",
     *              "course_type": 1,
     *              "delivery_type": 1,
     *              "start_time": "20:00:00",
     *              "gate": 1,
     *              "wing": 1,
     *              "tonnage": 1,
     *              "quantity": 1,
     *              "allowance": 10000,
     *              "department_id": 1,
     *              "routes": {
     *                  {
     *                      "id": 1,
     *                      "name": "My First Route",
     *                      "department_id": 1,
     *                      "customer_id": 1,
     *                      "route_fare_type": 1,
     *                      "fare": 1200,
     *                      "highway_fee": 120,
     *                      "highway_fee_holiday": 125,
     *                      "is_government_holiday": 1,
     *                      "route_non_delivery": {
     *                          {
     *                              "route_id": 1,
     *                              "number_at": 6,
     *                              "is_week": 1,
     *                              "created_at": "2022-07-10T01:01:01.000000Z",
     *                              "updated_at": "2022-07-10T01:01:01.000000Z"
     *                          }
     *                      }
     *                  },
     *                  {
     *                      "id": 2,
     *                      "name": "My Secind Route",
     *                      "department_id": 1,
     *                      "customer_id": 1,
     *                      "route_fare_type": 1,
     *                      "fare": 1200,
     *                      "highway_fee": 120,
     *                      "highway_fee_holiday": 125,
     *                      "is_government_holiday": 1,
     *                      "route_non_delivery": {
     *                          {
     *                              "route_id": 2,
     *                              "number_at": 1,
     *                              "is_week": 1,
     *                              "created_at": "2022-07-10T01:01:01.000000Z",
     *                              "updated_at": "2022-07-10T01:01:01.000000Z"
     *                          },
     *                          {
     *                              "route_id": 2,
     *                              "number_at": 30,
     *                              "is_week": 0,
     *                              "created_at": "2022-07-10T01:01:01.000000Z",
     *                              "updated_at": "2022-07-10T01:01:01.000000Z"
     *                          }
     *                      }
     *                  }
     *              }
     *          }
     *      }
     *  }
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
     * @OA\Put(
     *   path="/api/course/{id}",
     *   tags={"Course"},
     *   summary="Update Course",
     *   operationId="course_update",
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
     *          example={
     *       "start_date": "2022-01-01",
     *       "end_date": "2022-02-01",
     *       "course_type": 1,
     *       "delivery_type": 1,
     *       "start_time": "20:00:00",
     *       "gate": 0,
     *       "wing": 0,
     *       "tonnage": 5,
     *       "quantity": 10,
     *       "allowance": 100,
     *       "department_id": 1,
     *       "address": "1234567890",
     *       "routes" : {
     *           7, 1, 2
     *       }
     *   },
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
    public function update(CourseRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->update($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/course/{id}",
     *   tags={"Course"},
     *   summary="Delete Course",
     *   operationId="course_delete",
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

    public function sendCourseDataToTimeSheet(CourseRequest $request)
    {
        $month = Arr::get($request->all(), 'month', null);
        $course_code = Arr::get($request->all(), 'course_code', null);
        $accountantCalendar = Arr::get($request->all(), 'accountant_calendar', null);
        return $this->repository->sendCourseDataToTimeSheet(
            $month,
            [
                "department" => null,
                "course_code" => $course_code,
                "order_by" => null,
                "order_type" => null,
                "month" => $month
            ],
            $accountantCalendar
        );
    }

    public function sendNondeliveryDataToTimeSheet(Request $request)
    {
        return $this->repository->sendNondeliveryDataToTimeSheet([
            "department_id" => $request->department_id,
            "month" => $request->month,
            "include_non_delivery" => true
        ]);
    }

    private function MyPaginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, $options);
    }

    /**
     * @OA\Post(
     *   path="/api/course/import-course-route",
     *   tags={"Course"},
     *   summary="import course route",
     *   operationId="import_course_route",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
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
    public function importCourseRoute(Request $request)
    {
        try {
//            ini_set('memory_limit', '-1');
            Common::setInputEncoding($request->file('file'));
            Excel::import(new CourseRoutImport(), $request->file('file'));
            return $this->responseJson(Response::HTTP_OK, null, trans('messages.mes.import_success'));

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/mobile/course/list-course-by-department",
     *   tags={"Course Mobile"},
     *   summary="Route In Course",
     *   operationId="mobile_course",
     *   @OA\Parameter(
     *     name="department",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="course_code",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="order_by",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="order_type",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
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
    public function listAllCourse(Request $request)
    {
        $department = $request->get('department', null);
        $course_code = $request->get('course_code', null);
        $order_by = $request->get('order_by', 'id');
        $order_type = $request->get('order_type', 'asc');

        $data = Course::select('id', 'course_code')
            ->where('department_id', $department)
            ->when($course_code, function ($query) use ($course_code) {
                return $query->where('course_code', 'like', '%' . $course_code . '%');
            })->orderBy($order_by, $order_type)
            ->get();
        return $this->responseJson(Response::HTTP_OK, $data);
    }
}
