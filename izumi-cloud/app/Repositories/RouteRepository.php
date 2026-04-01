<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace Repository;

use App\Models\Route;
use App\Models\Store;
use App\Repositories\Contracts\RouteRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Application;

class RouteRepository extends BaseRepository implements RouteRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param Route $model
     */

    public function model()
    {
        return Route::class;
    }


    public function index($request)
    {
        $route_id = $request->get('route_id');
        $per_page = $route_id ? 1 : $request->per_page;
        $sortby = $request->has('sort_by') ? $request->sort_by : 'routes.id';
        if ($sortby == 'department_name') {
            $sortby = 'departments.position';
        }
        if ($sortby == 'name') {
            $sortby = 'routes.name';
        }
        if ($sortby == 'customer_name') {
            $sortby = 'customers.customer_name';
        }
        if ($sortby == 'route_fare_type') {
            $sortby = 'routes.route_fare_type';
        }
        if ($sortby == 'fare') {
            $sortby = 'routes.fare';
        }
        if ($sortby == 'highway_fee') {
            $sortby = 'routes.highway_fee';
        }
        if ($sortby == 'highway_fee_holiday') {
            $sortby = 'routes.highway_fee_holiday';
        }
        if ($sortby == 'store') {
            $sortby = 'stores_count';
        }
        if ($sortby == 'route_id') {
            $sortby = 'routes.id';
        }

        $sorttype = $request->has('sort_type') && $request->boolean('sort_type') ? 'desc' : 'asc';

        $query = Route::with(['stores:id,store_name', 'routeNonDelivery:route_id,number_at,is_week'])
            ->select("routes.id", "departments.name as department_name", "routes.name",
                "customers.id as customer_id", "customers.customer_name",
                "routes.route_fare_type", "routes.fare", "routes.highway_fee",
                "routes.highway_fee_holiday", "routes.is_government_holiday", "routes.remark"
            )->withCount('stores')
            ->leftJoin('departments', function ($join) {
                $join->on('departments.id', '=', 'routes.department_id');
                $join->whereRaw('departments.deleted_at is null');
            })->leftJoin('customers', function ($join) {
                $join->on('customers.id', '=', 'routes.customer_id');
                $join->whereRaw('customers.deleted_at is null');
            })->when($request->get('department_id'), function ($query) use ($request) {
                return $query->where('routes.department_id', $request->get('department_id'));
            })->when($request->get('name'), function ($query) use ($request) {
                return $query->where('routes.name', 'like', "%{$request->get('name')}%");
            })->when($request->get('customer_id'), function ($query) use ($request) {
                return $query->where('routes.customer_id', $request->get('customer_id'));
            })->when($route_id, function ($query) use ($route_id) {
                return $query->where('routes.id', $route_id);
            });
        $result = $query->orderBy($sortby, $sorttype)->paginate($per_page);

        if ($result->count() > 0) {
            foreach ($result as $key => $value) {
                $list_week = $value->routeNonDelivery->where('is_week', 1)->where('route_id', $value->id)->pluck('number_at')->toArray();
                $list_month = $value->routeNonDelivery->where('is_week', '<>', 1)->where('route_id', $value->id)->pluck('number_at')->toArray();
                $value->list_week = $list_week;
                $value->list_month = $list_month;
                $value->store_count = $value->stores_count;
//                if ($route_id) {
//                    $stores = Store::select('store_name', 'id')->selectRaw('COALESCE(false) AS value')->get();
//                    $value->store_with_status = $this->mapStoreStatus($value->stores, $stores);
//                } else {
                $value->store_with_status = [];
//                }
                $value->unsetRelation('routeNonDelivery');
            }
        }
        return $result;
    }

    public function storeRoute($dataInput)
    {
        $list_weeks = Arr::get($dataInput, 'list_week');
        $list_months = Arr::get($dataInput, 'list_month');
        $list_store = Arr::get($dataInput, 'list_store');
        $route = Route::create($dataInput);
        if ($list_weeks && count($list_weeks) > 0) {
            foreach ($list_weeks as $week) {
                $dataWeek = [
                    'number_at' => $week,
                    'is_week' => 1
                ];
                $route->route_non_delivery()->updateOrCreate($dataWeek);
            }
        }
        if ($list_months && count($list_months) > 0) {
            foreach ($list_months as $month) {
                $dataMonth = [
                    'number_at' => $month,
                    'is_week' => 0
                ];
                $route->route_non_delivery()->updateOrCreate($dataMonth);
            }
        }
        if ($list_store && count($list_store) > 0) {
            $route->stores()->sync($list_store);
        }
        return $route;
    }

    public function updateRoute($dataInput)
    {
        $routeData = [];
        if ($dataInput && count($dataInput) > 0) {
            foreach ($dataInput as $value) {
                $list_weeks = Arr::get($value, 'list_week');
                $list_months = Arr::get($value, 'list_month');
                $list_store = Arr::get($value, 'list_store');
                $id = Arr::get($value, 'id');
                $route = Route::find($id);
                if (!$id || !$route) {
                    continue;
                }
                if (array_key_exists('department_id', $value)) {
                    unset($value['department_id']);
                }
                $route->update($value);
                if ($list_weeks && count($list_weeks) > 0) {
                    $route->route_non_delivery()->where('is_week', 1)->delete();
                    foreach ($list_weeks as $week) {
                        $dataWeek = [
                            'number_at' => $week,
                            'is_week' => 1
                        ];
                        $route->route_non_delivery()->updateOrCreate($dataWeek);
                    }
                } elseif (array_key_exists('list_week', $value) && count($list_weeks) == 0) {
                    $route->route_non_delivery()->where('is_week', 1)->delete();
                }
                if ($list_months && count($list_months) > 0) {
                    $route->route_non_delivery()->where('is_week', '<>', 1)->delete();
                    foreach ($list_months as $month) {
                        $dataMonth = [
                            'number_at' => $month,
                            'is_week' => 0
                        ];
                        $route->route_non_delivery()->updateOrCreate($dataMonth);
                    }
                } elseif (array_key_exists('list_month', $value) && count($list_months) == 0) {
                    $route->route_non_delivery()->where('is_week', '<>', 1)->delete();
                }
                if ($list_store && count($list_store) > 0) {
                    $route->stores()->sync($list_store);
                }
                $routeData[] = $route;
            }
        }
        return $routeData;
    }

    private function mapStoreStatus($dataStore, $stores)
    {
        $dataArrId = $dataStore->pluck('id', 'id')->toArray();
        if ($stores->count() > 0) {
            $data = $stores->whereIn('id', $dataArrId)->all();
            foreach ($data as $key => $val) {
                $stores[$key]->value = true;
            }
        }
        return $stores;
    }
}
