<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-11-15
 */

namespace Repository;

use App\Jobs\SaveFileToS3Job;
use App\Models\DriverPlayList;
use App\Models\DriverRecorder;
use App\Models\DriverRecorderPlayList;
use App\Models\File;
use App\Repositories\Contracts\DriverPlayListRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class DriverPlayListRepository extends BaseRepository implements DriverPlayListRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param DriverPlayList $model
     */

    public function model()
    {
        return DriverPlayList::class;
    }

    public function index()
    {
        return DriverPlayList::query()->with('imageFile')->get();
    }

    public function indexViewer()
    {
        return DriverPlayList::query()->with('imageFile')->with('driverRecorder')->get();
    }

    public function storePlayList($data)
    {
        $dataPlayList = $this->model->create($data);
        if ($file_image = Arr::get($data, 'file_id')) {
            File::query()->where('id', $file_image)->whereNotNull('expired_at')->update(['expired_at' => null]);
            SaveFileToS3Job::dispatch($file_image);
        }
        return $dataPlayList;
    }


    public function updatePlayList($id, $data)
    {
        $dataPlayList = $this->model->find($id);
        if ($dataPlayList) {
            if ($dataPlayList->file_id && (Arr::get($data, 'file_id') !== $dataPlayList->file_id)) {
                File::query()->where('id', $dataPlayList->file_id)->whereNull('expired_at')
                    ->update(['expired_at' => Carbon::now()->addHours(24)]);
            }
            if ($file_image = Arr::get($data, 'file_id')) {
                File::query()->where('id', $file_image)->whereNotNull('expired_at')->update(['expired_at' => null]);
                SaveFileToS3Job::dispatch($file_image);
            }
            $dataPlayList->update($data);
        }
        return $dataPlayList;
    }

    public function detail($id)
    {
        $dataPlayList = DriverPlayList::query()->with('imageFile')->find($id);
        return $dataPlayList;
    }

    public function showViewer($id)
    {
        $dataPlayList = DriverPlayList::query()
            ->with('driverRecorder.file')
            ->find($id);
        $play_list_recorder = [];
        foreach (data_get($dataPlayList, 'driverRecorder') as $data) {
            $groups = $data->file->groupBy('pivot.group_position')->all();
            $list_recorder = [];
            foreach ($groups as $gr => $val) {
                $list_recorder[$gr] = [
                    'driver_recorder_id' => data_get($data, 'id'),
                    'movie_title' => data_get($val, '0.pivot.movie_title'),
                    'department_name' => data_get($data, 'department.name'),
                    'record_date' => data_get($data, 'record_date'),
                ];
                foreach ($val as $item) {
                    $typeKey = data_get($item, 'pivot.type');
                    $disk = data_get($item, 'file_sys_disk');
                    $filePath = data_get($item, 'file_path');
                    $fileUrl = Storage::url($filePath);
                    if ($disk == 's3') {
                        $fileUrl = Storage::disk('s3')->url($filePath);
                    }
                    $list_recorder[$gr][$typeKey] = [
                        'id' => data_get($item, 'id'),
                        'file_name' => data_get($item, 'file_name'),
                        'file_url' => $fileUrl
                    ];
                }
            }
            $data->department_name = $data->department->name;
            $data->list_recorder = $list_recorder;
            $play_list_recorder = array_merge($play_list_recorder, $list_recorder);
        }
        $dataPlayList->play_list_recorder = $play_list_recorder;
        $dataPlayList->unsetRelation('driverRecorder');

        return $dataPlayList;
    }

    public function updatePosition($params) 
    {
        try {
            $listPositions = Arr::get($params, 'list_position');
                foreach ($listPositions as $key => $value) {
                   DriverRecorderPlayList::where('driver_play_list_id', $value['driver_play_list_id'])
                        ->where('driver_recorder_id', $value['driver_recorder_id'])->update(['position' => $key]);
                }
            return $listPositions;
        } catch(Exception $e) {
            return ['error' =>$e->getMessage() ];
        }
    }
}
