<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Contracts\InspectionNotificationRecipientRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Issue #810 / #826: Inspection notification recipients API (vehicle inspection / periodic inspection)
 *
 * @OA\Tag(
 *   name="Inspection Notification Recipients",
 *   description="Notification recipients for vehicle inspection and periodic inspection"
 * )
 */
class InspectionNotificationRecipientController extends BaseController
{
    protected InspectionNotificationRecipientRepositoryInterface $repository;

    public function __construct(InspectionNotificationRecipientRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get candidates (users with MG+ role)
     *
     * @OA\Get(
     *   path="/api/inspection-notification-recipients/candidates",
     *   tags={"Inspection Notification Recipients"},
     *   summary="Get candidates",
     *   description="Returns users who can be selected as notification recipients (site manager, HQ manager, department manager, executive officer, director, DX, DX manager).",
     *   operationId="inspectionNotificationRecipients_candidates",
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="data", type="array",
     *           @OA\Items(
     *             @OA\Property(property="id", type="integer", example=123),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="department_code", type="string", nullable=true, example="001")
     *           )
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   security={{"auth": {}}}
     * )
     *
     * GET /api/inspection-notification-recipients/candidates
     */
    public function candidates(): JsonResponse
    {
        $users = $this->repository->getCandidates();
        return response()->json(['data' => $users]);
    }

    /**
     * Get saved list (with department and user)
     *
     * @OA\Get(
     *   path="/api/inspection-notification-recipients",
     *   tags={"Inspection Notification Recipients"},
     *   summary="Get saved list",
     *   description="Returns saved notification recipients by department. Optional filter by department_id.",
     *   operationId="inspectionNotificationRecipients_index",
     *   @OA\Parameter(
     *     name="department_id",
     *     in="query",
     *     description="Filter by department ID (optional)",
     *     required=false,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="data", type="array",
     *           @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="department_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=123),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(property="department", type="object",
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="name", type="string")
     *             ),
     *             @OA\Property(property="user", type="object", nullable=true,
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="name", type="string")
     *             )
     *           )
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   security={{"auth": {}}}
     * )
     *
     * GET /api/inspection-notification-recipients
     */
    public function index(Request $request): JsonResponse
    {
        $departmentId = $request->has('department_id') ? (int) $request->department_id : null;
        $items = $this->repository->getList($departmentId);
        return response()->json(['data' => $items]);
    }

    /**
     * Bulk save (overwrite notification recipients)
     *
     * @OA\Put(
     *   path="/api/inspection-notification-recipients",
     *   tags={"Inspection Notification Recipients"},
     *   summary="Bulk save",
     *   description="Saves notification recipients in bulk. Existing data is deleted and replaced by the request body. Empty array clears all.",
     *   operationId="inspectionNotificationRecipients_store",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"recipients"},
     *       @OA\Property(property="recipients", type="array",
     *         description="List of department_id and user_id pairs",
     *         @OA\Items(
     *           @OA\Property(property="department_id", type="integer", example=1, description="Department ID"),
     *           @OA\Property(property="user_id", type="integer", example=123, description="User ID (users.id)")
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="message", type="string", example="OK"),
     *         @OA\Property(property="data", type="array",
     *           @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="department_id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="department", type="object"),
     *             @OA\Property(property="user", type="object", nullable=true)
     *           )
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=422, description="Validation error (e.g. department_id or user_id not found)"),
     *   security={{"auth": {}}}
     * )
     *
     * PUT /api/inspection-notification-recipients
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipients' => 'required|array',
            'recipients.*.department_id' => 'required|integer|exists:departments,id',
            'recipients.*.user_id' => 'required|integer|exists:users,id',
        ]);

        $items = $this->repository->store($validated['recipients']);
        return response()->json(['message' => 'OK', 'data' => $items]);
    }

    /**
     * Get map for notification (no auth; used by izumi-maintenance)
     *
     * @OA\Get(
     *   path="/api/inspection-notification-recipients/for-notification",
     *   tags={"Inspection Notification Recipients"},
     *   summary="Get map for notification (no auth)",
     *   description="Called by izumi-maintenance vehicle inspection notification command. Returns object with department_id as key and array of user_id as value. No authentication required.",
     *   operationId="inspectionNotificationRecipients_forNotification",
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         description="Object with department_id as key and array of user_id as value"
     *       )
     *     )
     *   )
     * )
     *
     * GET /api/inspection-notification-recipients/for-notification
     */
    public function forNotification(): JsonResponse
    {
        $map = $this->repository->getForNotificationMap();
        return response()->json($map);
    }
}
