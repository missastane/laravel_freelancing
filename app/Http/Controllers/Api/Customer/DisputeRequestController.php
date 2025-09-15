<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Services\DisputeRequest\DisputeRequestService;
use App\Models\User\DisputeRequest;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DisputeRequestController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected DisputeRequestService $disputeRequestService)
    {
    }
    /**
     * @OA\Get(
     *     path="/api/dispute-request",
     *     summary="Retrieve list of auth user's Dispute Requests",
     *     description="Retrieve list of auth user's `Dispute Requests`",
     *     tags={"Customer-DisputeRequest"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     
     *     @OA\Response(
     *         response=200,
     *         description="A list of Dispute Requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/DisputeRequest"
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=45)
     *             )
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *  @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->success($this->disputeRequestService->getUserRequests());
    }

    /**
     * @OA\Get(
     *     path="/api/dispute-request/show/{disputeRequest}",
     *     summary="Get details of a specific disputeRequest",
     *     description="Returns the `disputeRequest` details",
     *     tags={"Customer-DisputeRequest"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="disputeRequest",
     *         in="path",
     *         description="ID of the DisputeRequest to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched DisputeRequest details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/DisputeRequest"
     *             )
     *         )
     *   ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *  @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *  @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * )
     */
    public function show(DisputeRequest $disputeRequest)
    {
        return $this->success($this->disputeRequestService->showDisputeRequest($disputeRequest));
    }


    /**
     * @OA\Delete(
     *     path="/api/dispute-request/delete/{disputeRequest}",
     *     summary="Delete a DisputeRequest",
     *     description="This endpoint allows the user to `delete an existing DisputeRequest`.",
     *     operationId="deleteDisputeRequest",
     *     tags={"Customer-DisputeRequest"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="disputeRequest",
     *         in="path",
     *         description="The ID of the DisputeRequest to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="DisputeRequest deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="رکورد با موفقیت حذف شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */

    public function delete(DisputeRequest $disputeRequest)
    {
        if (Gate::denies('delete', $disputeRequest)) {
            return $this->error('شما مجاز به انجام این عملیات نیستید', 403);
        }
        try {
            $this->disputeRequestService->deleteDisputeRequest($disputeRequest);
            return $this->success(null, 'درخواست داوری با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
