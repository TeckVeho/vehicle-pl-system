<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace Repository;

use App\Jobs\SyncCourse;
use App\Models\Course;
use App\Models\Department;
use App\Models\GovernmentHoliday;
use App\Models\Route;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use App\Models\DataConnection;
use App\Models\DataItem;
use Illuminate\Support\Facades\Http;
use App\Events\MessageSentEvent;

class CourseRepository extends BaseRepository implements CourseRepositoryInterface
{

    protected $dataConnection;
    protected $dataItem;
    protected $dataContent;

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Instantiate modeldeop
     *
     * @param Course $model
     */

    public function model()
    {
        return Course::class;
    }

    public function find($id, $columns = ['*'])
    {
        return $this->model->where('id', $id)->with([
            'routes' => function ($query) {
                $query->with([
                    'route_non_delivery' => function ($query) {
                        $query->select(['*']);
                    }
                ])->select(QUERY_ROUTE_MUST_SELECT);
            }
        ])->first(QUERY_COURSE_MUST_SELECT);
    }

    public function create(array $attributes)
    {
        $routes = $attributes['routes'];
        unset($attributes['routes']);
        $course = $this->model->create($attributes);
        if ($course ) {
            SyncCourse::dispatch($course->id);
            $syncRoutes = [];
            $count = 1;
            foreach ($routes as $key => $route) {
                $syncRoutes[$route] = [
                    'position' => $count
                ];
                $count += 1;
            }
            //$syncRoute = $course->routes()->sync($syncRoutes);
            return $course;
        }
    }

    public function courseSchedule($month, $filter = [])
    {
        $governmentHoliday = $this->getGovernmentHoliday($month);
        $departments = Department::pluck('name', 'id')->toArray();
        $dayInMonth = Carbon::parse($month)->daysInMonth;
        $listCourse = $this->listCourses($filter);
        $year = date('Y', strtotime($month));
        $monthM = date('m', strtotime($month));
        foreach ($listCourse as $key => &$course) {
            $course->fare = 0;
            $course->highway_fare = 0;
            $course->operating_day = 0;
            $schedule = [];
            for ($i = 1; $i <= $dayInMonth; $i++) {
                $schedule[$i] = [];
            }
            $courseNonDeliveires = [];
            foreach ($course->routes as $key => &$route) {
                $carbonStartDate = Carbon::createFromFormat('Y-m-d', $course->start_date);
                $carbonEndDate = ($course->end_date) ? Carbon::createFromFormat('Y-m-d', $course->end_date) : null;
                $nonDeliveries = $this->routeNonDelivery($route, $month, $governmentHoliday);
                $workingDays = [];
                for ($i = 1; $i <= $dayInMonth; $i++) {
                    if (
                        ($course->end_date && $carbonEndDate->lt(Carbon::createFromFormat('Y-m-d', $month . "-" . $i)))
                        ||
                        ($course->start_date && $carbonStartDate->gt(Carbon::createFromFormat('Y-m-d', $month . "-" . $i)))
                    ) {
                        continue;
                    }
                    if (in_array($i, $nonDeliveries)) {
                        continue;
                    }
                    $schedule[$i][] = [
                        'route_id' => $route->id,
                        'route_name' => $route->name,
                        'date' => $i
                    ];
                    $workingDays[] = $i;
                }
                $this->caculateFee($route, $workingDays, $nonDeliveries, $dayInMonth, $governmentHoliday, $month);
                // if ($course->operating_day < $route->route_operating_day) $course->operating_day = $route->route_operating_day;

                $course->fare += round($route->total_fare, 0);
                $course->highway_fare += round($route->total_highway_fare + $route->total_highway_fare_holiday, 0);
            }
            // $course->non_deliveries = $courseNonDeliveires;
            foreach ($schedule as $key => $value) {
                if (count($value) == 0) {
                    $courseNonDeliveires[] = $key;
                }
            }
            $course->operating_day = $dayInMonth - count($courseNonDeliveires);
            $course->non_deliveries = $courseNonDeliveires;
            $course->schedule = $schedule;
            $course->department_name = '';
            $course->routes_list_name = implode(",", $course->routes->pluck('name')->toArray());
            if ($departments && array_key_exists($course->department_id, $departments)) {
                $course->department_name = $departments[$course->department_id];
            }
        }
        return
            [
                "schedule" => $this->orderByFare($listCourse->toArray(), $filter),
                "gorvernment_holiday" => $this->caculateGovernmentHoliday($month, $governmentHoliday)
            ];
    }

    public function update(array $attributes, $id)
    {
        $routes = $attributes['routes'];
        $course = $this->model->find($id);
        if ($course) {
            $course->course_code = $attributes['course_code'];
            $course->start_date = $attributes['start_date'];
            $course->end_date = $attributes['end_date'];
            $course->course_type = $attributes['course_type'];
            $course->bin_type = $attributes['bin_type'];
            $course->delivery_type = $attributes['delivery_type'];
            $course->start_time = $attributes['start_time'];
            $course->gate = $attributes['gate'];
            $course->wing = $attributes['wing'];
            $course->tonnage = $attributes['tonnage'];
            $course->quantity = $attributes['quantity'];
            $course->allowance = $attributes['allowance'];
            $course->department_id = $attributes['department_id'];
            $course->course_flag = $attributes['course_flag'];
            $course->address = $attributes['address'];
            $course->save();
            $count = 1;
            SyncCourse::dispatch($id);
            foreach ($routes as $key => $route) {
                $syncRoutes[$route] = [
                    'position' => $count
                ];
                $count += 1;
            }
            if (isset($syncRoutes)) {
                $syncRoute = $course->routes()->sync($syncRoutes);
            } else $syncRoute = $course->routes()->sync([]);
            return $course;
        } else {
            return null;
        }
    }

    public function delete($id)
    {
        $model = $this->model->where('id', $id)->first();
        if ($model)
            return $model->delete();
        else return false;
    }

    public function sendCourseDataToTimeSheet($dataConnection, $dataItem)
    {
        $this->dataConnection = $dataConnection;
        $this->dataItem = $dataItem;
        $this->changeStatus('excluding');

        $urlCallApi = API_SEND_COURSE_DATA_TO_TIMESHEET_DEV;
        if (App::environment('staging')) {
            $urlCallApi = API_SEND_COURSE_DATA_TO_TIMESHEET_STAGING;
        }
        if (App::environment('production')) {
            $urlCallApi = API_SEND_COURSE_DATA_TO_TIMESHEET_PRODUCTION;
        }

        $dataNeedToSync = $this->dataOfCourseNeedToSync();

        $courseDataConnection = DataConnection::where('data_code', 'ICL_1018')->first();

        if (!$courseDataConnection) {
            $this->changeStatus('fail', "Data connection 'data_code' not exists");
            return;
        }

        $response = Http::timeout(60)->withoutVerifying()->post($urlCallApi, [
            'data' => $dataNeedToSync
        ]);

        $body = json_decode($response->getBody());
        if (!$response->getBody()) {
            $body = json_decode($response->json());
        }
        if ($response->getStatusCode() !== 200) {
            $this->changeStatus('fail', $response->getStatusCode(), $body, 'Connection API error');
        } else {
            if (isset($body->error)) {
                $this->changeStatus('fail', $body->error, $body);
            } else {
                //comment and change status with api change_status
                $this->changeStatus('success', null, $body);
            }
        }
        $response->close();
    }

    public function sendNondeliveryDataToTimeSheet(array $attributes)
    {
        $dataConnection = DataConnection::where('data_code', 'ICL_1019')->first();
        $this->dataConnection = $dataConnection;
        $this->dataItem = DataItem::create([
            'status' => null,
            'content' => null,
            'who_uploaded' => 0,
            'type' => null,
            'data_connection_id' => $this->dataConnection->id,
            'data_connection_history' => null,
            'msg_error' => null,
            'response_body' => null,
            'final_connect_time' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        $this->changeStatus('excluding');
        try {
            $non_delivery = $this->dataOfNonDeliveryNeedToSync($attributes['department_id'], $attributes['month'], true);
            if ($dataConnection) {
                return response()->json([
                    "connection" => [
                        'item_id' => $this->dataItem->id,
                        'status' => 'excluding',
                        'data_code' => 'ICL_1019'
                    ],
                    "non_delivery" => $non_delivery
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "connection" => [
                    'item_id' => $this->dataItem->id,
                    'status' => 'excluding',
                    'data_code' => 'ICL_1019'
                ],
                'error' => $e
            ], 500);
        }
    }

    private function dataOfNonDeliveryNeedToSync($department_id = null, $month = null)
    {
        $dataNeedToSync = [];
        $nowDate = Carbon::now()->format('Y-m');
        $from = ($month) ? $month : $nowDate;
        $coursesList = Course::query();
        $coursesList = $coursesList->withTrashed();

        if ($department_id) {
            $coursesList = $coursesList->where('department_id', $department_id);
        }
        $coursesList = $coursesList->get(['id', 'course_code', 'start_date', 'end_date', 'deleted_at', 'address']);

        foreach ($coursesList as $course) {
            if ($course->deleted_at !== null) {
                $dataNeedToSyncFroAllMonthInLifeCycleOfRoute["course_data"] = [
                    'course_id' => $course->id,
                    'course_code' => $course->course_code,
                    'deleted_at' => $course->deleted_at
                ];
                $dataNeedToSync[$course->id] = $dataNeedToSyncFroAllMonthInLifeCycleOfRoute;
                continue;
            }
            if ($course->end_date == null) $to = $this->accountantCalendar($nowDate);
            else {
                $to = $course->end_date;
            }
            $to = ($month) ? $month : $to;
            $period = CarbonPeriod::create($from, '1 month', $to);
            foreach ($period as $dt) {
                $M = $dt->format("Y-m");
                // $dataNeedToSyncEachMonth = [];
                $courseSchedule = $this->courseSchedule($M, [
                    "id" => $course->id,
                    "month" => $M
                ]);
                foreach ($courseSchedule['schedule'] as $courseSchedule) {
                    $dataNeedToSyncFroAllMonthInLifeCycleOfRoute[$M] =  $courseSchedule['non_deliveries'];
                }
                //   $dataNeedToSyncFroAllMonthInLifeCycleOfRoute[$M] = $dataNeedToSyncEachMonth;
            }
            if (!isset($courseSchedule['course_code'])) continue;
            $dataNeedToSyncFroAllMonthInLifeCycleOfRoute["course_data"] = [
                'course_id' => $course->id,
                'course_code' => $course->course_code,
                'course_type' => $course->course_type,
                'bin_type' => $course->bin_type,
                'delivery_type' => $course->delivery_type,
                'start_time' => $course->start_time,
                'gate' => $course->gate,
                'wing' => $course->wing,
                'tonnage' => $course->tonnage,
                'quantity' => $course->quantity,
                'allowance' => $course->allowance,
                'department_id' => $course->department_id,
                'start_date' => $course->start_date,
                'end_date' => $course->end_date,
                'address' => $course->address
            ];
            $dataNeedToSync[$course->id] = $dataNeedToSyncFroAllMonthInLifeCycleOfRoute;
        }
        // cal to time sheet.
        return $dataNeedToSync;
    }

    private function dataOfCourseNeedToSync()
    {
        $dataNeedToSync = [];
        $coursesList = Course::query();
        $coursesList = $coursesList->withTrashed();
        $coursesList = $coursesList->get(QUERY_COURSE_MUST_SELECT);

        foreach ($coursesList as $course) {
            $dataNeedToSyncFroAllMonthInLifeCycleOfRoute["course_data"] = [
                'course_id' => $course->id,
                'course_code' => $course->course_code,
                'course_type' => $course->course_type,
                'bin_type' => $course->bin_type,
                'delivery_type' => $course->delivery_type,
                'start_time' => $course->start_time,
                'gate' => $course->gate,
                'wing' => $course->wing,
                'tonnage' => $course->tonnage,
                'quantity' => $course->quantity,
                'allowance' => $course->allowance,
                'department_id' => $course->department_id,
                'start_date' => $course->start_date,
                'end_date' => $course->end_date,
                'deleted_at' => $course->deleted_at,
                'course_flag' => $course->course_flag,
                'address' => $course->address
            ];
            $dataNeedToSync[$course->id] = $dataNeedToSyncFroAllMonthInLifeCycleOfRoute;
        }
        // cal to time sheet.
        return $dataNeedToSync;
    }

    private function caculateFee(Route &$route, $workingDays, $non_deliveries, $dayInMonth, $gorvernment_holiday, $month)
    {
        $route->total_fare = 0;
        $route->total_highway_fare = 0;
        $route->total_highway_fare_holiday = 0;
        $route->route_operating_day = 0;

        $gorvernment_holiday_in_array_of_integer = [];
        foreach ($gorvernment_holiday as $key => $value) {
            $gorvernment_holiday_in_array_of_integer[] = (int)date('d', strtotime($value['date']));
        }
        $uniqueNonDeliveries = $this->checkDuplicateHoliday($non_deliveries);
        $listOfSatDayInGivenMonth = array_unique(array_merge($gorvernment_holiday_in_array_of_integer, $this->how_Many_Day_Of_Week_In_Given_Month(6, $month)));
        $listOfSunDayInGivenMonth = array_unique(array_merge($gorvernment_holiday_in_array_of_integer, $this->how_Many_Day_Of_Week_In_Given_Month(7, $month)));

        $holidayUnique = array_unique(array_merge(
            $listOfSatDayInGivenMonth,
            $listOfSunDayInGivenMonth
        ));
        $totalDaysAreWorkingInHoliday = 0;
        foreach ($holidayUnique as $key => $value) {
            if (in_array($value, $workingDays)) {
                $route->total_highway_fare_holiday += $route->highway_fee_holiday;
                $totalDaysAreWorkingInHoliday += 1;
            }
        }
        $route->route_operating_day = count($workingDays);

        if ($route->route_fare_type == ROUTE_FARE_TYPE['daily']) {
            $route->total_fare = $route->fare * $route->route_operating_day;
        } else if ($route->route_fare_type == ROUTE_FARE_TYPE['monthly']) {
            $route->total_fare = $route->fare / $dayInMonth * $route->route_operating_day;
        }

        $normalWorkingDays = count($workingDays) - $totalDaysAreWorkingInHoliday;
        $route->normal_working_days = $normalWorkingDays;
        $route->total_highway_fare = $route->highway_fee * $normalWorkingDays;
        $route->total_days_are_working_in_holiday = $totalDaysAreWorkingInHoliday;
        $route->total_gorvernment_holiday_are_working_days = ($route->is_government_holiday == IS_GORVERNMENT_HOLIDAY['yes']) ? count($gorvernment_holiday) : 0;
    }

    private function routeNonDelivery(Route &$route, $month, $governmentHoliday)
    {
        $non_deliveries = [];
        foreach ($route->route_non_delivery as $key => $non) {
            if ($non->is_week == IS_WEEK_NON_DELIVERY['yes']) { // nếu lựa chọn nghỉ mọi thứ (2, 3, 4, 5, 6, 7, chủ nhật) trong tuần
                unset($route->route_non_delivery[$key]);
                $dOweek = $this->how_Many_Day_Of_Week_In_Given_Month($non->number_at, $month);
                foreach ($dOweek as $key => $day) {
                    $non_deliveries[$day] = $day;
                    $route->route_non_delivery[$day] = [
                        "route_id" => $route->id,
                        "number_at" => $day,
                        "is_week" => IS_WEEK_NON_DELIVERY['yes'],
                        "is_gorvernment_holiday" => IS_GORVERNMENT_HOLIDAY['no']
                    ];
                }
            } else {
                $non_deliveries[] = $non->number_at;
            }
        }
        if ($route->is_government_holiday == IS_GORVERNMENT_HOLIDAY['yes']) { // nếu lựa chọn nghỉ các ngày lễ quy định theo chính phủ Nhật Bản.
            foreach ($governmentHoliday as $key => $holiday) {
                $day = (int)date('d', strtotime($holiday['date']));
                $non_deliveries[$day] = $day;
                $route->route_non_delivery[$day] = [
                    "route_id" => $route->id,
                    "number_at" => (int)date('d', strtotime($holiday['date'])),
                    "is_week" => IS_WEEK_NON_DELIVERY['no'],
                    "is_gorvernment_holiday" => IS_GORVERNMENT_HOLIDAY['yes']
                ];
            }
        } else if ($route->is_government_holiday == IS_GORVERNMENT_HOLIDAY['no']) { // nếu KHÔNG lựa chọn nghỉ các ngày lễ quy định theo chính phủ Nhật Bản.
            foreach ($governmentHoliday as $key => $holiday) {
                $day = (int)date('d', strtotime($holiday['date']));
                unset($non_deliveries[$day]);
                unset($route->route_non_delivery[$day]);
            }
        }
        return $non_deliveries;
    }

    private function listCourses($filter = [])
    {
        $query = $this->model->with([
            'routes' => function ($query) {
                $query->with([
                    'route_non_delivery' => function ($query) {
                        $query->select(['*']);
                    }
                ])->select(QUERY_ROUTE_MUST_SELECT);
            }
        ])->leftJoin('departments', function ($join) {
            $join->on('departments.id', '=', 'courses.department_id');
            $join->whereRaw('departments.deleted_at is null');
        });

        if (isset($filter['id']) && $filter['id'] != null) {
            $query = $query->where('courses.id', $filter['id']);
        }

        if (isset($filter['course_code']) && $filter['course_code'] != null) {
            $query = $query->where('courses.course_code', 'like', "%" . $filter['course_code'] . "%");
        }

        if (isset($filter['department']) && $filter['department'] != null) {
            $query = $query->where('courses.department_id', $filter['department']);
        }

        if (isset($filter['order_by']) && isset($filter['order_type']) && $filter['order_by'] && $filter['order_type']) {
            $query = $query->orderBy('departments.position', 'ASC');
            switch ($filter['order_by']) {
                case 'department':
                    $query = $query->orderBy('departments.position', $filter['order_type']);
                    break;
                case 'course_code':
                    $query = $query->orderBy('courses.course_code', $filter['order_type']);
                    break;
                default:
                    break;
            }
        } else {
            $query = $query->orderBy('departments.position', 'ASC');
        }

        if (isset($filter['month'])) {
            $query = $query->where(function ($query) use ($filter) {
                $dayInMonth = Carbon::parse($filter['month'])->daysInMonth;
                $query->where(
                    function ($query) use ($filter, $dayInMonth) {
                        $query->where('courses.start_date', '<=', $filter['month'] . "-" . $dayInMonth);
                        $query->where('courses.end_date', '>=', $filter['month'] . "-01");
                    }
                );
                $query->orWhere(
                    function ($query) use ($filter, $dayInMonth) {
                        $query->where('courses.start_date', '<=', $filter['month'] . "-" . $dayInMonth);
                        $query->where('courses.end_date', null);
                    }
                );
            });
        }

        $query = $query->get(QUERY_COURSE_MUST_SELECT);
        return $query;
    }

    private function how_Many_Day_Of_Week_In_Given_Month($dayOfWeek, $month)
    {
        $dayInMonth = Carbon::parse($month)->daysInMonth;
        $strToTime = strtotime($month);
        $M = date('M', $strToTime);
        $Y = date('Y', $strToTime);
        $result = [];
        $firstDayOfMonth = (int)date('d', strtotime(Carbon::parse("first " . DAY_IN_WEEKEND[$dayOfWeek] . " of " . $M . " " . $Y)));
        $result[] = $firstDayOfMonth;
        while ($firstDayOfMonth + 7 <= $dayInMonth) {
            $firstDayOfMonth += 7;
            $result[] = $firstDayOfMonth;
        }
        return $result;
    }

    private function getGovernmentHoliday($month)
    {
        $strToTime = strtotime($month);
        $m = date('m', $strToTime);
        $Y = date('Y', $strToTime);
        $gorvernmentHoliday = GovernmentHoliday::whereMonth('date', $m)->whereYear('date', $Y)->get([
            'id',
            'date',
            'description'
        ])->toArray();
        return $gorvernmentHoliday;
    }

    private function caculateGovernmentHoliday($month, $gorvernmentHoliday)
    {
        $dayInMonth = Carbon::parse($month)->daysInMonth;
        foreach ($gorvernmentHoliday as $key => $value) {
            $listGorvermentHolidays[] = (int)date('d', strtotime($value['date']));
        }
        if (isset($listGorvermentHolidays)) {
            for ($i = 1; $i <= $dayInMonth; $i++) {
                if (in_array($i, $listGorvermentHolidays)) {
                    $result[$i] = true;
                } else {
                    $result[$i] = false;
                }
            }
            return $result;
        }
        return [];
    }

    private function checkDuplicateHoliday($non_deliveries)
    {
        //gorverntment holiday same as weekly non-delivery or day-delivery
        $uniqueNonDeliveries = array_unique($non_deliveries);
        return $uniqueNonDeliveries;
    }

    private function orderByFare($schedule, $filter = [])
    {
        if (isset($filter['order_by']) && isset($filter['order_type'])) {
            if ($filter['order_by'] && $filter['order_type']) {
                switch ($filter['order_by']) {
                    case 'department_id':
                        $total_fare = array_column($schedule, 'department_id');
                        array_multisort($total_fare, ($filter['order_type'] == "asc") ? SORT_ASC : SORT_DESC, $schedule);
                        break;
                    case 'fare':
                        $total_fare = array_column($schedule, 'fare');
                        array_multisort($total_fare, ($filter['order_type'] == "asc") ? SORT_ASC : SORT_DESC, $schedule);
                        break;
                    case 'highway_fare':
                        $highway_fare = array_column($schedule, 'highway_fare');
                        array_multisort($highway_fare, ($filter['order_type'] == "asc") ? SORT_ASC : SORT_DESC, $schedule);
                        break;
                    default:
                        break;
                }
            }
        }
        return $schedule;
    }

    private function accountantCalendar($now)
    {
        $now = strtotime($now);
        $month = (int)date('m', $now);
        $year = ($month > 3) ? date('Y', $now) + 1 : date('Y', $now);
        return $year . "-" . "03";
    }

    private function changeStatus($status, $msgError = null, $msgRes = null, $msg = 'Internal error', $fileId = null)
    {
        if ($this->dataConnection) {
            $this->dataConnection->final_status = $status;
            $this->dataConnection->final_connect_time = Carbon::now()->format('Y-m-d H:i:s');
            $this->dataConnection->save();
        }

        $this->dataItem->status = $status;
        $this->dataItem->type = 'active';
        $this->dataItem->data_connection_history = $this->dataConnection->toArray();
        if ($this->dataContent) {
            $this->dataItem->content = $this->dataContent;
        }
        if ($fileId) {
            $this->dataItem->file_id = $fileId;
        }
        if ($msgError) {
            $this->dataItem->msg_error = ["message" => $msg, "message_detail" => $msgError];
        }
        if ($msgRes) {
            $this->dataItem->response_body = $msgRes;
        }
        $this->dataItem->save();
        event(new MessageSentEvent($this->dataConnection, $this->dataItem));
    }
}
