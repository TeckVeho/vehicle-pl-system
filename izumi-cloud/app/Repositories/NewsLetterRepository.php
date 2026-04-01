<?php
/**
 * Created by VeHo.
 * Year: 2026-03-30
 */

namespace Repository;

use App\Models\NewsLetter;
use App\Repositories\Contracts\NewsLetterRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use App\Jobs\SaveFileToS3Job;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class NewsLetterRepository extends BaseRepository implements NewsLetterRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param NewsLetter $model
       */

    public function model()
    {
        return NewsLetter::class;
    }


    public function index($params)
    {

        $perPage = Arr::get($params, 'per_page', 50);
        $page = Arr::get($params, 'page', 1);
        $year = Arr::get($params, 'year', null);
        $month = Arr::get($params, 'month', null);
        $title = Arr::get($params, 'title', null);
        $status = Arr::get($params, 'status', null);

        $query = $this->model->query();

        if($year) {
            $query->where('year', $year);
        }
        if($month) {
            $query->where('month', $month);
        }
        if($title) {
            $query->where('title', 'like', '%' . $title . '%');
        }
        if($status) {
            $query->where('status', $status);
        }
        $data = $query->with(['file','createdBy','updatedBy'])
            ->orderBy('position', 'ASC')
            ->paginate($perPage, ['*'], 'page', $page);
        return $data;
    }

    public function ListNewsLetterMobile($params)
    {

        $year = Arr::get($params, 'year', null);
        $month = Arr::get($params, 'month', null);
        $status = Arr::get($params, 'status', null);
        $user_id = Arr::get($params, 'user_id', null);
        $query = $this->model->query();
        
        if($user_id) {
            $query->where('created_by', $user_id);
        }
        
        if($year) {
            $query->where('year', $year);
        }

        if($month) {
            $query->where('month', $month);
        }
        if($status) {
            $query->where('status', $status);
        }

        $data = $query->with(['file','createdBy','updatedBy'])
            ->orderBy('position', 'ASC')
            ->get();
        return $data;
    }

    public function show($id)
    {
        return $this->model->with(['file','createdBy','updatedBy'])->find($id);
    }

    public function storeNewsLetter($attributes)
    {

        $user = Auth::user();
        $position = $this->model->orderBy('position', 'DESC')->first();
        $fileId = Arr::get($attributes, 'file_id', null);
        $year = Arr::get($attributes, 'year', null);
        $month = Arr::get($attributes, 'month', null);
        $title = Arr::get($attributes, 'title', null);
        $status = Arr::get($attributes, 'status', null);
        $newsLetter = $this->model->create([
            'title' => $title,
            'status' => $status,
            'position' => $position ? $position->position + 1 : 1,
            'file_id' => $fileId,
            'year' => $year,
            'month' => $month,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        if($fileId && $newsLetter) {
            SaveFileToS3Job::dispatch($fileId, 'news_letter')->delay(now()->addMinute());
        }
        return $newsLetter;
    }

    public function updateNewsLetter($attributes, $id)
    {
        $user = Auth::user();
        $newsLetter = $this->model->where('id', $id)->first();
        $title = Arr::get($attributes, 'title', null);
        $status = Arr::get($attributes, 'status', null);
        $year = Arr::get($attributes, 'year', null);
        $month = Arr::get($attributes, 'month', null);
        if($newsLetter) {
            if($title) {
                $newsLetter->title = $title;
            }
            if($status) {
                $newsLetter->status = $status;
            }
            if($year) {
                $newsLetter->year = $year;
            }
            if($month) {
                $newsLetter->month = $month;
            }
            $newsLetter->updated_by = $user->id;
            $newsLetter->save();
        }
        return $newsLetter;
    }

    public function saveFile(UploadedFile $file)
    {

        $fileName = $this->createFilename($file);
        // Group files by mime type
        $mime = str_replace('/', '-', $file->getMimeType());
        // Group files by the date (week
        $dateFolder = date("YmW");

        // Build the file path
        $filePath = NEWS_LETTER_PATH_UPLOAD_FILE . "/{$dateFolder}";

        $fileData = File::create([
            'file_path' => $file->storeAs($filePath, $fileName),
            'file_name' => $file->getClientOriginalName(),
            "file_extension" => $file->getClientOriginalExtension(),
            "file_size" => $file->getSize(),
            "file_sys_disk" => 'public',
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

    public function updatePosition($data)
    {
        $listPositions = Arr::get($data, 'list_position');
        foreach($listPositions as $key => $value) {
            $this->model::where('id', $value)->update(['position' => $key]);
        }
        return $listPositions;
    }

}
