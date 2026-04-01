<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-11-10
 */

namespace Repository;

use App\Jobs\SaveFileToS3Job;
use App\Models\DefaceVideo;
use App\Models\DriverRecorder;
use App\Models\File;
use App\Models\ProcessEvent;
use App\Repositories\Contracts\DriverRecorderRepositoryInterface;
use Carbon\Carbon;
use Helper\Common;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;
use ZipArchive;
use App\Models\DriverRecorderPlayList;
use App\Events\ProcessEvents;
class DriverRecorderRepository extends BaseRepository implements DriverRecorderRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param DriverRecorder $model
     */

    public function model()
    {
        return DriverRecorder::class;
    }

    public function paginate($limit = null, $filter = [], $columns = ['*'], $method = "paginate")
    {
        $query = $this->model->join('departments', 'departments.id', '=', 'department_id')
            ->with([
                'file' => function ($query) {
                    $query->orderBy('group_position')->select([
                        "id", "movie_title", "file_name", "file_extension", "file_path", "file_size", "group_position", "driver_recorder_file.type"
                    ]);
                },
                'driverPlayList:id',
                'driverRecorderImages',
                'excel' => function ($query) {
                    $query->select(['id', 'file_name', 'file_extension', 'file_path', 'file_size', 'file_url', 'file_sys_disk']);
                },
            ]);
        if (isset($filter['department_id']) && $filter['department_id'] != null) {
            $query->where('departments.id', $filter['department_id']);
        }
        if (isset($filter['title']) && $filter['title'] != null) {
            $query->where('title', 'like', '%' . $filter['title'] . '%');
        }

        if (isset($filter['record_date']) && $filter['record_date'] != null) {
            $query->where('record_date', $filter['record_date']);
        } else
            if (isset($filter['month']) && $filter['month'] != null) {
                $dayInMonth = Carbon::parse($filter['month'])->daysInMonth;
                $query->where('record_date', '>=', $filter['month'] . "-01");
                $query->where('record_date', '<=', $filter['month'] . "-$dayInMonth");
            }

        if (isset($filter['type']) && $filter['type'] != null) {
            $query->where('type', $filter['type']);
        }

        if (isset($filter['type_one']) && $filter['type_one'] != null) {
            $query->where('type_one', $filter['type_one']);
        }

        if (isset($filter['type_two']) && $filter['type_two'] != null) {
            $query->where('type_two', $filter['type_two']);
        }

        if (isset($filter['shipper']) && $filter['shipper'] != null) {
            $query->where('shipper', $filter['shipper']);
        }

        if (isset($filter['accident_classification']) && $filter['accident_classification'] != null) {
            $query->where('accident_classification', $filter['accident_classification']);
        }

        if (isset($filter['place_of_occurrence']) && $filter['place_of_occurrence'] != null) {
            $query->where('place_of_occurrence', $filter['place_of_occurrence']);
        }

        if ((isset($filter['sort_by']) && $filter['sort_by'] != null) && isset($filter['sort_type']) && $filter['sort_type'] != null) {
            if ($filter['sort_by'] == 'department_id') {
                $query->orderBy('departments.position', $filter['sort_type']);
            } else {
                $query->orderBy('driver_recorders.' . $filter['sort_by'], $filter['sort_type']);

            }
        }

        if (isset($filter['sort_by_record_date']) && $filter['sort_by_record_date'] != null) {
            $query->orderBy('driver_recorders.record_date', 'asc');
        } else {
            $query->orderBy('driver_recorders.record_date', 'desc');
        }

        return $query->paginate($limit, QUERY_DRIVER_RECORD_INDEX);
    }

    public function storeDriverRecorder($dataDriverRec, $dataRecorder, $recorderImages)
    {
        $dataDriveRecorder = $this->model->create($dataDriverRec);
        $listAllFile = [];
        foreach ($dataRecorder as $group => $data) {
            $movie_title = Arr::get($data, 'movie_title');
            $list_movie = Arr::get($data, 'list_movie');
            $listAllFile = array_merge($listAllFile, array_values($list_movie));
            foreach ($list_movie as $type => $fileId) {
                $dataDriveRecorder->file()->attach($fileId, [
                    'group_position' => $group,
                    'movie_title' => $movie_title,
                    'type' => $type,
                    'created_at' => Carbon::now(),
                ]);
            }
        }
        if ($recorderImages && is_array($recorderImages)) {
            $listAllFile = array_merge($listAllFile, $recorderImages);
            $dataDriveRecorder->driverRecorderImages()->sync($recorderImages);
        }
        if (Arr::get($dataDriverRec, 'excel_file_id')) {
            $listAllFile[] = Arr::get($dataDriverRec, 'excel_file_id');
        }
        if (count($listAllFile) > 0) {
            $listAllFile = array_unique($listAllFile);
            File::query()->whereIn('id', $listAllFile)->whereNotNull('expired_at')->update(['expired_at' => null]);
            foreach ($listAllFile as $id) {
                SaveFileToS3Job::dispatch($id);
            }
        }
        return $dataDriveRecorder;
    }

    public function updateDriverRecorder($dataDriverRec, $dataRecorder, $recorderImages, $id)
    {
        $driveRecorder = $this->find($id);
        $dataDriveRecorder = $this->update($dataDriverRec, $id);
        $listAllFile = [];
        $listAllOldFile = [];
        $listOldFile = $dataDriveRecorder->file->pluck('id');
        $listOldFileImages = $dataDriveRecorder->driverRecorderImages->pluck('id');
        if (count($listOldFile)) {
            $listAllOldFile = array_merge($listAllOldFile, $listOldFile->toArray());
        }
        if (count($listOldFileImages)) {
            $listAllOldFile = array_merge($listAllOldFile, $listOldFileImages->toArray());
        }
        if ($driveRecorder && $driveRecorder->excel_file_id) {
            $listAllOldFile[] = $driveRecorder->excel_file_id;
        }

        $dataDriveRecorder->file()->detach();
        foreach ($dataRecorder as $group => $data) {
            $movie_title = Arr::get($data, 'movie_title');
            $list_movie = Arr::get($data, 'list_movie');
            $listAllFile = array_merge($listAllFile, array_values($list_movie));
            foreach ($list_movie as $type => $fileId) {
                $dataDriveRecorder->file()->attach($fileId, [
                    'group_position' => $group,
                    'movie_title' => $movie_title,
                    'type' => $type,
                    'created_at' => Carbon::now(),
                ]);
            }
        }
        if ($recorderImages && is_array($recorderImages) && !empty($recorderImages)) {
            $listAllFile = array_merge($listAllFile, $recorderImages);
            $dataDriveRecorder->driverRecorderImages()->sync($recorderImages);
        } else {
            $dataDriveRecorder->driverRecorderImages()->detach();
        }
        if (Arr::get($dataDriverRec, 'excel_file_id')) {
            $listAllFile[] = Arr::get($dataDriverRec, 'excel_file_id');
        }

        if (count($listAllOldFile)) {
            File::query()->whereIn('id', $listAllOldFile)->whereNull('expired_at')
                ->update(['expired_at' => Carbon::now()->addHours(24)]);
        }
        if (count($listAllFile) > 0) {
            $listAllFile = array_unique($listAllFile);
            File::query()->whereIn('id', $listAllFile)->whereNotNull('expired_at')->update(['expired_at' => null]);
            foreach ($listAllFile as $id) {
                SaveFileToS3Job::dispatch($id);
            }
        }
        return $dataDriveRecorder;
    }

    public function detail($id)
    {
        $data = $this
            ->with([
                'file',
                'department',
                'driverRecorderImages',
                'excel',
                'crewMember' => function ($query) {
                $query->select('id', 'name');
            }])
            ->find($id);
        $groups = $data->file->groupBy('pivot.group_position')->all();
        $list_recorder = [];
        foreach ($groups as $gr => $val) {
            $list_recorder[$gr]['movie_title'] = data_get($val, '0.pivot.movie_title');
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
        $data->unsetRelation('file');
        $data->unsetRelation('department');
        return $data;
    }

    public function saveFile(UploadedFile $file)
    {
        $fileName = $this->createFilename($file);
        // Group files by mime type
        $mime = str_replace('/', '-', $file->getMimeType());
        // Group files by the date (week
        $dateFolder = date("YmW");

        // Build the file path
        $filePath = DRIVER_PATH_UPLOAD_FILE . "/{$dateFolder}";

        $fileData = File::create([
            'file_path' => $file->storeAs($filePath, $fileName),
            'file_name' => $file->getClientOriginalName(),
            "file_extension" => $file->getClientOriginalExtension(),
            "file_size" => $file->getSize(),
            "file_sys_disk" => 'public',
            "expired_at" => Carbon::now()->addHours(24),
            "file_url" => Storage::url($filePath . '/' . $fileName)
        ]);

        return $fileData;
    }

    public function deleteDriverRecord(int $id)
    {
        $driverRecord = $this->model->where('id', $id)->with([
            'file' => function ($query) {
                $query->orderBy('group_position')->select([
                    "id", "movie_title", "file_name", "file_extension", "file_path", "file_size", "group_position", "driver_recorder_file.type", "file_sys_disk"
                ]);
            }
        ])->first();
        if (!$driverRecord) return [
            "status" => 500,
            "message" => "model $id not found"
        ];
        $deletedCount = 0;
        $path = [];
        foreach ($driverRecord->file as $f) {
            $path[] = $f->file_path;
            if ($f->file_sys_disk == "s3") {
                if (Storage::disk('s3')->delete($f->file_path)) {
                    // $f->delete();
                    Log::info("Deleted file S3 DISK: " . $f->file_path);
                }
            } else {
                // $f->delete();
                Storage::disk('public')->delete($f->file_path);
            }
        }
        if ($driverRecord->delete()) {
            return [
                "status" => 200,
                "file_deleted" => $path,
            ];
        }
    }

    public function download(int $id)
    {
        $nameOfTypeInJp = [
            "front" => "前方",
            "inside" => "車内",
            "behind" => "後方"
        ];
        $zip = new ZipArchive;
        $driverRecord = $this->model->where('id', $id)->with([
            'file' => function ($query) {
                $query->orderBy('group_position')->select([
                    "id", "movie_title", "file_name", "file_extension", "file_path", "file_size", "group_position", "driver_recorder_file.type", "file_sys_disk"
                ]);
            },
            'excel','driverRecorderImages'
        ])->first();

        return response()->streamDownload(function () use ($id, $driverRecord, $nameOfTypeInJp) {
            $opt = new Archive();

            $opt->setContentType('application/octet-stream');

            $zip = new ZipStream("uploads.zip", $opt);

            foreach ($driverRecord->file as $f) {
                if ($file = Storage::disk('s3')->readStream($f->file_path)) {
                    $zip->addFileFromStream($nameOfTypeInJp[$f->type] . "_" . $f->group_position_ . $f->movie_title . ".mp4", $file);
                }
            }

            if ($driverRecord->excel && $fileExcel = Storage::disk('s3')->readStream($driverRecord->excel->file_path)) {
                $zip->addFileFromStream($driverRecord->excel->file_name, $fileExcel);
            }

            foreach ($driverRecord->driverRecorderImages as $fImage) {
                if ($fileDriverRecorderImages = Storage::disk('s3')->readStream($fImage->file_path)) {
                    $zip->addFileFromStream($fImage->file_name, $fileDriverRecorderImages);
                }
            }

            $zip->finish();
        }, $driverRecord->title . '.zip');
        // foreach ($driverRecord->file as $f) {
        //     Storage::disk('s3')->get($f->file_path);
        // }

        // if ($zip->open(Storage::path(PATH_ZIP_FILE . '/' . $zipFileName), ZipArchive::CREATE) === TRUE) {
        //     $zip->addFile(Storage::path($dataItemVehicle->file->file_path), 'vehicle-list.csv');
        //     $zip->addFile(Storage::path($dataItemLease->file->file_path), 'maintenance-lease-data.csv');
        //     $zip->close();
        // }
    }

    public function addOrUpdatePlayList($params, $id)
    {
        $data = $this->find($id)->driverPlayList()->sync($params);
       if(!empty($params)) {
            foreach($params as $param) {
                $driverRecorderPlayLists = DriverRecorderPlayList::where('driver_play_list_id', $param)
                    ->orderBy('position', 'asc')->get();
                if($driverRecorderPlayLists) {
                    foreach($driverRecorderPlayLists as $key => $value) {
                        DriverRecorderPlayList::where('driver_play_list_id', $value->driver_play_list_id)
                        ->where('driver_recorder_id', $value->driver_recorder_id)->update(['position' => $key]);
                    }
                }
            }
       }
        return $data;
    }


    protected function createFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = str_replace("." . $extension, "", $file->getClientOriginalName()); // Filename without extension
        // Add timestamp hash to name of the file
        $filename .= "_" . md5(time()) . "." . $extension;
        return $filename;
    }

    public function saveFileDeface(UploadedFile $file)
    {
        $dateFolder = date("YmW");
        $baseUrl = IZUMI_DEFACE_DEV;
        $env = App::environment();
        // if (App::environment('staging')) {

        //     $baseUrl = IZUMI_DEFACE_STAGE;
        // }
        // if (App::environment('production')) {
        //     $baseUrl = IZUMI_DEFACE_PRODUCT;
        // }
        $envBasePath = Common::getEnvBasePath();
        $path = $envBasePath . DRIVER_PATH_UPLOAD_FILE . "/{$dateFolder}";

        try {
            // Build the file path
            $fileName = $this->createFileNameDeface($file);
            $path_file = $file->storeAs($path, $fileName, 's3');
            $fileData = File::create([
                'file_path' => $path_file,
                'file_name' => $file->getClientOriginalName(),
                "file_extension" => $file->getClientOriginalExtension(),
                "file_size" => $file->getSize(),
                "file_url" => Storage::disk('s3')->url($path_file),
                "file_sys_disk" => 's3',
            ]);
            if ($fileData) {
                DefaceVideo::query()->create([
                    "file_id" => $fileData->id,
                ]);
            }
            $url = $baseUrl . 'api/driver-recorder/upload-file-deface/' . $fileData->id;
            Http::withoutVerifying()
                ->asMultipart()
                ->post($url, [
                    [
                        'name'     => 'file',
                        'contents' => fopen($file->getRealPath(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ],

                    [
                        'name'     => 'env',
                        'contents'     => $env,
                    ],
                ])
                ->json();
            $processEvent = ProcessEvent::query()->create([
                'file_id' => $fileData->id,
                'percent' => 0
            ]);
            return [
                "data" => $fileData,
                "channel" => App::environment() . '_cloud_deface_channel_' . $processEvent->id,
                "percent" => $processEvent->percent
            ];

        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }

    protected function createFileNameDeface(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $uuid = Str::uuid()->toString();
        $filename = $uuid . "." . $extension;
        return $filename;
    }

    public function handleSaveProcessDefaceVideo($params)
    {
        $fileId = Arr::get($params, 'file_id');
        $percent = Arr::get($params, 'percent');
        $status = Arr::get($params, 'status');
        $messageError = Arr::get($params, 'message_error');

        $processEvent = ProcessEvent::query()
            ->with([
                'defaceVideo',
                'defaceVideo.deface_file',
            ])
            ->where('file_id', $fileId)
            ->first();
        if (!$processEvent) {

            $processEvent = ProcessEvent::query()->create([
                'file_id' => $fileId,
            ]);
            event(new ProcessEvents($processEvent));
        } else {

            if ( $percent <= 100 && $percent > 98 ) {
                $processEvent->percent = 99;
            } else {
                $processEvent->percent = $percent;
            }

            if($status == 1) {
                $processEvent->status = $status;
                $processEvent->error_message = $messageError;
            }
            $processEvent->save();
            event(new ProcessEvents($processEvent));
        }
        return $processEvent;
    }

    public function handleSaveFileDeface(UploadedFile $file, $fileId)
    {
        $dateFolder = date("YmW");
        // Build the file path
        $envBasePath = Common::getEnvBasePath();
        $path = $envBasePath . DRIVER_PATH_UPLOAD_FILE . "/{$dateFolder}";
        $fileName = $this->createFileNameDeface($file);
        $path_file = $file->storeAs($path, $fileName, 's3');
        $fileData = File::create([
            'file_path' => $path_file,
            'file_name' => $file->getClientOriginalName(),
            "file_extension" => $file->getClientOriginalExtension(),
            "file_size" => $file->getSize(),
            "file_url" => Storage::disk('s3')->url($path_file),
            "file_sys_disk" => 's3',
        ]);
        if ($fileData) {

            DefaceVideo::query()
                ->where('file_id', $fileId)
                ->update([
                    'deface_file_id' => $fileData->id,
                ]);
            $processEvent = ProcessEvent::query()
                ->with([
                    'defaceVideo',
                    'defaceVideo.deface_file',
                ])
                ->where('file_id', $fileId)
                ->first();
            $processEvent->percent = 100;
            $processEvent->save();
            event(new ProcessEvents($processEvent));

        }

        return $fileData;
    }

    public function getAllVideoDeface()
    {
        $videoDeface = DefaceVideo::query()->with([
                'deface_file',
                'file'
            ])->whereNull('deleted_at')->get();
        return $videoDeface;
    }

    public function deleteDefaceVideo($id)
    {
        $defaceVideo = DefaceVideo::query()->where('id', $id)->delete();
        return $defaceVideo;
    }

    public function getDefaceVideo($id)
    {
        $videoDeface = ProcessEvent::query()->where('file_id', $id)->with([
            'defaceVideo.deface_file',
            'file'
        ])->first();
        return $videoDeface;
    }
}
