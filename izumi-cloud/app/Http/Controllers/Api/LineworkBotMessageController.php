<?php
/**
 * Created by VeHo.
 * Year: 2025-11-12
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LineworkBotMessageRequest;
use App\Repositories\Contracts\LineworkBotMessageRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\LineworkBotMessageResource;
use Illuminate\Http\Request;

class LineworkBotMessageController extends Controller
{

     /**
     * var Repository
     */
    protected $repository;

    public function __construct(LineworkBotMessageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }



    /**
     * @OA\Post(
     *   path="/api/linework-bot-message/import",
     *   tags={"LineworkBotMessage"},
     *   summary="Import LineworkBotMessage",
     *   operationId="import_linework_bot_message",
     *   @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"file"},
     *              @OA\Property(
     *                   description="file to upload",
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
    public function import(Request $request)
    {
        try {
            $data = $this->repository->importLineworkBotMessage($request->all());
            return $this->responseJson(200, $data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/linework-bot-message/{id}",
     *   tags={"LineworkBotMessage"},
     *   summary="Detail LineworkBotMessage",
     *   operationId="linework_bot_message_show",
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
            $department = $this->repository->find($id);
            return $this->responseJson(200, new BaseResource($department));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *   path="/api/linework-bot-message/{id}",
     *   tags={"LineworkBotMessage"},
     *   summary="Update LineworkBotMessage",
     *   operationId="linework_bot_message_update",
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
    public function update(LineworkBotMessageRequest $request, $id)
    {
        $attributes = $request->except([]);
        $data = $this->repository->update($attributes, $id);
        return $this->responseJson(200, new BaseResource($data));
    }

    /**
     * @OA\Delete(
     *   path="/api/linework-bot-message/{id}",
     *   tags={"LineworkBotMessage"},
     *   summary="Delete LineworkBotMessage",
     *   operationId="linework_bot_message_delete",
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
     *   path="/api/linework-bot-message/update",
     *   tags={"LineworkBotMessage"},
     *   summary="Update LineworkBotMessage From Google Sheet",
     *   operationId="linework_bot_message_update_google_sheet",
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *            required={"data"},
     *            @OA\Property(
     *              property="data",
     *              type="object",
     *              @OA\Property(
     *                property="message",
     *                type="string",
     *              ),
     *              @OA\Property(
     *                property="message_en",
     *                type="string",
     *              ),
     *              @OA\Property(
     *                property="message_zh",
     *                type="string",
     *              ),
     *              @OA\Property(
     *                property="date",
     *                type="string",
     *              ),
     *            )
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
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateFromGoogleSheet(Request $request)
    {
        $attributes = $request->all();
        $data = $this->repository->updateFromGoogleSheet($attributes);
        return $this->responseJson(200, new BaseResource($data));
    }
}
