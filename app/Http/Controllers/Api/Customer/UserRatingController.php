<?php

namespace App\Http\Controllers\Api\Customer;

use App\Exceptions\User\AlreadyRatedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\RatingRequest;
use App\Http\Services\Rating\RatingService;
use App\Models\Market\Order;
use App\Models\User\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserRatingController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected RatingService $ratingService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/user-rating/show/{user}",
     *     summary="Get details of a specific User Ratings",
     *     description="Returns the `User Ratings` details",
     *     tags={"Customer-Rating"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the User to fetch",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched User Ratings details",
     *           @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="object",
     *               ref="#/components/schemas/Rating"
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
    public function show(User $user)
    {
        return $this->success($this->ratingService->getContextRates(User::class, $user->id));
    }

    /**
     * @OA\Post(
     *     path="/api/user-rating/store/{order}",
     *     summary="Store a new User Rate by customers",
     *     description="In this method customers can Store a new User Rate for a special order",
     *     tags={"Customer-Rating"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="ID of the Order to rate",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *             @OA\Property(property="value", type="integer", description="this field only accept numbers between 1-5", example=1),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful User Education Creation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="true"),
     *             @OA\Property(property="message", type="string", example="امتیاز شما با موفقیت ثبت شد"),
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
    public function addRate(Order $order, RatingRequest $request)
    {
        if (Gate::denies('addRate', $order)) {
            return $this->error('عملیات غیرمجاز', 403);
        }
        try {
            if (auth()->user()->active_role === 'freelancer') {
                $user = $order->employer;
            } else {
                $user = $order->freelancer;
            }
            $this->ratingService->addRate($request->all(), User::class, $user->id, $order->id);
            return $this->success(null, 'امتیاز شما با موفقیت ثبت شد', 201);
        } catch (AlreadyRatedException $e) {
            throw $e;
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }
}
