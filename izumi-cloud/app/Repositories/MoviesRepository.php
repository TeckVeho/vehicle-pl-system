<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2024-05-08
 */

namespace Repository;

use App\Jobs\SaveFileToS3Job;
use App\Jobs\SendNotiDeleteMovieNow;
use App\Models\File;
use App\Models\MovieSchedules;
use App\Models\Movies;
use App\Models\MovieUserLike;
use App\Models\MovieWatching;
use App\Models\User;
use App\Repositories\Contracts\MoviesRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Collection;

class MoviesRepository extends BaseRepository implements MoviesRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param Movies $model
     */

    public function model()
    {
        return Movies::class;
    }

    public function listMovies($params)
    {
        $title = Arr::get($params, 'title');
        $tags = json_decode(Arr::get($params, 'tag'));
        $perPage = Arr::get($params, 'per_page', 10);

        $movieList = $this->model->with([
            'movieFile' => function($query) {
                $query->select('id', 'file_url');
            },

            'thumbnail' => function($query) {
                $query->select('id', 'file_url');
            },
        ]);

        if($title) {
            $movieList = $movieList->where('title', 'LIKE', '%' . $title . '%')
                ->orWhere('content', 'LIKE', '%' . $title . '%');
        }

        if($tags) {
            $movieList = $movieList->where(function ($query) use ($tags) {
                foreach ($tags as $tag) {
                    $query->orWhereRaw("JSON_CONTAINS(tag, '[{$tag}]')");
                }
            });
        }
        $movieList = $movieList->orderBy('position', 'ASC')->paginate($perPage);
        $movieList->getCollection()->transform(function ($item) {
            $item->tag = json_decode($item->tag);
            return $item;
        });
        return $movieList;
    }

    public function showMovie($id)
    {
        $movie = $this->model->with([
            'movieFile' => function($query) {
                $query->select('id', 'file_url', 'file_name');
            },
            'thumbnail' => function($query) {
                $query->select('id', 'file_url', 'file_name');
            },
        ])->find($id);

        if (!$movie) {
            return ['error' => "does not exist"];
        }
        $movie->tag = json_decode($movie->tag);
        return $movie;
    }

    public  function storeMovie($dataMovie)
    {
        $movieDESC = $this->model->orderBy('position','DESC')->first();
        if($movieDESC) {
            $dataMovie['position'] = $movieDESC->position + 1;
        }
        $tags = Arr::get($dataMovie, 'list_tags');
        $dataMovie['tag'] = json_encode($tags);
        $movie = $this->model->create($dataMovie);
        $fileId = Arr::get($dataMovie, 'file_id');
        $thumbnailId = Arr::get($dataMovie, 'thumbnail_file_id');
        $file = File::where('id', $fileId)->first();
        $thumbnail = File::where('id', $thumbnailId)->first();
        if($movie && $file) {
            $file->expired_at = null;
            $file->save();
            SaveFileToS3Job::dispatch($fileId, 'movie')->delay(now()->addMinute());

            if ($thumbnail) {
                $thumbnail->expired_at = null;
                $thumbnail->save();
                SaveFileToS3Job::dispatch($thumbnailId, 'movie')->delay(now()->addMinute());
            }
        }
        return $movie;
    }

    public function updateMovie($dataMovie, $id)
    {
        $movie = $this->model->find($id);
        $tags = Arr::get($dataMovie, 'list_tags');
        $dataMovie['tag'] = json_encode($tags);
        $thumbnailId = Arr::get($dataMovie, 'thumbnail_file_id');
        if(!$thumbnailId) {
            $dataMovie['thumbnail_file_id'] = null;
        }

        $movieUpdate = $this->update($dataMovie, $id);
        $fileId = Arr::get($dataMovie, 'file_id');

        if($fileId != $movie->file_id) {
            $file = File::where('id', $fileId)->first();
            $file->expired_at = null;
            $file->save();
            SaveFileToS3Job::dispatch($fileId, 'movie')->delay(now()->addMinute());
        }

        if($thumbnailId && $thumbnailId != $movie->thumbnail_file_id) {
            $thumbnail = File::where('id', $thumbnailId)->first();
            $thumbnail->expired_at = null;
            $thumbnail->save();
            SaveFileToS3Job::dispatch($thumbnailId, 'movie')->delay(now()->addMinute());
        }
        return $movieUpdate;

    }

    public function updatePositionMovie($dataMovie)
    {
        try {
            $listPositions = Arr::get($dataMovie, 'list_position');
            $pages = Arr::get($dataMovie, 'page');
            $per_page = Arr::get($dataMovie, 'per_page');
            if ($pages > 1) {
                $positionPage = $per_page * ($pages - 1);
                foreach ($listPositions as $key => $value) {
                    $this->model::where('id', $value)->update(['position' => $positionPage + $key]);
                }
            } else {
                foreach ($listPositions as $key => $value) {
                    $this->model::where('id', $value)->update(['position' => $key]);
                }
            }
            return $listPositions;
        } catch(Exception $e) {
            return ['error' =>$e->getMessage() ];
        }
    }

    public function deleteMovie($id)
    {
        $now = Carbon::now()->format('Y-m-d');
        $movieDetail = MovieSchedules::where('date', $now)->first();

        if ($movieDetail) {
            return ['error'=> 'cannot be deleted'];
        }

        $movie = $this->model::where('id', $id)->with([
            'movieFile' => function($query) {
                $query->select('id', 'file_path', 'file_sys_disk');
            },
            'thumbnail' => function($query) {
                $query->select('id', 'file_path', 'file_sys_disk');
            },
        ])->first();

        if ($movie->movieFile) {
            if ($movie->movieFile->file_sys_disk == "s3") {
                if (Storage::disk('s3')->delete($movie->movieFile->file_path)) {
                    // $f->delete();
                    Log::info("Deleted file S3 DISK: " . $movie->movieFile->file_path);
                }
            } else {
                // $f->delete();
                Storage::disk('public')->delete($movie->movieFile->file_path);
            }
        }

        if($movie->thumbnail) {
            if ($movie->thumbnail->file_sys_disk == "s3") {
                if (Storage::disk('s3')->delete($movie->thumbnail->file_path)) {
                    // $f->delete();
                    Log::info("Deleted file S3 DISK: " . $movie->movieFile->file_path);
                }
            } else {
                // $f->delete();
                Storage::disk('public')->delete($movie->thumbnail->file_path);
            }
        }
        $movie->delete();
        return ['messages' => 'delete success'];
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
            "expired_at" => Carbon::now()->addHours(24),
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

    public function showMovieMobile($params)
    {
        $title = Arr::get($params, 'title');
        $tags = json_decode(Arr::get($params, 'tag'));

        $movieList = $this->model->with([
            'movieFile' => function($query) {
                $query->select('id', 'file_url', 'file_name');
            },

            'thumbnail' => function($query) {
                $query->select('id', 'file_url', 'file_name');
            },
        ]);

        if($title) {
            $movieList = $movieList->where('title', 'LIKE', '%' . $title . '%')
                ->orWhere('content', 'LIKE', '%' . $title . '%');
        }

        if($tags) {
            $movieList = $movieList->where(function ($query) use ($tags) {
                $query->orWhereRaw("JSON_CONTAINS(tag, '[{$tags}]')");
            });
        }
        $movieList = $movieList->orderBy('position', 'ASC')->get();
        $isWatchingOrLike = 1;
        $movieList->transform(function ($item)  use ($isWatchingOrLike){

            $listUserWatching = MovieWatching::where('movie_id', $item->id)
                ->whereDate('date', Carbon::now())
                ->where('is_watching', $isWatchingOrLike)
                ->pluck('user_id');
            $listUserLikeList = MovieWatching::where('movie_id', $item->id)
                ->whereDate('date', Carbon::now())
                ->where('is_like_list', $isWatchingOrLike)
                ->pluck('user_id');
            $item->is_watching = in_array(auth()->user()->id, $listUserWatching->toArray()) ? $isWatchingOrLike : 0;
            $item->is_like_list = in_array(auth()->user()->id, $listUserLikeList->toArray()) ? $isWatchingOrLike : 0;
            $item->list_user_id_is_watching = $listUserWatching->toArray();
            $item->tag = json_decode($item->tag);
            return $item;
        });
        return $movieList;
    }

    public function storeMovieLike($params)
    {
        $params['date'] = Carbon::now()->format('Y-m-d');
        $movieUserLike = MovieUserLike::create($params);
        return $movieUserLike;
    }

    public function updateMovieLike($params)
    {
        $like_or_dislike = Arr::get($params, 'like_or_dislike');
        $movieUserLikeId = Arr::get($params, 'movie_user_like_id');
        $now = Carbon::now()->format('Y-m');

        $movieUserLike = MovieUserLike::whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$now])
            ->where('id', $movieUserLikeId)
            ->first();
        if ($movieUserLike->like_or_dislike != $like_or_dislike) {
            $movieUserLike->like_or_dislike = $like_or_dislike;
            $movieUserLike->save();
        }
        return $movieUserLike;
    }

    public function showMovieLike($params)
    {
        $userId = Arr::get($params, 'user_id');
        $movieId = Arr::get($params,'movie_id');
        $now = Carbon::now()->format('Y-m');
        $movieUserLike = MovieUserLike::select('like_or_dislike')->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$now])
            ->where('user_id', $userId)
            ->where('movie_id', $movieId)
            ->first();
        return $movieUserLike;
    }

    public function showMovieMobileDetail($id, $params)
    {
        $userId = Arr::get($params, 'user_id');
        $now = Carbon::now()->format('Y-m');
        $movie = $this->model::with([
            'movieFile' => function($query) {
                $query->select('id', 'file_url', 'file_name');
            },
            'thumbnail' => function($query) {
                $query->select('id', 'file_url', 'file_name');
            },
            'movieUserLike' => function($query) use($userId, $now){
                $query->select('movie_id', 'user_id', 'like_or_dislike', 'date')
                    ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$now])
                    ->where('user_id', $userId)->first();
            }])->where('id', $id)->first();
        return $movie;
    }

    public function createMovieWatching($params)
    {
        $isLikeApp = Arr::get($params, 'is_like_app');
        $isLikeList = Arr::get($params, 'is_like_list');
        $movieWatchings = MovieWatching::whereDate('date', Carbon::now()->format('Y-m-d'))
            ->where('movie_id', $params['movie_id'])
            ->where('user_id', $params['user_id'])
            ->first();
        if($isLikeApp && $isLikeApp == 1) {
            if($movieWatchings && $movieWatchings->is_like_app == 0) {
                $movieWatchings->is_like_app = 1;
                $movieWatchings->save();
            }
            if(!$movieWatchings) {
                $params['is_watching'] = 1;
                $params['date'] = Carbon::now()->format('Y-m-d');
                $params['time'] = Carbon::now()->format('H:i');
                $params['is_like_app'] = 1;
                $movieWatchingNow = MovieWatching::create($params);
                $params['date'] = Carbon::now()->addDay()->format('Y-m-d');
                $params['time'] = "00:00";
                $params['export_flag'] = 1;
                $movieWatchingNextDay = MovieWatching::create($params);
            }
        } else if($isLikeList && $isLikeList == 1){
            if($movieWatchings && $movieWatchings->is_like_list == 0){
                $movieWatchings->is_like_list = 1;
                $movieWatchings->save();
            }
            if(!$movieWatchings) {
                $params['is_watching'] = 1;
                $params['date'] = Carbon::now()->format('Y-m-d');
                $params['time'] = Carbon::now()->format('H:i');
                $params['is_like_list'] = 1;
                MovieWatching::create($params);
            }
        }

        return ['create movie watching success'];
    }

    public  function showUserWatchingMovieMobile($userId)
    {
        $now = Carbon::now()->format('Y-m-d');
        $movieSchedule = MovieSchedules::where('date', $now)
            ->where('time', '<', Carbon::now()->format('H:i:40'))
            ->orderBy('time', 'asc')
            ->pluck('movie_id');
        if ($movieSchedule->count() > 0) {
            $movies = $this->model::with([
                'movieFile:id,file_url,file_name',
                'thumbnail:id,file_url,file_name'
            ])->whereIn('id', $movieSchedule)->get();
            $movies = $movies->transform(function ($item){
                $listUserWatching = MovieWatching::where('movie_id', $item->id)
                    ->whereDate('date', Carbon::now())
                    ->where('is_watching', 1)
                    ->pluck('user_id');
                $is_like_app = MovieWatching::where('movie_id', $item->id)
                    ->whereDate('date', Carbon::now())
                    ->where('is_like_app', 1)
                    ->pluck('user_id');
                $is_like_list = MovieWatching::where('movie_id', $item->id)
                    ->whereDate('date', Carbon::now())
                    ->where('is_like_list', 1)
                    ->pluck('user_id');
                $item->is_watching = in_array(auth()->user()->id, $listUserWatching->toArray()) ? 1 : 0;
                $item->is_like_app = in_array(auth()->user()->id, $is_like_app->toArray()) ? 1 : 0;
                $item->is_like_list = in_array(auth()->user()->id, $is_like_list->toArray()) ? 1 : 0;
                return $item;
            });
            $movieShow = null;
            $movieWatchingShow = null;
            foreach ($movies as $key => $movie) {
                $movieWatching = MovieWatching::where([
                    ['user_id', '=', $userId],
                    ['movie_id', '=', $movie->id],
                    ['date', '=', $now],
                ])->first();

                if (!$movieWatching) {
                    $movieWatchingShow = $movieWatching;
                    $movieShow = $movie;
                    break; // Dừng vòng lặp khi tìm thấy kết quả phù hợp
                } else {
                    $movieShow = $movie;
                    $movieWatchingShow = $movieWatching;
                }
            }

            return [
                'is_watching' => $movieWatchingShow ? $movieWatchingShow->is_watching : 0,
                'is_like_app' => $movieWatchingShow ? $movieWatchingShow->is_like_app : 0,
                'is_like_list' => $movieWatchingShow ? $movieWatchingShow->is_like_list : 0,
                'movie' => $movieShow,
                'list_movies' => $movies
            ];
        } else {
            return [
                'is_watching' => 1,
                'movie' => null,
                'list_movies' => []
            ];
        }
    }

    public function storeMovieSchedules($params)
    {

        $listMovies = Arr::get($params, 'list_movie_viewer');
        $listDates = Arr::get($params, 'list_date');
        $range = Arr::get($params, 'range');
        $movieScheduleNow = MovieSchedules::where('date', Carbon::now()->format('Y-m-d'))->get();

        if (count($listMovies) > 0) {

            MovieSchedules::whereBetween('date', [$range[0], $range[1]])
                ->whereNotIn('date', $listDates)
                ->delete();
            foreach ($listMovies as $listMovie) {

                $listId = [];
                foreach ($listMovie['data'] as $value) {
                    $listId[] = $value['movie_id'];
                    $movieSchedules = MovieSchedules::where('date', $listMovie['date'])
                        ->where('movie_id', $value['movie_id'])
                        ->where('time', $value['time'])
                        ->first();
                    if(!$movieSchedules) {
                        MovieSchedules::create([
                            'movie_id' => $value['movie_id'],
                            'date' => Carbon::parse($listMovie['date'])->format("y-m-d"),
                            'time' => $value['time'] ? $value['time'] : "00:00",
                            'from' => $value['from'] ? $value['from'] : null,
                            'to' => $value['to'] ? $value['to'] : null,
                            'assign_type' => $value['assign_type'],
                            'auto_flag' => 0
                        ]);
                    }
                }
                if (count($listId) > 0) {
                    MovieSchedules::where('date', $listMovie['date'])
                        ->whereNotIn('movie_id', $listId)
                        ->delete();
                }
            }
            if ($movieScheduleNow->count() > 0) {
                $now = Carbon::now()->format('Y-m-d');
                if (!in_array($now, $listDates)) {
                    SendNotiDeleteMovieNow::dispatch()->delay(now()->addSeconds(20));
                }
            }
            return ['create movie viewer success'];
        } else if (count($listMovies) == 0){
            MovieSchedules::whereBetween('date', [$range[0], $range[1]])->delete();
            if ($movieScheduleNow->count() > 0) {
                SendNotiDeleteMovieNow::dispatch()->delay(now()->addSeconds(20));
            }
            return ["delete movie viewer success"];
        } else {
            return ["create movie viewer fail"];
        }

    }

    public function showMovieSchedules($params)
    {
        $date = Arr::get($params, 'date');
        $firstMonth = Carbon::parse($date)->firstOfMonth();
        $firstDate = $firstMonth->copy()->subDays(6)->format('Y-m-d');
        $endMonth =  Carbon::parse($date)->lastOfMonth();
        $endDate = $endMonth->copy()->addDays(6)->format('Y-m-d');
        $listMoviesViewers = MovieSchedules::select('id', 'movie_id', 'date', 'time', 'from', 'to','assign_type')
            ->with(['movie' => function ($query) {
                $query->select('id','title');
            }])
            ->whereBetween('date', [$firstDate, $endDate])
            ->get()
            ->groupBy('date');
        $result = [];

        foreach ($listMoviesViewers as $dateMovie => $items) {
            $items =  $items->transform(function ($item) {
                $item->time = Carbon::createFromFormat('H:i:s', $item->time)->format('H:i');
                $item->hour = (int)Carbon::createFromFormat('H:i', $item->time)->format('H');
                $item->minute = (int)Carbon::createFromFormat('H:i', $item->time)->format('i');
                $item->movie_title = $item->movie->title;
                return $item;
            });
            $result[] = [
                'date' => $dateMovie,
                'list_movie' => $items->makeHidden(['movie'])->toArray()
            ];
        }

        $listTitle = $this->model::select('id', 'title')->get();
        return [
            'data' => $result,
            'list_title' => $listTitle
        ];
    }

    public function deleteMovieSchedules($params)
    {
        $listDates = Arr::get($params, 'list_date');
        if ($listDates) {
            foreach ($listDates as $listDate) {
                $movieSchedules = MovieSchedules::where('date', $listDate)->first();
                if ($movieSchedules) {
                    $movieSchedules->delete();
                }
            }
        }
        return $listDates;
    }

    public function showUserWatchMovie($params)
    {
        $now = Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        $movieSchedule = MovieSchedules::select('date', 'time', 'movie_id', 'from', 'assign_type', 'to')
            ->with('movie', function ($query){
                $query->select('id','title');
            })
            ->where(function ($query) use ($currentDate, $currentTime) {
                $query->where('date', '<', $currentDate)
                    ->orWhere(function ($query) use ($currentDate, $currentTime) {
                        $query->where('date', '=', $currentDate)
                            ->where('time', '<', $currentTime);
                    });
            })
            ->orderBy('date', 'DESC')
            ->get();
        $watchingCounts = \DB::table('movie_watching')
            ->select('movie_id', 'date', \DB::raw('COUNT(*) as total'))
            ->whereIn('movie_id', $movieSchedule->pluck('movie_id')->unique())
            ->groupBy('movie_id', 'date')
            ->get()
            ->groupBy('movie_id')
            ->map(function($group) {
                return $group->keyBy('date');
            });

        $movieSchedule = $movieSchedule->transform(function ($item) use ($watchingCounts) {
            $timeFormatted = Carbon::createFromFormat('H:i:s', $item->time)->format('H:i');
            $movieWatchingByDate = $watchingCounts->get($item->movie_id);

            if ($movieWatchingByDate && isset($movieWatchingByDate[$item->date])) {
                $item->total = $movieWatchingByDate[$item->date]->total;
            } else {
                $item->total = 0;
            }

            $item->date = ($item->from ? $item->from : $item->date) . " " . $timeFormatted;
            return $item;
        });

        $filteredSchedule = $movieSchedule->groupBy(function($item) {
            if ($item->assign_type == 2) {
                return $item->movie_id . '-' . $item->from;
            }
            return $item->movie_id . '-' . $item->date;
        })->map(function(Collection $group) {
            if ($group->first()->assign_type == 2) {
                $grouped = $group->first();
                $grouped->total = $group->sum('total');
                return $grouped;
            }
            return $group->first();
        })->values();

        return $filteredSchedule;
    }

    public function downloadUserWatchingMovie($params)
    {
        $now = Carbon::now()->format('Y-m-d');
        $from = Arr::get($params, 'from');
        $to = Arr::get($params, 'to');
        $date = Arr::get($params, 'date');
        $datas = MovieWatching::select('date', 'user_id', 'movie_id', 'time')
            ->with('movie', function ($query){
                $query->select('id', 'title');
            })->where('export_flag', 0);
        if ($from && $to) {
            $datas = $datas->whereBetween('date', [$from, $to]);
        }

        if ($date) {
            $datas = $datas->whereDate('date', Carbon::parse($date)->format('Y-m-d'))
                ->where('time','>=', Carbon::parse($date)->format('H:i'));
        }

        $datas = $datas->where('movie_id', $params['movie_id'])
            ->get();

        $datas->transform(function ($item) {
            $user = User::with('department')->where('id', $item->user_id)->first();
            $item->user_name = $user ? $user->name : null;
            $item->user_department = $user ? $user->department ? $user->department->name : '' : '';
            $item->date =   Carbon::parse($item->date)->format("Y-m-d") . " " . ($item->time ? $item->time : '00:00');
            return $item;
        });

        return $datas;
    }


    public function getAllWatchingMovieList($params)
    {
        $query = Movies::select('id', 'title');

        if ($movieIds = Arr::get($params, 'movie_ids')) {
            $movieIdsArray = array_filter(array_map('trim', explode(',', $movieIds)));
            $query->whereIn('id', $movieIdsArray);
        }

        if ($title = Arr::get($params, 'title')) {
            $query->where('title', 'like', "%{$title}%");
        }

        return $query->get();
    }

    public function downloadAllWatchingMovie($params)
    {
        $query = Movies::select('id', 'title')->whereNotNull('title');

        if ($movieIds = Arr::get($params, 'movie_ids')) {
            $movieIdsArray = array_filter(array_map('trim', explode(',', $movieIds)));
            $query->whereIn('id', $movieIdsArray);
        }

        if ($title = Arr::get($params, 'title')) {
            $query->where('title', 'like', "%{$title}%");
        }

        $startDate = Arr::get($params, 'start_date');
        $endDate = Arr::get($params, 'end_date');

        $datas = $query->with(['movieWatching' => function ($query) use ($startDate, $endDate) {
                $query->where('export_flag', 0);
                if ($startDate && $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->where('date', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('date', '<=', $endDate);
                }
                $query->select('id', 'movie_id', 'user_id', 'date', 'time')
                    ->with([
                        'movie:id,title',
                        'user:id,name,department_code',
                        'user.department:id,name'
                    ]);
            }])
            ->get();
        return $datas;
    }

    public function updateLoopEnabled($id, $isLoopEnabled)
    {
        $movie = $this->model->find($id);

        if (!$movie) {
            throw new Exception("Movie not found");
        }

        $movie->is_loop_enabled = $isLoopEnabled;
        $movie->save();

        return $movie;
    }
}
