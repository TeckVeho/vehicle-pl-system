<?php
/**
 * Created by VeHo.
 * Year: 2026-03-30
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsLetterRequest;
use App\Repositories\Contracts\NewsLetterRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\NewsLetterResource;
use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class NewsLetterController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(NewsLetterRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/news-letter",
     *   tags={"NewsLetter"},
     *   summary="List NewsLetter",
     *   operationId="news_letter_index",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
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
     *   @OA\Parameter(
     *     name="year",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="month",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="title",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="status",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
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
    public function index(NewsLetterRequest $request)
    {
        $data = $this->repository->index($request->all());
        return $this->responseJson(200, new NewsLetterResource($data));
    }

    /**
     * @OA\Post(
     *   path="/api/news-letter",
     *   tags={"NewsLetter"},
     *   summary="Add new NewsLetter",
     *   operationId="news_letter_create",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          example={"title":"string", "file_id":1, "status":1, "year":2026, "month":3},
     *          @OA\Schema(
     *            required={"title", "file_id", "status", "year", "month"},
     *            @OA\Property(
     *              property="title",
     *              format="string",
     *            ),
     *            @OA\Property(
     *              property="file_id",
     *              format="integer",
     *            ),
     *            @OA\Property(
     *              property="status",
     *              format="integer",
     *            ),
     *            @OA\Property(
     *              property="year",
     *              format="year",
     *            ),
     *            @OA\Property(
     *              property="month",
     *              format="month",
     *            ),
     *       ),
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
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(NewsLetterRequest $request)
    {
        try {
            $data = $this->repository->storeNewsLetter($request->all());
            return $this->responseJson(200, new NewsLetterResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/news-letter/{id}",
     *   tags={"NewsLetter"},
     *   summary="Detail NewsLetter",
     *   operationId="news_letter_show",
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
            $data = $this->repository->show($id);
            return $this->responseJson(200, new NewsLetterResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *   path="/api/news-letter/{id}",
     *   tags={"NewsLetter"},
     *   summary="Update NewsLetter",
     *   operationId="news_letter_update",
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
     *          example={"title":"string", "file_id":1, "status":1, "year":2026, "month":3},
     *          @OA\Schema(
     *            required={"title", "file_id", "status", "year", "month"},
     *            @OA\Property(
     *              property="title",
     *              format="string",
     *            ),
     *            @OA\Property(
     *              property="file_id",
     *              format="integer",
     *            ),
     *            @OA\Property(
     *              property="status",
     *              format="integer",
     *            ),
     *            @OA\Property(
     *              property="year",
     *              format="integer",
     *            ),
     *            @OA\Property(
     *              property="month",
     *              format="integer",
     *            ),
     *         ),
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
    public function update(NewsLetterRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->updateNewsLetter($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/news-letter/{id}",
     *   tags={"NewsLetter"},
     *   summary="Delete NewsLetter",
     *   operationId="news_letter_delete",
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
        $this->repository->delete($id);
        return $this->responseJson(200, null, trans('messages.mes.delete_success'));
    }

    /**
     * @OA\Post(
     *   path="/api/news-letter/upload-file",
     *   tags={"NewsLetter"},
     *   summary="upload-file news letter ",
     *   operationId="news-letter-upload-file",
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
     *               )
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
        $year = request()->input('year');
        $tag = request()->input('tag');
        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }
        // receive the file
        $save = $receiver->receive();

        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            // save the file and return any response you need
            $file = $this->repository->saveFile($save->getFile());
            if($file){
                return $file;
            } else {
                throw new UploadMissingFileException();
            }
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();
        return response()->json([
            "done" => $handler->getPercentageDone()
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/news-letter/update-position",
     *   tags={"NewsLetter"},
     *   summary="Update position",
     *   operationId="news_letter_update_position",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          example={"list_position":{1,2,3}},
     *          @OA\Schema(
     *              required={"list_position"},
     *              @OA\Property(property="list_position", type="array",
     *                  @OA\Items(type="integer"),
     *              ),
     *          )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{1,2,3}}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function updatePosition(Request $request)
    {
        $data = $this->repository->updatePosition($request->all());
        return $this->responseJson(200, new BaseResource($data));
    }


    /**
     * @OA\Get(
     *   path="/api/news-letter-mobile",
     *   tags={"NewsLetterMobile"},
     *   summary="List NewsLetter",
     *   operationId="news_letter_index_mobile",
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"data":{{"id": 1,"name": "..........."}}}
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="year",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="month",
     *     in="query",
     *     @OA\Schema(
     *      type="integer",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="title",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
     *   ),
     *   @OA\Parameter(
     *     name="status",
     *     in="query",
     *     @OA\Schema(
     *      type="string",
     *     ),
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
    public function indexMobile(NewsLetterRequest $request)
    {
        $data = $this->repository->ListNewsLetterMobile($request->all());
        return $this->responseJson(200, new NewsLetterResource($data));
    }
}
