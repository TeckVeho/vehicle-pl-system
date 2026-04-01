<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-02-07
 */

namespace Repository;

use App\Models\File;
use App\Models\PocketBooks;
use App\Repositories\Contracts\PocketBooksRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class PocketBooksRepository extends BaseRepository implements PocketBooksRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param PocketBooks $model
       */

    public function model()
    {
        return PocketBooks::class;
    }

    public function storePocketBook($year, $fileId, $tag)
    {
        $pocketBook = $this->model::where('year', $year)
            ->orderBy('id', 'DESC')->first();
        $position = 1;
        if($pocketBook) {
            $position = $pocketBook->position + 1;
        }
        return $this->model->create([
            'year' => $year,
            'file_id' => $fileId,
            'position' => $position,
            'tag' => $tag,
        ]);

    }

    public function optionYear() {

        $yearMin = $this->model::min('year');
        $yearNow = Carbon::now()->year;
        $yearNext = $yearNow + 1;
        $startYear = $yearMin ? (int) $yearMin : $yearNow;
        $listYear = range($startYear, $yearNext);

        return $listYear;
    }
    public function saveFile(UploadedFile $file)
    {

        $fileName = $this->createFilename($file);
        // Group files by mime type
        $mime = str_replace('/', '-', $file->getMimeType());
        // Group files by the date (week
        $dateFolder = date("YmW");

        // Build the file path
        $filePath = MOVIE_PATH_UPLOAD_FILE . "/{$dateFolder}";

        $fileData = File::create([
            'file_path' => $file->storeAs($filePath, $fileName),
            'file_name' => $file->getClientOriginalName(),
            "file_extension" => $file->getClientOriginalExtension(),
            "file_size" => $file->getSize(),
            "file_sys_disk" => 'public',
            "expired_at" => null,
            "file_url" => Storage::url($filePath . '/' . $fileName)
        ]);

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

    public function getPocketBooks($param)
    {
        return $this->model::with('file')->where('year', $param['year'])->orderBy('position', 'asc')->get();
    }

    public function findPocketBooks($id)
    {
        return $this->model::with('file')->find($id);
    }

    public function updatePositions($listPocketBook)
    {
        foreach ($listPocketBook as $index => $id) {
            $this->model::where('id', $id)->update(['position' => $index]);
        }
        return true;
    }

}
