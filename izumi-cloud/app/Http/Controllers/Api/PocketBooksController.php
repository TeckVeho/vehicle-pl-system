<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-02-07
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PocketBooksRequest;
use App\Repositories\Contracts\PocketBooksRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\PocketBooksResource;
use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class PocketBooksController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(PocketBooksRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/pocket-book",
     *   tags={"PocketBooks"},
     *   summary="List pocket_books",
     *   operationId="pocket_books_index",
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
    public function index(PocketBooksRequest $request)
    {
        $data = $this->repository->getPocketBooks($request->all());
        return $this->responseJson(200, $data);
    }

    /**
     * @OA\Post(
     *   path="/api/pocket-book",
     *   tags={"PocketBooks"},
     *   summary="Add new pocket_books",
     *   operationId="pocket_books_create",
     *   @OA\RequestBody(
     *           description="Input data",
     *           @OA\MediaType(
     *               mediaType="application/json",
     *               @OA\Schema(
     *                  type="object",
     *                    @OA\Property(property="year", description="year", format="string", example="2025"),
     *                    @OA\Property(property="list_file_id",
     *                           description="list_file_id",
     *                           type="object",
     *                           example={1,2}
     *                     ),
     *               ),
     *           ),
     *       ),
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
    public function store(PocketBooksRequest $request)
    {
        try {
            $data = $this->repository->storePocketBook($request->all());
            if ($data != false) {
                return $this->responseJson(200, $data);
            } else {
                return $this->responseJsonError(500, 'error', 'exceed quantity');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/pocket-book/{id}",
     *   tags={"PocketBooks"},
     *   summary="Detail PocketBooks",
     *   operationId="pocket_books_show",
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
            $department = $this->repository->findPocketBooks($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *   path="/api/pocket-book/{id}",
     *   tags={"PocketBooks"},
     *   summary="Update PocketBooks",
     *   operationId="pocket_books_update",
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
     *          example={"name":"string"},
     *          @OA\Schema(
     *            required={"name"},
     *            @OA\Property(
     *              property="name",
     *              format="string",
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
    public function update(PocketBooksRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->update($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/pocket-book/{id}",
     *   tags={"PocketBooks"},
     *   summary="Delete PocketBooks",
     *   operationId="pocket_books_delete",
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
     *   path="/api/pocket-book/upload-file",
     *   tags={"PocketBooks"},
     *   summary="upload-file-pdf pocket book ",
     *   operationId="pocket-book-upload-file",
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
     *               @OA\Property(
     *                    description="Year of the pocket book",
     *                    property="year",
     *                    type="integer",
     *                ),
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
                return $this->repository->storePocketBook($year, $file->id, $tag);
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
     * @OA\Get(
     *   path="/api/pocket-book/option",
     *   tags={"PocketBooks"},
     *   summary="option PocketBooks",
     *   operationId="pocket_books_option",
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
    public function option()
    {
        try {
            $option = $this->repository->optionYear();
            return $this->responseJson(200, new BaseResource($option));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *   path="/api/pocket-book/change-order",
     *   tags={"PocketBooks"},
     *   summary="Change order of pocket books",
     *   operationId="pocket_books_change_order",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          example={"list_pocket_book":{1,2,3}},
     *          @OA\Schema(
     *            required={"list_pocket_book"},
     *            @OA\Property(
     *              property="list_pocket_book",
     *              type="array",
     *              @OA\Items(type="integer")
     *            ),
     *         )
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Send request success",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":200,"message":"Order updated successfully"}
     *     ),
     *   ),
     *   security={{"auth": {}}},
     * )
     * Change order of pocket books
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeOrder(PocketBooksRequest $request)
    {
        try {
            $listPocketBook = $request->input('list_pocket_book', []);
            $this->repository->updatePositions($listPocketBook);
            return $this->responseJson(200, null, 'Order updated successfully');
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
