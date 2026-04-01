<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-05-19
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShakenshoEmailRequest;
use App\Repositories\Contracts\ShakenshoEmailRepositoryInterface;
use App\Http\Resources\BaseResource;
use App\Http\Resources\ShakenshoEmailResource;
use Illuminate\Http\Request;

class ShakenshoEmailController extends Controller
{

    /**
     * var Repository
     */
    protected $repository;

    public function __construct(ShakenshoEmailRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *   path="/api/shakensho-email",
     *   tags={"ShakenshoEmail"},
     *   summary="List shakensho_email",
     *   operationId="shakensho_email_index",
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
    public function index(ShakenshoEmailRequest $request)
    {
        $data = $this->repository->getAll();
        return $this->responseJson(200, $data);
    }

    /**
     * @OA\Post(
     *   path="/api/shakensho-email",
     *   tags={"ShakenshoEmail"},
     *   summary="Add new shakensho_email",
     *   operationId="shakensho_email_create",
     *   @OA\Parameter(name="name", in="query", required=true,
     *     @OA\Schema(type="string"),
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
     *   security={},
     * )
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(ShakenshoEmailRequest $request)
    {
        try {
            $data = $this->repository->createOrUpdate($request->get('emails'));
            return $this->responseJson(200, new ShakenshoEmailResource($data));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *   path="/api/shakensho-email/{id}",
     *   tags={"ShakenshoEmail"},
     *   summary="Detail ShakenshoEmail",
     *   operationId="shakensho_email_show",
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

//    /**
//     * @OA\Put(
//     *   path="/api/shakensho-email/{id}",
//     *   tags={"ShakenshoEmail"},
//     *   summary="Update ShakenshoEmail",
//     *   operationId="shakensho_email_update",
//     *   @OA\Parameter(
//     *     name="id",
//     *     in="path",
//     *     required=true,
//     *     @OA\Schema(
//     *      type="string",
//     *     ),
//     *   ),
//     *   @OA\RequestBody(
//     *       @OA\MediaType(
//     *          mediaType="application/json",
//     *          example={"name":"string"},
//     *          @OA\Schema(
//     *            required={"name"},
//     *            @OA\Property(
//     *              property="name",
//     *              format="string",
//     *            ),
//     *         )
//     *      )
//     *   ),
//     *   @OA\Response(
//     *     response=200,
//     *     description="Send request success",
//     *     @OA\MediaType(
//     *      mediaType="application/json",
//     *      example={"code":200,"data":{"id": 1,"name":  "............."}}
//     *     ),
//     *   ),
//     *   @OA\Response(
//     *     response=403,
//     *     description="Access Deny permission",
//     *     @OA\MediaType(
//     *      mediaType="application/json",
//     *      example={"code":403,"message":"Access Deny permission"}
//     *     ),
//     *   ),
//     *   security={{"auth": {}}},
//     * )
//     * Display a listing of the resource.
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function update(ShakenshoEmailRequest $request, $id)
//    {
//        $attributes = $request->except([]);
//        $data = $this->repository->update($attributes, $id);
//        return $this->responseJson(200, new BaseResource($data));
//    }
//
//    /**
//     * @OA\Delete(
//     *   path="/api/shakensho-email/{id}",
//     *   tags={"ShakenshoEmail"},
//     *   summary="Delete ShakenshoEmail",
//     *   operationId="shakensho_email_delete",
//     *   @OA\Parameter(
//     *      name="id",
//     *      in="path",
//     *      required=true,
//     *     @OA\Schema(
//     *      type="string",
//     *     ),
//     *   ),
//     *   @OA\Response(
//     *     response=200,
//     *     description="Send request success",
//     *     @OA\MediaType(
//     *      mediaType="application/json",
//     *      example={"code":200,"data":"Send request success"}
//     *     )
//     *   ),
//     *   security={{"auth": {}}},
//     * )
//     * @param int $id
//     * @return \Illuminate\Http\JsonResponse
//     * @throws \Exception
//     */
//    public function destroy($id)
//    {
//        $this->repository->delete($id);
//        return $this->responseJson(200, null, trans('messages.mes.delete_success'));
//    }
}
