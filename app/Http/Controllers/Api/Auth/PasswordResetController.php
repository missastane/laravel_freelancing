<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswaordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Services\User\UserService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Log;
class PasswordResetController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected UserService $userService){}
    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Request password reset email",
     *     description="Sends a password reset link to the given email address. Rate limiting is applied to prevent spam.",
     *     operationId="forgotPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="لینک بازیابی کلمه عبور به ایمیل شما ارسال شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="ما اخیرا یک لینک تنظیم مجدد کلمه عبور برای شما ارسال کرده ایم. لطفا قبل از درخواست مجدد صبر کنید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswaordRequest $request)
    {
        try {
            $result = $this->userService->forgotPassword($request->all());
            if(!$result){
                return $this->error('ما اخیراً یک لینک تنظیم مجدد کلمه عبور برای شما ارسال کرده‌ایم. لطفاً قبل از درخواست مجدد صبر کنید', 429);
            }
            return $this->success(null, 'لینک بازیابی کلمه عبور به ایمیل شما ارسال شد');
        } catch (Exception $e) {
            Log::error('متد فراموشی کلمه عبور با خطا مواجه شد' . $e->getMessage());
            return $this->error();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset user password",
     *     description="Resets the password using the provided token and new password.",
     *     operationId="resetPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "token", "password", "password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="a1b2c3d4e5f6g7h8"),
     *             @OA\Property(property="password", type="string", format="password", example="Ex@mpl8N0risk7"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="Ex@mpl8N0risk7")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="رمز عبور شما با موفقیت تغییر یافت")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="اطلاعات وارد شده نامعتبر است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $result = $this->userService->resetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token')
        );

        if ($result['status']) {
            return $this->success(null,$result['message']);
        }
        return $this->error($result['message'],$result['code'] ?? 400);
    }
}
