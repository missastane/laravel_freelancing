<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\CommentRequest;
use App\Http\Services\Comment\CommentService;
use App\Models\Market\Order;
use App\Traits\ApiResponseTrait;
use Exception;

class OrderCommentController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected CommentService $commentService)
    {
    }

    /**
     * @OA\Post(
     *     path="/api/order/{order}/submit-comment",
     *     summary="Store a new comment for an order by customers",
     *     description="In this method customers can Store a new comment for an order",
     *     tags={"OrderComment","Customer-Comment"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="comment", type="string", pattern="^[a-zA-Z\u0600-\u06FF0-9\s\-\،,?!.]+$", description="This field can only contain Persian and English letters, Persian and English numbers, and symbols (,،?!.). Any other characters will result in a validation error.", example="این نظر منه"),
     *                       )
     *             )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful Order Comment Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="نظر شما با موفقیت ثبت شد"),
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
     *     @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
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
    public function store(Order $order, CommentRequest $request)
    {
        try {
            $this->commentService->addComment($request->all(), Order::class, $order->id, null, null, null);
            return $this->success(null, 'نظر شما با موفقیت ثبت شد', 201);
        } catch (Exception $e) {
            return $this->error();
        }
    }


}
