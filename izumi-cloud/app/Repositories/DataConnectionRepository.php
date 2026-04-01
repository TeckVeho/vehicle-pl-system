<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace Repository;

use App\Events\MessageSentEvent;
use App\Http\Requests\DataConnectionRequest;
use App\Jobs\DataConnectionJob;
use App\Models\DataConnection;
use App\Models\DataItem;
use App\Models\Department;
use App\Models\Employee;
use App\Repositories\Contracts\DataConnectionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\TimesheetData;
use Illuminate\Support\Facades\DB;

class DataConnectionRepository extends BaseRepository implements DataConnectionRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param dataconnection $model
     */

    public function model()
    {
        return dataconnection::class;
    }

    public function getIndex(DataConnectionRequest $request)
    {
        $listRoleName = auth()->user()->getRoleNames();
        $sortby = $request->has('sortby') ? $request->sortby : 'data_connections.id';
        if ($sortby == 'name') {
            $sortby = 'data_connections.name';
        }
        if ($sortby == 'from') {
            $sortby = 'st1.name';
        }
        if ($sortby == 'to') {
            $sortby = 'st2.name';
        }
        if ($sortby && $sortby == 'final_connect_time') {
            $sortby = 'data_connections.final_connect_time';
        }
        if ($sortby && $sortby == 'type') {
            $sortby = 'data_connections.type';
        }
        if ($sortby && $sortby == 'connection_frequency') {
            $sortby = 'data_connections.connection_frequency';
        }
        if ($sortby && $sortby == 'connection_timing') {
            $sortby = 'data_connections.connection_timing';
        }
        if ($sortby && $sortby == 'final_status') {
            $sortby = 'data_connections.final_status';
        }
        if (!in_array($sortby, ['data_connections.name', 'st1.name', 'st2.name', 'data_connections.final_connect_time', 'data_connections.type',
            'data_connections.connection_frequency', 'data_connections.connection_timing', 'data_connections.final_status'])) {
            $sortby = 'data_connections.id';
        }

        $per_page = (int)$request->get('per_page', 20);
        $sorttype = $request->get('sorttype', 'asc');

        $query = DataConnection::role($listRoleName)
            ->select("data_connections.id", "data_connections.name", "data_connections.type as type",
                "data_connections.frequency", "data_connections.frequency_between",
                "data_connections.connection_frequency", "data_connections.connection_timing", "data_connections.final_connect_time",
                "data_connections.final_status", "st1.name as from", "st2.name as to"
            )
            ->leftJoin('systems as st1', 'st1.id', '=', 'data_connections.from')
            ->leftJoin('systems as st2', 'st2.id', '=', 'data_connections.to')->whereNull('data_connections.deleted_at');

        if ($request->has('name')) {
            $query = $query->where("data_connections.name", 'like', "%{$request['name']}%");
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $start_date = Carbon::parse($request->get('start_date'))->startOfDay();
            $end_date = Carbon::parse($request->get('end_date'))->endOfDay();
            $query = $query->whereBetween('data_connections.final_connect_time', [$start_date, $end_date]);
        } elseif ($request->has('start_date')) {
            $start_date = Carbon::parse($request->get('start_date'))->startOfDay();
            $query = $query->whereDate('data_connections.final_connect_time', '>=', $start_date);
        } elseif ($request->has('end_date')) {
            $end_date = Carbon::parse($request->get('end_date'))->endOfDay();
            $query = $query->whereDate('data_connections.final_connect_time', '<=', $end_date);
        }

        $result = $query->orderBy($sortby, $sorttype)->paginate($per_page);
        return $result;
    }

    public function showDetail($id)
    {
        $dataConnection = DataConnection::select("data_connections.id", "data_connections.name", "data_connections.type as type",
            "data_connections.frequency", "data_connections.frequency_between",
            "data_connections.connection_frequency", "data_connections.connection_timing", "data_connections.final_connect_time",
            "data_connections.final_status", "st1.name as from", "st2.name as to"
        )
            ->leftJoin('systems as st1', 'st1.id', '=', 'data_connections.from')
            ->leftJoin('systems as st2', 'st2.id', '=', 'data_connections.to')
            ->where("data_connections.id", "=", $id)->first();
        $dataLog = [];
        if ($dataConnection) {
            $dataLog = DataItem::select('id', 'status', 'created_at')->with('file')->where("data_connection_id", "=", $id)
                ->orderBy('id', 'desc')->get();
        }
        return ['data_connection' => $dataConnection, 'data_log' => $dataLog];
    }

    public function execQueue($id, $date, $url_api_callback = null, $department_name = null)
    {
        $date = $date ? $date : Carbon::now()->format('Y-m-d');
        $dataConnection = DataConnection::find($id);
        if ($dataConnection) {
            DataConnectionJob::dispatch($id, $date, $url_api_callback, $department_name);
            return true;
        } else {
            return false;
        }
    }

    public function changeStatus(Request $request)
    {
        $dataItem = DataItem::where('id', $request->get('item_id'))->where('status', 'excluding')->first();
        $status = $request->boolean('status');
        $body = $request->get('content');

        $dataConnection = $dataItem->dataConnection;

        if ($dataItem && $dataItem->dataConnection) {

            if ($dataConnection) {
                $dataConnection->final_status = $status ? 'success' : 'fail';
                $dataConnection->save();
            }

            $dataItem->status = $status ? 'success' : 'fail';
            $dataItem->type = 'active';
            $dataItem->data_connection_history = $dataConnection->toArray();

            if (!$status) {
                $dataItem->msg_error = ["message" => 'Api internal error', "message_detail" => $body];
            }
            if ($status) {
                $dataItem->response_body = $body;
            }
            $dataItem->save();
            event(new MessageSentEvent($dataConnection, $dataItem));

            return ["status" => true, 'message' => 'Change status success'];
        } else {
            return ["status" => false, 'message' => 'Item id not exist or is changed'];
        }
    }
}
