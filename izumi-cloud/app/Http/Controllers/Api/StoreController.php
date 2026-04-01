<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Route;
use App\Models\Store;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\StoreResource;
use Illuminate\Http\Request;
use App\Http\Requests\storeFromMobileAppRequest;
use App\Http\Resources\storeDetailFromMobileAppResource;
use Helper\Common;
use Illuminate\Support\Facades\File;
use App\Models\Course;
use App\Http\Resources\StoreInCourseResource;
use Illuminate\Support\Arr;

class StoreController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(StoreRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/store",
     *   tags={"Store"},
     *   summary="List store",
     *   operationId="store_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *           "code": 200,
     *           "data": {
     *               "result": {
     *                   {
     *                       "id": 1,
     *                       "customer_name": "Vinmart",
     *                       "deleted_at": null,
     *                       "created_at": 1657080943,
     *                       "updated_at": 1657080943
     *                   }
     *               },
     *               "pagination": {
     *                   "display": 1,
     *                   "total_records": 1,
     *                   "per_page": 15,
     *                   "current_page": 1,
     *                   "total_pages": 1
     *               }
     *           }
     *       }
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
    public function index(StoreRequest $request)
    {
        $data = $this->repository->paginate($request->per_page, ['*'], "paginate", [
            "store_name" => Arr::get($request->all(), 'store_name', null),
            "sort_by" => Arr::get($request->all(), 'sort_by', null),
            "sort_type" => Arr::get($request->all(), 'sort_type', null),
        ]);
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/store",
     *   tags={"Store"},
     *   summary="Add new store",
     *   operationId="store_create",
     *   @OA\Parameter(name="store_name", in="query", required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *   @OA\Parameter(name="delivery_destination_code", in="query",
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Parameter(name="tel_number", in="query",
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Parameter(name="destination_name_kana", in="query",
     *     @OA\Schema(type="string"),
     *   ),
     *   @OA\Parameter(name="destination_name", in="query",
     *     @OA\Schema(type="string"),
     *   ),
     *   @OA\Parameter(name="post_code", in="query",
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Parameter(name="address_1", in="query",
     *     @OA\Schema(type="string"),
     *   ),
     *   @OA\Parameter(name="address_2", in="query",
     *     @OA\Schema(type="string"),
     *   ),
     *   @OA\Parameter(name="pass_code", in="query",
     *     @OA\Schema(type="integer"),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *       "code": 200,
     *       "data": {
     *           "store_name": "name",
     *           "updated_at": 1657081301,
     *           "created_at": 1657081301,
     *           "id": 2
     *       }
     *   }
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(StoreRequest $request)
    {
        try {
            $data = $this->repository->registerStoreForWeb($request);
            return $this->responseJson(200, new StoreResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/store/{id}",
     *   tags={"Store"},
     *   summary="Detail Store",
     *   operationId="store_show",
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
     *       "code": 200,
     *       "data": {
     *           "id": 1,
     *           "store_name": "Vinmart+ 22 Nguyen Thi Dinh Update",
     *           "deleted_at": null,
     *           "created_at": 1657080838,
     *           "updated_at": 1657080891
     *       }
     *       }
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
            $store = $this->repository->showStore($id);
            return $this->responseJson(200, new BaseResource($store));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/store/{id}",
     *   tags={"Store"},
     *   summary="Update Store",
     *   operationId="store_update",
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
     *            @OA\Property(property="store_name",format="string",),
     *            @OA\Property(property="delivery_destination_code",format="integer", example="99999"),
     *            @OA\Property(property="destination_name_kana",format="string", example="99999"),
     *            @OA\Property(property="destination_name", format="string",example="99999"),
     *            @OA\Property(property="post_code", format="integer",example="99999"),
     *            @OA\Property(property="address_1", format="string",example="99999"),
     *            @OA\Property(property="address_2", format="string",example="99999"),
     *            @OA\Property(property="pass_code", format="integer",example="1234"),
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *   "code": 200,
     *   "data": {
     *       "id": 1,
     *       "store_name": "name update",
     *       "deleted_at": null,
     *       "created_at": 1657080838,
     *       "updated_at": 1657081381
     *   }
     *   }
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
    public function update(StoreRequest $request, $id)
    {
        $data = $this-> repository->updateStoreForWeb($request, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Post(
     *   path="/api/update-store-for-web/{id}",
     *   tags={"Store"},
     *   summary="Update Store",
     *   operationId="store_update_for_web",
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
     *            @OA\Property(property="store_name",format="string",),
     *            @OA\Property(property="delivery_destination_code",format="integer", example="99999"),
     *            @OA\Property(property="destination_name_kana",format="string", example="99999"),
     *            @OA\Property(property="destination_name", format="string",example="99999"),
     *            @OA\Property(property="post_code", format="integer",example="99999"),
     *            @OA\Property(property="address_1", format="string",example="99999"),
     *            @OA\Property(property="address_2", format="string",example="99999"),
     *            @OA\Property(property="pass_code", format="integer",example="1234"),
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={
     *   "code": 200,
     *   "data": {
     *       "id": 1,
     *       "store_name": "name update",
     *       "deleted_at": null,
     *       "created_at": 1657080838,
     *       "updated_at": 1657081381
     *   }
     *   }
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
    public function updateStore(StoreRequest $request, $id)
    {
        $data = $this->repository->updateStoreForWeb($request, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/store/{id}",
     *   tags={"Store"},
     *   summary="Delete Store",
     *   operationId="store_delete",
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
     *      example={
     *       "code": 200,
     *       "message": "delete success",
     *       "data": null
     *   }
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
     * @OA\Get(
     *   path="/api/store/list-all",
     *   tags={"Store"},
     *   summary="List all store",
     *   operationId="store_index_all",
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
        $datas = Store::select('store_name', 'id')->get();
        return $this->responseJson(200, $datas);
    }

    /**
     * @OA\Get(
     *   path="/api/mobile/store/list/{course_id}",
     *   tags={"StoreMobile"},
     *   summary="List Stores of a course",
     *   operationId="stores_of_a_course",
     *   @OA\Parameter(
     *     name="course_id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="store_name",
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
    public function listStoreInCourse(Request $request, Course $course)
    {
        $department_id = Arr::get($request->all(), 'department', null);
        $store_name = Arr::get($request->all(), 'store_name', null);
        $orderBy = Arr::get($request->all(), 'order_by', null);
        $orderType = Arr::get($request->all(), 'order_type', null);

        $stores = $course->routes()->with([
            'stores' => function ($query) {
                $query->select(['id', 'store_name', 'pass_code']);
            }
        ])->get(['routes.id'])->toArray();

        $storesList = [];
        foreach ($stores as &$s) {
            $storesList = array_merge($storesList, $s['stores']);
        }
        $storeListIds = [];
        foreach ($storesList as $key => $value) {
            if (!in_array($value['id'], $storeListIds)) {
                $storeListIds[] = $value['id'];
            }
        }

        $storesQuery = Store::query();

        $storesQuery = $storesQuery->whereIn('id', $storeListIds);
        // filter
        if ($store_name != null) {
            $storesQuery = $storesQuery->where('store_name', 'like', '%' . $store_name . '%');
        }
        if ($orderBy && $orderType)
            $storesQuery = $storesQuery->orderBy($orderBy, $orderType);

        return $storesQuery->get(['id', 'store_name', 'pass_code']);
    }

    /**
     * @OA\Get(
     *   path="/api/mobile/store/{id}",
     *   tags={"StoreMobile"},
     *   summary="Detail a store with full peroperties",
     *   operationId="Detail_a_store_with_full_peroperties",
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
    public function storeDetailFromMobileApp(storeFromMobileAppRequest $request, Store $id)
    {
        return new storeDetailFromMobileAppResource($id);
    }

    /**
     * @OA\POST(
     *   path="/api/mobile/store/{id}?_method=put",
     *   tags={"StoreMobile"},
     *   summary="Update a store in mobile required pass code",
     *   operationId="store_update_required_pass_code",
     *   @OA\Parameter(name="pass_code", in="query", required=true,
     *     @OA\Schema(type="string"),
     *   ),
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
     *       "code": 200,
     *       "data": {
     *           "store_name": "name",
     *           "updated_at": 1657081301,
     *           "created_at": 1657081301,
     *           "id": 2
     *       }
     *   }
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function storeEditFromMobileApp(storeFromMobileAppRequest $request, Store $id)
    {
        $result = $this->repository->storeEditFromMobileApp($request->validated(), $id);
        return $result;
    }

    /**
     * @OA\Get(
     *   path="/api/mobile/store/image/{image_type}/{id}",
     *   tags={"StoreMobile"},
     *   summary="Detail a store with image url",
     *   operationId="store_images",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *  @OA\Parameter(
     *     name="image_type",
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
    public function storeImage(storeFromMobileAppRequest $request, $image_type, Store $id)
    {
        switch ($image_type) {
            case STORE_IMAGE_TYPE['delivery_route_map']:
                return response()->file(storage_path('app/' . $id->delivery_route_map_path));
                break;
            case STORE_IMAGE_TYPE['parking_position_1']:
                return response()->file(storage_path('app/' . $id->parking_position_1_file_path));
                break;
            case STORE_IMAGE_TYPE['parking_position_2']:
                return response()->file(storage_path('app/' . $id->parking_position_2_file_path));
                break;
            default:
                break;
        }
        return null;
    }

    /**
     * @OA\Get(
     *   path="/api/mobile/store-with-department/list",
     *   tags={"StoreMobile"},
     *   summary="List Stores with-department",
     *   operationId="stores_with_department",
     *   @OA\Parameter(
     *     name="department_id",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="store_name",
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
    public function listStoreFromDepartment(Request $request)
    {
        $department_id = Arr::get($request->all(), 'department_id', null);
        $store_name = Arr::get($request->all(), 'store_name', null);
        $orderBy = Arr::get($request->all(), 'order_by', 'id');
        $orderType = Arr::get($request->all(), 'order_type', 'ASC');


        $storesQuery = Course::where('courses.department_id', $department_id)
            ->join('course_route', 'course_route.course_id', '=', 'courses.id')
            ->join('routes', 'routes.id', '=', 'course_route.route_id')
            ->join('route_store', 'route_store.route_id', '=', 'routes.id')
            ->join('stores', 'stores.id', '=', 'route_store.store_id')
            ->select('stores.id', 'stores.store_name', 'stores.pass_code');

        if ($store_name != null) {
            $storesQuery = $storesQuery->where('store_name', 'like', '%' . $store_name . '%');
        }
        if ($orderBy && $orderType)
            $storesQuery = $storesQuery->orderBy($orderBy, $orderType);

        return $storesQuery->groupBy('id')->get(['id', 'store_name', 'pass_code']);
    }
}
