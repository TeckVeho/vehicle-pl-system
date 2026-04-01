<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2024-05-08
 */

namespace App\Http\Controllers\Api;

use App\Exports\ExportAllMovieWatching;
use App\Exports\UserWatcingMovieExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\MoviesRequest;
use App\Models\Movies;
use App\Models\MovieSchedules;
use App\Repositories\Contracts\MoviesRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\MoviesResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class MoviesController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(MoviesRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/movies",
     *   tags={"Movies"},
     *   summary="List movies",
     *   operationId="movies_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="title",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="tag",
     *     in="query",
     *     example="[1,2,3]",
     *     @OA\Schema(
     *      format="object",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(MoviesRequest $request)
    {
        $data = $this->repository->listMovies($request->all());
        return $this->responseJson(200, BaseResource::collection($data));
    }

    /**
     * @OA\Post(
     *   path="/api/movies",
     *   tags={"Movies"},
     *   summary="Add new movies",
     *   operationId="movies_create",
     *   @OA\RequestBody(
     *      required = true,
     *      description = "List department id",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="file_id", description="file id", format="integer", example=1),
     *           @OA\Property(property="file_length", description="file_length", format="string", example="03:11"),
     *           @OA\Property(property="thumbnail_file_id", description="thumbnail file id", format="integer", example=2),
     *           @OA\Property(property="title", description="title", format="string", example="title"),
     *           @OA\Property(property="content", description="content", format="string", example="content"),
     *           @OA\Property(property="list_tags", description="list tags", format="object", example="[1,2,3]"),
     *      ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(MoviesRequest $request)
    {
        try {
            $data = $this->repository->storeMovie($request->all());
            return $this->responseJson(200, new MoviesResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/movies/{id}",
     *   tags={"Movies"},
     *   summary="Detail Movies",
     *   operationId="movies_show",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $movie = $this->repository->showMovie($id);
            return $this->responseJson(200, new BaseResource($movie));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/movies/{id}",
     *   tags={"Movies"},
     *   summary="Update Movies",
     *   operationId="movies_update",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          example={
     *              "file_id":1, "thumbnail_file_id":2,"title":"test",
     *              "content":"test","list_tags":{1,2,3},"file_length":"03:11"
     *          },
     *          @OA\Schema(
     *            required={"file_id","title","content"},
     *            @OA\Property(
     *              property="file_id",
     *              format="integer",
     *            ),
     *            @OA\Property(
     *              property="file_length",
     *              format="string",
     *            ),
     *            @OA\Property(
     *              property="thumbnail_file_id",
     *              format="integer",
     *            ),
     *            @OA\Property(
     *              property="title",
     *              format="string",
     *            ),
     *            @OA\Property(
     *              property="content",
     *              format="string",
     *            ),
     *            @OA\Property(
     *              property="list_tags",
     *              format="object",
     *            ),
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":  "............."}}
     *     ),
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Access Deny permission",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Access Deny permission"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(MoviesRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->updateMovie($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/movies/{id}",
     *   tags={"Movies"},
     *   summary="Delete Movies",
     *   operationId="movies_delete",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":"Send request success"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $data = $this->repository->deleteMovie($id);
        return $this->responseJson(200, new MoviesResource($data));
    }

    /**
     * @OA\Post(
     *   path="/api/movies/upload-file",
     *   tags={"Movies"},
     *   summary="upload-file movies",
     *   operationId="movies-upload-file",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="file",
     *                   property="file",
     *                   type="string",
     *                   format="binary",
     *               ),
     *           )
     *       )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function uploadFile(FileReceiver $receiver)
    {
        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }
        // receive the file
        $save = $receiver->receive();

        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            // save the file and return any response you need
            return $this->repository->saveFile($save->getFile());
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();
        return response()->json([
            "done" => $handler->getPercentageDone()
        ]);
    }

    /**
     * @OA\Put(
     *   path="/api/movies/update-position",
     *   tags={"Movies"},
     *   summary="Update position",
     *   operationId="movies_update_position",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="list_position", type="array",
     *                  @OA\Items(type="integer"),
     *              ),
     *            @OA\Property(property="page", type="integer"),
     *          )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *    @OA\Response(
     *     response=403,
     *     description="Access Deny permission",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Access Deny permission"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function updatePosition(MoviesRequest $request)
    {
        try {
            $data = $this->repository->updatePositionMovie($request->all());
            return $this->responseJson(200, new MoviesResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/movies/{id}/loop-enabled",
     *   tags={"Movies"},
     *   summary="Update movie loop enabled flag",
     *   operationId="movies_update_loop_enabled",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="is_loop_enabled", type="boolean", example=true)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=404, description="Movie not found"),
     *   security={{"auth": {}}},
     * )
     */
    public function updateLoopEnabled(MoviesRequest $request, $id)
    {
        try {
            $data = $this->repository->updateLoopEnabled($id, $request->input('is_loop_enabled'));
            return $this->responseJson(200, new BaseResource($data));
        } catch (\Exception $e) {
            return $this->responseJson(404, null, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *   path="/api/movies/mobile",
     *   tags={"MoviesMobile"},
     *   summary="Movies Mobile",
     *   operationId="movies_mobile",
     *     @OA\Parameter(
     *     name="title",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="tag",
     *     in="query",
     *     example="1",
     *     @OA\Schema(
     *      format="integer",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMovieMobile(MoviesRequest $request)
    {
        try {
            $movie = $this->repository->showMovieMobile($request->all());
            return $this->responseJson(200, new BaseResource($movie));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *   path="/api/movies/mobile/like",
     *   tags={"MoviesMobile"},
     *   summary="Add new movies mobilelike",
     *   operationId="movies_mobile_like_create",
     *   @OA\Parameter(name="movie_id", in="query", required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *  @OA\Parameter(name="user_id", in="query", required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Parameter(name="like_or_dislike", in="query", required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function storeMovieLike(MoviesRequest $request)
    {
        try {
            $data = $this->repository->storeMovieLike($request->all());
            return $this->responseJson(200, new MoviesResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/movies/mobile/update-like",
     *   tags={"MoviesMobile"},
     *   summary="update movies mobile like",
     *   operationId="movies_mobile_like_update",
     *   @OA\Parameter(name="movie_user_like_id", in="query", required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Parameter(name="like_or_dislike", in="query", required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function updateMovieLike(MoviesRequest $request)
    {
        try {
            $data = $this->repository->updateMovieLike($request->all());
            return $this->responseJson(200, new MoviesResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/movies/mobile/show-like",
     *   tags={"MoviesMobile"},
     *   summary="Show Movies Mobile Like",
     *   operationId="movies_show_mobile_like",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *     @OA\Parameter(
     *     name="user_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="movie_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMovieLike(MoviesRequest $request)
    {
        try {
            $movie = $this->repository->showMovieLike($request->all());
            return $this->responseJson(200, new BaseResource($movie));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/movies/mobile/{id}",
     *   tags={"MoviesMobile"},
     *   summary="Show Movies Mobile",
     *   operationId="movies_show_mobile_",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="user_id",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMovieMobileDetail($id, MoviesRequest $request)
    {
        $data = $this->repository->showMovieMobileDetail($id, $request->all());
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Post(
     *   path="/api/movies/mobile/watching",
     *   tags={"MoviesMobile"},
     *   summary="Add new movies mobile watching",
     *   operationId="movies_mobile_watching",
     *   @OA\Parameter(name="movie_id", in="query", required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *  @OA\Parameter(name="user_id", in="query", required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createMovieWatching(MoviesRequest $request)
    {
        try {
            $data = $this->repository->createMovieWatching($request->all());
            return $this->responseJson(200, new MoviesResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/movies/mobile/watching/{id}",
     *   tags={"MoviesMobile"},
     *   summary="Show Movies Mobile watching",
     *   operationId="movies_show_mobile_watching",
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUserWatchingMovieMobile($id)
    {
        $data = $this->repository->showUserWatchingMovieMobile($id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Post(
     *   path="/api/movies/store-movie-schedule",
     *   tags={"Movies"},
     *   summary="Store Movie Detail",
     *   operationId="store_movie_detail",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="list_movie_viewer",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer"
     *                     ),
     *                     @OA\Property(
     *                         property="date",
     *                         type="object",
     *                         format="date"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="list_dates",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="date",
     *                         type="object",
     *                         format="date"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="range",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="date",
     *                         type="object",
     *                         format="date"
     *                     )
     *                 )
     *             ),
     *             example={
     *                 "list_movie_viewer": {
     *                   {
     *                     "date":"2024-07-01",
     *                      "data":{
     *                          {
     *                              "movie_id":1,
     *                              "time": "15:00",
     *                              "assign_type":2,
     *                              "from":"2024-07-01",
     *                              "to":"2024-07-05"
     *                          },{
     *                              "movie_id":2,
     *                              "time": "16:00",
     *                              "assign_type":1,
     *                              "from":"2024-07-01",
     *                              "to":"2024-07-01"
     *                          }
     *                      }
     *                    },{
     *                     "date":"2024-07-06",
     *                      "data":{
     *                          {
     *                              "movie_id":1,
     *                              "time": "15:00",
     *                              "assign_type":2,
     *                              "from":"2024-07-06",
     *                              "to":"2024-07-09"
     *                          },{
     *                              "movie_id":2,
     *                              "time": "16:00",
     *                              "assign_type":1,
     *                              "from":"2024-07-06",
     *                              "to":"2024-07-06"
     *                          }
     *                      }
     *                    }
     *                  },
     *                  "range": {"2024-07-01","2024-08-06"},
     *             }
     *         )
     *     )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *    @OA\Response(
     *     response=403,
     *     description="Access Deny permission",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Access Deny permission"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function storeMovieSchedules(MoviesRequest $request)
    {
        $data = $this->repository->storeMovieSchedules($request->all());
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Get(
     *   path="/api/movies/schedule",
     *   tags={"Movies"},
     *   summary="Show Movies Schedule",
     *   operationId="movies_show_schedule",
     *   @OA\Parameter(
     *      name="date",
     *      in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMovieSchedules(MoviesRequest $request)
    {
        $data = $this->repository->showMovieSchedules($request->all());
        return $this->responseJson(200, $data);
    }

    /**
     * @OA\Put(
     *   path="/api/movies/delete-schedule",
     *   tags={"Movies"},
     *   summary="Delete Schedule",
     *   operationId="delete_schedule",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="list_date", type="array",
     *                  @OA\Items(type="string"),
     *              ),
     *          )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name": "......"}}
     *     )
     *   ),
     *    @OA\Response(
     *     response=403,
     *     description="Access Deny permission",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":403,"message":"Access Deny permission"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteMovieSchedules(MoviesRequest $request)
    {
        try {
            $data = $this->repository->deleteMovieSchedules($request->all());
            return $this->responseJson(200, new MoviesResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/movies/noti",
     *   tags={"Movies"},
     *   summary="update send noti app",
     *   operationId="call_back_send_noti",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSendNoti()
    {
        $now = Carbon::now()->format('Y-m-d');
        $isNotSendNoti = 0;
        $movieSchedule = MovieSchedules::where('date', $now)
            ->where('time', '<', Carbon::now()->format('H:i'))
            ->where('is_send_noti', $isNotSendNoti)
            ->orderBy('id','DESC')
            ->first();
        if ($movieSchedule) {
            $movieSchedule->is_send_noti  = 1 ;
            $movieSchedule->save();
        }
        return $this->responseJson(200, null, "update success");
    }

    /**
     * @OA\Get(
     *   path="/api/movies/show-user-watch-movie",
     *   tags={"Movies"},
     *   summary="Show user watch movie",
     *   operationId="show_user_watch_movie",
     *     @OA\Parameter(
     *      name="date",
     *      in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{"id": 1,"name":"......"}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *     security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
     public function showUserWatchMovie(MoviesRequest $request)
     {
         $data = $this->repository->showUserWatchMovie($request->all());
         return $this->responseJson(200, new MoviesResource($data));
     }

    /**
     * @OA\Get(
     *   path="/api/movies/dowload-user-watching",
     *   tags={"Movies"},
     *   summary="dowload User Watching Movies",
     *   operationId="dowload_user_watching_movie",
     *     @OA\Parameter(
     *      name="movie_id",
     *      in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
     public function downloadUserWatchingMovie(MoviesRequest $request)
     {
         $params = $request->all();
         $data = $this->repository->downloadUserWatchingMovie($params);
         $title = Movies::select('title')->where('id', $params['movie_id'])->first();
         $fileName = $title->title . '_視聴結果.csv';
         return Excel::download(
             new UserWatcingMovieExport($data),
             $fileName,
             null,
             ['Content-Type' => 'application/octet-stream; charset=UTF-8', 'Content-Transfer-Encoding' => 'Binary', 'Charset' => 'UTF-8']
         );
     }


    /**
     * @OA\Get(
     *   path="/api/movies/all-watching-movie-list",
     *   tags={"Movies"},
     *   summary="Get all Watching Movies list",
     *   operationId="get_all_watching_movie_list",
     *   @OA\Parameter(
     *      name="movie_ids",
     *      in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *      name="title",
     *      in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"title": "..........."}}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllWatchingMovieList(Request $request)
    {
        $request->validate([
            'movie_ids' => 'nullable|string',
            'title' => 'nullable|string|max:255',
        ]);

        $data = $this->repository->getAllWatchingMovieList($request->all());
        return $this->responseJson(200, BaseResource::collection($data));
    }

   /**
     * @OA\Get(
     *   path="/api/movies/download-all-watching-movie",
     *   tags={"Movies"},
     *   summary="download all Watching Movies",
     *   operationId="download_all_watching_movie",
     *   @OA\Parameter(
     *      name="movie_ids",
     *      in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *      name="title",
     *      in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *      name="start_date",
     *      in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *      name="end_date",
     *      in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function downloadAllWatchingMovie(Request $request)
    {
        $request->validate([
            'movie_ids' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $data = $this->repository->downloadAllWatchingMovie($request->all());
        $fileName = '視聴者データ.xlsx';
        return Excel::download(
            new ExportAllMovieWatching($data),
            $fileName,
            null,
            ['Content-Type' => 'application/octet-stream; charset=UTF-8', 'Content-Transfer-Encoding' => 'Binary', 'Charset' => 'UTF-8']
        );
    }
}
