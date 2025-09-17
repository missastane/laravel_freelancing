<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\PaymentRequest;
use App\Http\Services\Payment\PaymentService;
use App\Http\Services\Payment\ZarinPalService;
use App\Models\Payment\Payment;
use App\Models\Payment\WalletTransaction;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use ApiResponseTrait;
    protected $zarinpalService;
    public function __construct(
        ZarinPalService $zarinPalService,
        protected PaymentService $paymentService
    ) {
        $this->zarinpalService = $zarinPalService;
    }

    /**
     * @OA\Post(
     *     path="/api/payment",
     *     summary="Initiate a new online payment using Zarinpal",
     *     description="This endpoint allows an authenticated user to create a new payment request through Zarinpal. It returns a payment URL that the user must be redirected to in order to complete the transaction. The callback from Zarinpal must be handled separately.",
     *     tags={"Customer-Payment"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(
     *                 property="amount",
     *                 type="integer",
     *                 example=100000,
     *                 description="The amount to be paid (in Rials)."
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 example="Payment for premium plan",
     *                 description="Optional description for the payment."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment request successful. Redirect user to returned payment_url.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payment_url", type="string", example="https://www.zarinpal.com/pg/StartPay/S000000000000000000000000000000gmwrm")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Zarinpal failed to initiate the payment request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطا در ارتباط با درگاه پرداخت")
     *         )
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
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

    // public function store(PaymentRequest $request)
    // {
    //     try {
    //         $user = auth()->user();
    //         $payment = Payment::create([
    //             'user_id' => $user->id,
    //             'amount' => $request->amount,
    //             'gateway' => 'zarinpal',
    //             'description' => $request->description
    //         ]);
    //         $result = $this->zarinpalService->request(
    //             $payment->amount,
    //             $payment->description,
    //             $user->email ?? null,
    //             $user->mobile ?? null
    //         );
    //         if ($result['success']) {
    //             $payment->update([
    //                 'transaction_id' => $result['authority'],
    //                 'bank_first_response' => json_encode($result)
    //             ]);
    //             // this url returns https://www.zarinpal.com/pg/StartPay/S000000000000000000000000000000gmwrm
    //             // to text this url must remove wwww and replace it with sandbox -> go to zarinpal system
    //             // and get callback url and enter in postman with bearer auth token to get response ok
    //             return $this->success(['payment_url' => $result['payment_url']]);
    //         }
    //         // fail zarinpal connection
    //         $payment->update([
    //             'status' => 3,
    //             'bank_first_response' => json_encode($result)
    //         ]);
    //         return $this->error($result['message'], 422);
    //     } catch (Exception $e) {
    //         return $this->error($e->getMessage());
    //     }
    // }
    public function store(PaymentRequest $request)
    {
        try {
            $result = $this->paymentService->store($request->validated());
            if ($result['status']) {
                return $this->success($result['data'], $result['message']);
            } else {
                return $this->error($result['message'], 422);
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->error();
        }
    }


    /**
     * @OA\Get(
     *     path="/api/payment/callback",
     *     summary="Verify Zarinpal payment after redirect. This method is not able to test",
     *     description="This endpoint is used to verify a completed payment after the user is redirected back from Zarinpal. It checks the authority code and payment status, verifies the transaction with Zarinpal, updates the payment status, and credits the user's wallet.",
     *     tags={"Customer-Payment"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="Authority",
     *         in="query",
     *         required=true,
     *         description="The unique transaction authority code provided by Zarinpal",
     *         @OA\Schema(type="string", example="000000000000000000000000000000gmwrm")
     *     ),
     *     @OA\Parameter(
     *         name="Status",
     *         in="query",
     *         required=true,
     *         description="The payment status sent from Zarinpal (e.g., 'OK' for success)",
     *         @OA\Schema(type="string", example="OK")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment verified successfully and wallet credited",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="پرداخت با موفقیت انجام شد"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="ref_id", type="string", example="123456789"),
     *                 @OA\Property(property="amount", type="integer", example=100000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment record not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="تراکنش یافت نشد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Payment verification failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="تأیید پرداخت ناموفق بود")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Payment was cancelled by the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="پرداخت توسط کاربر لغو شد")
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
    // public function verify(Request $request)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $authority = $request->query('Authority');
    //         $status = $request->query('Status');

    //         $payment = Payment::where('transaction_id', $authority)->first();

    //         if (!$payment) {
    //             return $this->error('تراکنش یافت نشد', 404);
    //         }

    //         $payment = Payment::where('transaction_id', $authority)->first();
    //         // Log::info('payment from db: ' . ($payment ? $payment->id : 'null'));
    //         if ($status !== 'OK') {
    //             Log::info('status: not ok');
    //             $payment->update([
    //                 'status' => 3,
    //                 'bank_second_response' => json_encode(['status' => $status]),
    //             ]);
    //             return $this->error('پرداخت توسط کاربر لغو شد', 422);
    //         }
    //         $result = $this->zarinpalService->verify($authority, $payment->amount);
    //         if (!$result['success']) {
    //             Log::info('result: not success');
    //             $payment->update([
    //                 'status' => 3,
    //                 'bank_second_response' => json_encode($result),
    //             ]);
    //             return $this->error('تأیید پرداخت ناموفق بود', 401);
    //         }
    //         $payment->update([
    //             'status' => 2,
    //             'reference_id' => $result['ref_id'],
    //             'bank_second_response' => json_encode($result),
    //             'paid_at' => now()
    //         ]);
    //         WalletTransaction::create([
    //             'wallet_id' => $payment->user->wallet->id,
    //             'transaction_type' => 1,
    //             'amount' => $payment->amount,
    //             'description' => 'شارژ کیف پول با پرداخت اینترنتی',
    //             'related_type' => Payment::class,
    //             'related_id' => $payment->id
    //         ]);
    //         $payment->user->wallet->increment('balance', $payment->amount);
    //         DB::commit();
    //         return $this->success([
    //             'ref_id' => $result['ref_id'],
    //             'amount' => $payment->amount
    //         ], 'پرداخت با موفقیت انجام شد', 201);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return $this->error($e->getMessage());
    //     }
    // }

    public function verify(Request $request)
    {
        try {
            $result = $this->paymentService->verify($request->all());
            if (!$result['status']) {
                return $this->error($result['message'], $result['code']);
            }
            return $this->success($result['data'], $result['message'], 201);
        } catch (Exception $e) {
            Log::error('Verify payment error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error($e->getMessage());
        }
    }

}
