<?php
/**
 * Created by VeHo.
 * Year: 2026-02-13
 */

namespace Repository;

use App\Models\VehicleMaintenanceCost;
use App\Repositories\Contracts\VehicleMaintenanceCostRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use App\Jobs\SaveFileToS3Job;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\File;
use Carbon\Carbon;

class VehicleMaintenanceCostRepository extends BaseRepository implements VehicleMaintenanceCostRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param VehicleMaintenanceCost $model
       */

    public function model()
    {
        return VehicleMaintenanceCost::class;
    }

    public function syncVehicleMaintenanceCost($attributes)
    {

        Log::info("syncVehicleMaintenanceCost");

        $id = Arr::get($attributes, 'id', null);
        if ($id) {
            $data = $this->model->find($id);
            if ($data) {
                if (isset($attributes['file_id'])) {
                    $attributes['updated_at'] = Carbon::now();
                    unset($attributes['file_id']);
                }
                $data->fill($attributes);
                if ($data->isDirty()) {
                    $data->save();
                }
            } 
        } else {
            $data = $this->model->create($attributes);
            return $data;
        }
    }


    public function saveFile(UploadedFile $file)
    {

        $fileName = $this->createFilename($file);
        $mime = str_replace('/', '-', $file->getMimeType());
        $dateFolder = date("YmW");

        $filePath = VEHICLE_MAINTENANCE_COST_PATH_UPLOAD_FILE . "/{$dateFolder}";

        $fileData = File::create([
            'file_path' => $file->storeAs($filePath, $fileName),
            'file_name' => $file->getClientOriginalName(),
            "file_extension" => $file->getClientOriginalExtension(),
            "file_size" => $file->getSize(),
            "file_sys_disk" => 'public',
            "expired_at" => null,
            "file_url" => Storage::url($filePath . '/' . $fileName)
        ]);

        if ($fileData) {
            SaveFileToS3Job::dispatch($fileData->id, 'VehicleMaintenanceCost')->delay(now()->addMinute());
        }
        return $fileData;
    }

    protected function createFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = str_replace("." . $extension, "", md5($file->getClientOriginalName())); // Filename without extension
        // Add timestamp hash to name of the file
        $filename .= "_" . md5(time()) . "." . $extension;
        return $filename;
    }
}
