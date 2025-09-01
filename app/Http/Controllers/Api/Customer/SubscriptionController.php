<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Services\Subscription\SubscriptionService;
use App\Models\Market\Subscription;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected SubscriptionService $subscriptionService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/subscription",
     *     summary="Retrieve list of Subscriptions that auth user can buy them",
     *     description="Retrieve list of all `Subscriptions that auth user can buy them`",
     *     tags={"Customer-Subscription"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="A list of Subscriptions that auth user can buy them",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Subscription")
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
    public function index()
    {
        return $this->success($this->subscriptionService->getAllowedSubscriptionPlans());
    }

    /**
     * @OA\Get(
     *     path="/api/subscription/active-plan",
     *     summary="Retrieve active Subscription for authenticated user",
     *     description="Get the subscription plan currently active for the authenticated user.",
     *     tags={"Customer-Subscription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Active subscription retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(property="data", ref="#/components/schemas/Subscription")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         )
     *     )
     * )
     */
    public function activePlan()
    {
        return $this->success($this->subscriptionService->userActivePlan());
    }

    /**
     * @OA\Post(
     *     path="/api/subscription/puchase/{subscription}",
     *     summary="Puchase a Subscription by customers",
     *     description="In this method customers Puchase a Subscription",
     *     tags={"Customer-Subscription"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         description="ID of the Subscription to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subscription Purchase was successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="پلن با موفقیت خریداری شد"),
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
     *   @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
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
    public function purchase(Subscription $subscription)
    {
        try {
            $result = $this->subscriptionService->purchaseSubscription($subscription);
            return $this->success($result['data'], $result['message'], $result['code']);
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/subscription/show/{subscription}",
     *     summary="Get details of a specific Subscription",
     *     description="Returns the `Subscription` details",
     *     tags={"Customer-Subscription"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         description="ID of the Subscription to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Subscription details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/Subscription"
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
    public function show(Subscription $subscription)
    {
        return $this->success($this->subscriptionService->showSubscription($subscription));
    }
}
