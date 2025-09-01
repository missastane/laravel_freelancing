<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\WithdrawalRequest;
use App\Http\Services\Payment\WithDrawalRequestService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class WithdrawalRequestController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected WithDrawalRequestService $withDrawalRequestService)
    {
    }

    /**
     * @OA\Post(
     *     path="/api/withdrawal-request/add-request",
     *     summary="Add a new Withdrawal Request by customers",
     *     description="In this method customers can Add a a new Withdrawal Request",
     *     tags={"Customer-WithdrawalRequest"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Add WithdrawalRequest was successful",
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
        try{
            $this->withDrawalRequestService->storeWithdrawalRequest($request->all());
            return $this->success(null, 'درخواست انتقال به حساب با موفقیت ثبت شد');
        }catch(Exception $e){
            return $this->error();
        }
    }
}
