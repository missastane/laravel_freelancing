<?php

namespace App\Http\Controllers\Api\Admin\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\WithdrawalRequest;
use App\Http\Services\Payment\WithDrawalRequestService;
use App\Models\Payment\Withdrawal;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected WithDrawalRequestService $withDrawalRequestService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/Withdrawal-request",
     *     summary="Retrieve list of Withdrawal Requests",
     *     description="Retrieve list of all `Withdrawal Requests` can filters by status",
     *     tags={"WithdrawalRequest"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Request Status: `pending`,`accepted`,`rejected`",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","accepted","rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of Withdrawal Requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/WithdrawalRequest"
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
        return $this->success($this->withDrawalRequestService->getWithdrawalRequests($request->query('status')));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/user/Withdrawal-request/show/{Withdrawal}",
     *     summary="Get details of a specific Withdrawal Request",
     *     description="Returns the `Withdrawal Request` details",
     *     operationId="getWithdrawalRequestDetails",
     *     tags={"WithdrawalRequest"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="Withdrawal",
     *         in="path",
     *         description="ID of the Withdrawal Request to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Withdrawal Request details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/WithdrawalRequest"
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
    public function show(Withdrawal $withdrawal)
    {
        return $this->success($this->withDrawalRequestService->showRequest($withdrawal));
    }

    /**
     * @OA\Post(
     *     path="/api/admin/user/withdrawal-request/store",
     *     summary="Add a new withdrawal request by admin",
     *     description="In this method admins can Add a new withdrawal request",
     *     tags={"WithdrawalRequest"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="amount", type="integer", example=200000),
     *             @OA\Property(property="bank_name", type="string", description="This field can only contain Persian letters and space. Any other characters will result in a validation error.", example="ملی"),
     *             @OA\Property(property="card_number", type="string", description="This field can only contain Persian letters and space. Any other characters will result in a validation error.", example="6037227458963256"),
     *             @OA\Property(property="account_number_sheba", type="string", description="This field can only contain IR plus 24 numbers. Any other characters will result in a validation error.", example="IR12 1245 1258 6985 5789 2456 34"),
     *          )
     *        )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful WithdrawalRequest Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="درخواست انتقال به حساب با موفقیت ثبت شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *  @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *      @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="فیلد وارد شده نامعتبر است"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="x", type="array",
     *                     @OA\Items(type="string", example="فیلد x اجباری است")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید.")
     *         )
     *     )
     * )
     */
    public function addRequest(WithdrawalRequest $request)
    {
        try {
            $this->withDrawalRequestService->storeWithdrawalRequest($request->all());
            return $this->success(null, 'درخواست انتقال به حساب با موفقیت ثبت شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

       /**
     * @OA\Put(
     *     path="/api/admin/user/withdrawal-request/pay/{withdrawal}",
     *     summary="Change the status of a Withdrawal Request to Pay",
     *     description="This endpoint `change the status of a Withdrawal Request to Pay`",
     *     operationId="updateWithdrawalRequestStatus",
     *     security={{"bearerAuth": {}}},
     *     tags={"WithdrawalRequest"},
     *     @OA\Parameter(
     *         name="withdrawal",
     *         in="path",
     *         description="Withdrawal Request id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Withdrawal Request status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="درخواست انتقال وجه کاربر به حالت پرداخت شده تغییر کرد"),
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
    public function changeRequestToPaid(Withdrawal $Withdrawal)
    {
        try {
            $change = $this->withDrawalRequestService->changeRequestToPaid($Withdrawal);
            if ($change) {
                return $this->success(null, 'درخواست برداشت از کیف پول کاربر با موفقیت انجام گرفت');
            } else {
                return $this->error('درخواست شما امکان پذیر نیست', 403);
            }
        } catch (Exception $e) {
            return $this->error();
        }
    }

       /**
     * @OA\Patch(
     *     path="/api/admin/user/withdrawal-request/reject/{withdrawal}",
     *     summary="Change the status of a Withdrawal Request to Reject",
     *     description="This endpoint `change the status of a Withdrawal Request to Reject`",
     *     security={{"bearerAuth": {}}},
     *     tags={"WithdrawalRequest"},
     *     @OA\Parameter(
     *         name="withdrawal",
     *         in="path",
     *         description="Withdrawal Request id to change the status",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     security={ {"bearerAuth": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Withdrawal Request status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="درخواست انتقال وجه کاربر با موفقیت رد شد"),
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
    public function rejectRequest(Withdrawal $Withdrawal)
    {
        try {
            $change = $this->withDrawalRequestService->rejectRequest($Withdrawal);
            if ($change) {
                return $this->success(null, 'درخواست برداشت از کیف پول کاربر با موفقیت رد شد');
            } else {
                return $this->error('درخواست شما امکان پذیر نیست', 403);
            }
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
