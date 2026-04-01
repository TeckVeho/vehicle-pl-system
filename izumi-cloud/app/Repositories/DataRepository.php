<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-21
 */

namespace Repository;

use App\Jobs\SaveFileAndExecuteFromAIOCRJob;
use App\Models\Data;
use App\Models\DataConnection;
use App\Models\DataItem;
use App\Repositories\Contracts\DataRepositoryInterface;
use Carbon\Carbon;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class DataRepository extends BaseRepository implements DataRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param data $model
     */

    const sortKey = [
        "name" => "name",
        "from" => "from",
        "to" => "to"
    ];

    const sortType = [
        "desc" => "DESC",
        "asc" => "ASC"
    ];

    public function model()
    {
        return DataConnection::class;
    }

    public function paginateAndSort($perPage = 10, $sortBy = 'data_connections.id', $sortType = 'desc', $search = null)
    {
        $listRoleName = auth()->user()->getRoleNames();

        if ($sortBy == 'name') {
            $sortBy = 'data_connections.name';
        }
        if ($sortBy == 'from') {
            $sortBy = 'st1.name';
        }
        if ($sortBy == 'to') {
            $sortBy = 'st2.name';
        }

        $query = DataConnection::role($listRoleName)
            ->select("data_connections.id", "data_connections.name", "data_connections.type as type", "data_connections.frequency", "data_connections.frequency_between",
                "data_connections.connection_frequency", "data_connections.connection_timing", "data_connections.remark",
                "data_connections.final_connect_time", "data_connections.final_status",
                "st1.name as from", "st2.name as to"
            )
            ->leftJoin('systems as st1', 'st1.id', '=', 'data_connections.from')
            ->leftJoin('systems as st2', 'st2.id', '=', 'data_connections.to')
            ->whereNotIn('data_code', ['ICL_1012', 'ICL_1013', 'ICL_1014'])
            ->whereNull('data_connections.deleted_at');

        if ($search != null) {
            $query = $query->where('data_connections.name', 'like', "%{$search}%");
        }

        if ($sortBy) {
            $query = $query->orderBy($sortBy, $sortType);
        }

        return $query->paginate($perPage);
    }

    public function findOneWithDataDetail($id)
    {
        $model = $this->model->with(['from', 'to'])->find($id);
        $data_item = [];
        if ($model) {
            $data_item = DataItem::select('id', 'status', 'created_at')->with('file')->where('status', 'success')
                ->where('data_connection_id', $id)
                ->orderBy('id', 'desc')
                ->get();
        }
        return ['data' => $model, 'data_item' => $data_item];
    }


    public function getListDataImport()
    {
        $listRoleName = auth()->user()->getRoleNames();
        return DataConnection::role($listRoleName)
            ->select("data_connections.id", "data_connections.name", "data_connections.is_import")
            ->join('systems as st1', 'st1.id', '=', 'data_connections.to')
            ->where('st1.name', 'イズミクラウド')->get();
    }

    public function orcAiUnit(array $attributes)
    {
        $connection = DataConnection::where('data_code', 'ICL_1021')->first();
        if ($connection) {
            if ($connection) {
                $connection->final_connect_time = Carbon::now();
                $connection->final_status = 'excluding';
                $connection->save();
            }
            $dataItem = DataItem::create([
                DataItem::STATUS => 'excluding',
                DataItem::CONTENT => $attributes['response'],
                DataItem::WHO_UPLOADED => 0,
                DataItem::TYPE => 'passive',
                DataItem::DATA_CONNECTION_ID => $connection->id,
                DataItem::DATA_CONNECTION_HISTORY => null,
                DataItem::MSG_ERROR => null,
                DataItem::RESPONSE_BODY => null
            ]);

            if ($dataItem) {
                SaveFileAndExecuteFromAIOCRJob::dispatch($connection->id, $dataItem->id)->delay(now()->addMinutes(60));
                return $dataItem;
            }
        }

        return false;
    }
}
