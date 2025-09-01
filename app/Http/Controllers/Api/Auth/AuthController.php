<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Services\Contracts\User\UserServiceInterface;
use App\Http\Services\User\UserService;
use App\Models\User\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(
 *     title="Laravel_Freelancing API",
 *     version="1.0.0",
 *     description="API For Freelancing Projects",
 *     @OA\Contact(
 *         email="missastaneh@gmail.com"
 *     ),
 *     @OA\License(
 *         name="Missastaneh",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * ),
 *  @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer"
 *         )
 *     )
 * )
 * @OA\Security(
 *     securityScheme="bearerAuth"
 * )
 */
class AuthController extends Controller
{
    use ApiResponseTrait;


    public function __construct(protected UserService $userService)
    {
    }
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     description="This Method Registers a new User and Sends approval link to user to approve email",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "password", "password_confirmation"},
     *             @OA\Property(property="role", type="integer", enum={1,2}),
     *             @OA\Property(property="email", type="string", format="email", example="example@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="r@mZ4Ob00r"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="r@mZ4Ob00r"),
     * 
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registeration was successfully and approval link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="با تشکر از ثبت نام شما. لینک تأیید ایمیل به آدرس ایمیل وارد شده ارسال گردید. لطفا ابتدا ایمیل خود را تأیید فرمایید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="وارد کردن ایمیل الزامی است"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا مجددا تلاش کنید"),
     *         )
     *     )
     * )
     */
    public function register(AuthRequest $request)
    {
        try {
            $user = $this->userService->registerUser($request->all());
            return $this->success(null, 'با تشکر از ثبت نام شما. لینک تأیید ایمیل به آدرس ایمیل وارد شده ارسال گردید. لطفا ابتدا ایمیل خود را تأیید فرمایید', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     description="Authenticate a user and return a JWT token if credentials are valid and email is verified",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="missastaneh@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="r@mZ4Ob00r")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *         @OA\Property(property="status", type="boolean", example=true),
     *         @OA\Property(property="data", type="array", 
     *             @OA\Items(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJK..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *       )
     *         ),
     *        @OA\Property(property="message", type="string", example=null)
     *      )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials or email not verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="ایمیل یا رمز عبور اشتباه است یا ایمیل شما تأیید نشده")
     *         )
     *     )
     * )
     */
    public function login(AuthRequest $request)
    {
        try {
            $result = $this->userService->loginUser($request->only(['email', 'password']));
            if (!$result['status']) {
                return $this->error($result['message'], $result['code']);
            }
            return $this->success($result['data'], $result['message'], $result['code']);
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User Logout",
     *     description="Invalidate the current JWT token and log the user out",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="کاربر با موفقیت از حساب کاربری خود خارج شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *             
     *         )
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     * )
     */
    public function logout()
    {
        try {
            $this->userService->logoutUser();
            return $this->success(null, 'کاربر با موفقیت از حساب کاربری خود خارج شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh JWT Token",
     *     description="Refresh the expired JWT token and return a new token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(property="data", type="array", 
     *             @OA\Items(
     *                @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJK..."),
     *                @OA\Property(property="token_type", type="string", example="bearer"),
     *                @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *       )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated- Invalid or expired token",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *         )
     *     ),
     *   @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     ),
     * )
     */
    public function refresh()
    {
        try {
            $newToken = $this->userService->refresh();
            return $this->success([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        } catch (\RuntimeException $e) {
            return $this->error();
        }
    }

}
