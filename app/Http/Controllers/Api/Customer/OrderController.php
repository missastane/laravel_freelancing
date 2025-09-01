<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\CommentRequest;
use App\Http\Services\Comment\CommentService;
use App\Http\Services\Order\OrderService;
use App\Models\Market\Order;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected OrderService $orderService,
        protected CommentService $commentService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/order",
     *     summary="Retrieve list of a special user's Orders",
     *     description="Retrieve list of all a `user's Orders`",
     *     tags={"Customer-Order"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User id to fetch him/her orders",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *    @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Order Status: `pending`, `processing`, `completed`, `canceled`",
     *         @OA\Schema(type="string", enum={"pending", "processing", "completed","canceled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of a user's Orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                      ref="#/components/schemas/Order"
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
     *     ))
     * )
     */
    public function index(Request $request)
    {
        $orders = $this->orderService->getUserOrders(null, $request->query('status'));
        return $this->success($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/order/show/{order}",
     *     summary="Get details of a specific Order",
     *     description="Returns the `Order` details",
     *     tags={"Customer-Order"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="ID of the Order to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Order details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/Order"
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
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * )
     */
    public function show(Order $order)
    {
        return $this->success($this->orderService->showOrder($order));
    }

    /**
     * @OA\Get(
     *     path="/api/order/final-files/{order}",
     *     summary="Get Final Files of a specific Order",
     *     description="Returns the Order `Final Files`",
     *     tags={"Customer-Order"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="ID of the Order to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Order Final Files",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/Order"
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
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     ))
     * )
     */
    public function getOrderFileFiles(Order $order)
    {
        return $this->success($this->orderService->getOrderFinalFiles($order));
    }


}
